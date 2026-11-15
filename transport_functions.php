<?php
class Dashboard{
	function __construct(private PDO $pdo){}

	public function fetch_counts(){
		$fetch = "SELECT
					(SELECT COUNT(*) FROM operators) AS total_operators,
					(SELECT COUNT(*) FROM vehicles) AS total_vehicles,
					(SELECT COUNT(*) FROM routes) AS total_routes,
					(SELECT COUNT(*) FROM assignments WHERE date_ended IS NULL) AS active_assignments,
					(SELECT COUNT(*) FROM documents
					 WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AS expiring_documents,
					(SELECT COUNT(*) FROM documents WHERE expiry_date < CURDATE()) AS expired_documents,
					(SELECT COUNT(*) FROM documents WHERE verification_status = 'Pending') AS pending_documents,
					(SELECT COUNT(*) FROM inspections WHERE result = 'Pending' AND schedule_date < CURDATE()) AS overdue_inspections,
					(SELECT COUNT(*) FROM inspections
					 WHERE result = 'Pending' AND schedule_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS upcoming_inspections,
					(SELECT COUNT(*) FROM inspections i
					 WHERE i.result = 'Failed'
					 AND i.inspection_date = (SELECT MAX(i2.inspection_date) FROM inspections i2
					                          WHERE i2.vehicle_id = i.vehicle_id AND i2.result != 'Pending')) AS noncompliant_vehicles";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function fetch_attention_documents(){
		$fetch = "SELECT d.*, o.full_name AS operator_name, v.plate_number
				  FROM documents d
				  LEFT JOIN operators o ON d.operator_id = o.id
				  LEFT JOIN vehicles v ON d.vehicle_id = v.id
				  WHERE d.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
				  OR d.verification_status != 'Verified'
				  ORDER BY d.expiry_date ASC
				  LIMIT 8";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

	public function fetch_due_inspections(){
		$fetch = "SELECT i.*, v.plate_number, v.vehicle_type
				  FROM inspections i
				  JOIN vehicles v ON i.vehicle_id = v.id
				  WHERE i.result = 'Pending'
				  ORDER BY i.schedule_date ASC
				  LIMIT 8";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

}

class Associations{
	function __construct(private PDO $pdo){}

	public function insert_data($association_name,$contact_person,$contact_number){
		$insert = "INSERT INTO associations(association_name, contact_person, contact_number)
				   VALUES(:association_name, :contact_person, :contact_number)";
		$prep = $this->pdo->prepare($insert);
		return $prep->execute([
			":association_name" => $association_name,
			":contact_person" => $contact_person,
			":contact_number" => $contact_number,
		]);
	}

	public function fetch_data(){
		$fetch = "SELECT a.*, COUNT(o.id) AS member_count
				  FROM associations a
				  LEFT JOIN operators o ON o.association_id = a.id
				  GROUP BY a.id
				  ORDER BY a.association_name ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

	public function show_update_data($id){
		$fetch = "SELECT * FROM associations WHERE id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function update_data($association_name,$contact_person,$contact_number,$id){
		$update = "UPDATE associations
				   SET association_name = :association_name,
				       contact_person = :contact_person,
				       contact_number = :contact_number
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":association_name" => $association_name,
			":contact_person" => $contact_person,
			":contact_number" => $contact_number,
			":id" => $id,
		]);
	}

	public function name_exists($association_name,$id){
		$fetch = "SELECT COUNT(*) AS total FROM associations WHERE association_name = :association_name AND id != :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":association_name" => $association_name,
			":id" => $id,
		]);
		$result = $prep->fetch();
		return $result['total'] > 0;
	}

	public function count_members($id){
		$fetch = "SELECT COUNT(*) AS total FROM operators WHERE association_id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetch();
	}

	public function delete_data($id){
		$delete = "DELETE FROM associations WHERE id = :id";
		$prep = $this->pdo->prepare($delete);
		return $prep->execute([
			":id" => $id,
		]);
	}

}

class Operators{
	function __construct(private PDO $pdo){}

	public function next_registration_number(){
		$fetch = "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM operators";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		$result = $prep->fetch();
		return "OPR-" . date("Y") . "-" . sprintf("%03d", $result['next_id']);
	}

	public function insert_data($registration_number,$full_name,$contact_number,$email,$address,$association_id,$membership_number,$member_since,$date_registered){
		$insert = "INSERT INTO operators(registration_number, full_name, contact_number, email, address, association_id, membership_number, member_since, date_registered, status)
				   VALUES(:registration_number, :full_name, :contact_number, :email, :address, :association_id, :membership_number, :member_since, :date_registered, 'Active')";
		$prep = $this->pdo->prepare($insert);
		return $prep->execute([
			":registration_number" => $registration_number,
			":full_name" => $full_name,
			":contact_number" => $contact_number,
			":email" => $email,
			":address" => $address,
			":association_id" => $association_id,
			":membership_number" => $membership_number,
			":member_since" => $member_since,
			":date_registered" => $date_registered,
		]);
	}

	public function fetch_data($search,$status,$association_id = ""){
		$fetch = "SELECT o.*, a.association_name,
					(SELECT COUNT(*) FROM vehicles v WHERE v.operator_id = o.id) AS vehicle_count
				  FROM operators o
				  LEFT JOIN associations a ON o.association_id = a.id
				  WHERE 1 = 1";
		$params = [];

		if($search != ""){
			$fetch .= " AND (o.full_name LIKE :s1 OR o.registration_number LIKE :s2 OR o.contact_number LIKE :s3 OR o.email LIKE :s4)";
			$params[":s1"] = "%" . $search . "%";
			$params[":s2"] = "%" . $search . "%";
			$params[":s3"] = "%" . $search . "%";
			$params[":s4"] = "%" . $search . "%";
		}

		if($status != ""){
			$fetch .= " AND o.status = :status";
			$params[":status"] = $status;
		}

		if($association_id == "none"){
			$fetch .= " AND o.association_id IS NULL";
		}
		else if($association_id != ""){
			$fetch .= " AND o.association_id = :association_id";
			$params[":association_id"] = $association_id;
		}

		$fetch .= " ORDER BY o.id ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function fetch_list(){
		$fetch = "SELECT id, registration_number, full_name, status FROM operators ORDER BY full_name ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

	public function count_status(){
		$fetch = "SELECT
					COUNT(*) AS total_operators,
					SUM(status = 'Active') AS active_operators,
					SUM(association_id IS NOT NULL) AS member_operators,
					SUM(status != 'Active') AS inactive_operators
				  FROM operators";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function show_update_data($id){
		$fetch = "SELECT o.*, a.association_name, a.contact_person AS association_contact
				  FROM operators o
				  LEFT JOIN associations a ON o.association_id = a.id
				  WHERE o.id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function update_data($registration_number,$full_name,$contact_number,$email,$address,$association_id,$membership_number,$member_since,$date_registered,$status,$id){
		$update = "UPDATE operators
				   SET registration_number = :registration_number,
				       full_name = :full_name,
				       contact_number = :contact_number,
				       email = :email,
				       address = :address,
				       association_id = :association_id,
				       membership_number = :membership_number,
				       member_since = :member_since,
				       date_registered = :date_registered,
				       status = :status
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":registration_number" => $registration_number,
			":full_name" => $full_name,
			":contact_number" => $contact_number,
			":email" => $email,
			":address" => $address,
			":association_id" => $association_id,
			":membership_number" => $membership_number,
			":member_since" => $member_since,
			":date_registered" => $date_registered,
			":status" => $status,
			":id" => $id,
		]);
	}

	public function registration_exists($registration_number,$id){
		$fetch = "SELECT COUNT(*) AS total FROM operators WHERE registration_number = :registration_number AND id != :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":registration_number" => $registration_number,
			":id" => $id,
		]);
		$result = $prep->fetch();
		return $result['total'] > 0;
	}

	public function fetch_vehicles($id){
		$fetch = "SELECT v.*, r.route_code, r.route_name
				  FROM vehicles v
				  LEFT JOIN assignments a ON a.vehicle_id = v.id AND a.date_ended IS NULL
				  LEFT JOIN routes r ON a.route_id = r.id
				  WHERE v.operator_id = :id
				  ORDER BY v.plate_number ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function fetch_documents($id){
		$fetch = "SELECT * FROM documents WHERE operator_id = :id ORDER BY expiry_date ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function count_links($id){
		$fetch = "SELECT
					(SELECT COUNT(*) FROM vehicles WHERE operator_id = :id1) AS vehicles,
					(SELECT COUNT(*) FROM assignments WHERE operator_id = :id2) AS assignments,
					(SELECT COUNT(*) FROM documents WHERE operator_id = :id3) AS documents";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id1" => $id,
			":id2" => $id,
			":id3" => $id,
		]);
		return $prep->fetch();
	}

	public function delete_data($id){
		$delete = "DELETE FROM operators WHERE id = :id";
		$prep = $this->pdo->prepare($delete);
		return $prep->execute([
			":id" => $id,
		]);
	}

}

class Vehicles{
	function __construct(private PDO $pdo){}

	private $select = "SELECT v.*, o.full_name AS operator_name, o.registration_number AS operator_number,
						a.id AS assignment_id, r.id AS route_id, r.route_code, r.route_name
					   FROM vehicles v
					   JOIN operators o ON v.operator_id = o.id
					   LEFT JOIN assignments a ON a.vehicle_id = v.id AND a.date_ended IS NULL
					   LEFT JOIN routes r ON a.route_id = r.id";

	public function insert_data($plate_number,$vehicle_type,$make,$model,$year_model,$color,$capacity,$engine_number,$chassis_number,$operator_id,$date_registered,$status){
		$insert = "INSERT INTO vehicles(plate_number, vehicle_type, make, model, year_model, color, capacity, engine_number, chassis_number, operator_id, date_registered, status)
				   VALUES(:plate_number, :vehicle_type, :make, :model, :year_model, :color, :capacity, :engine_number, :chassis_number, :operator_id, :date_registered, :status)";
		$prep = $this->pdo->prepare($insert);
		$prep->execute([
			":plate_number" => $plate_number,
			":vehicle_type" => $vehicle_type,
			":make" => $make,
			":model" => $model,
			":year_model" => $year_model,
			":color" => $color,
			":capacity" => $capacity,
			":engine_number" => $engine_number,
			":chassis_number" => $chassis_number,
			":operator_id" => $operator_id,
			":date_registered" => $date_registered,
			":status" => $status,
		]);
		return $this->pdo->lastInsertId();
	}

	public function fetch_data($search,$status,$type = ""){
		$fetch = $this->select . " WHERE 1 = 1";
		$params = [];

		if($search != ""){
			$fetch .= " AND (v.plate_number LIKE :s1 OR o.full_name LIKE :s2 OR v.make LIKE :s3 OR v.model LIKE :s4 OR r.route_name LIKE :s5)";
			$params[":s1"] = "%" . $search . "%";
			$params[":s2"] = "%" . $search . "%";
			$params[":s3"] = "%" . $search . "%";
			$params[":s4"] = "%" . $search . "%";
			$params[":s5"] = "%" . $search . "%";
		}

		if($status != ""){
			$fetch .= " AND v.status = :status";
			$params[":status"] = $status;
		}

		if($type != ""){
			$fetch .= " AND v.vehicle_type = :type";
			$params[":type"] = $type;
		}

		$fetch .= " ORDER BY v.id ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function fetch_list(){
		$fetch = "SELECT v.id, v.plate_number, v.vehicle_type, v.status, o.full_name AS operator_name
				  FROM vehicles v
				  JOIN operators o ON v.operator_id = o.id
				  ORDER BY v.plate_number ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

	public function count_status(){
		$fetch = "SELECT
					COUNT(*) AS total_vehicles,
					SUM(status = 'Active') AS active_vehicles,
					SUM(status = 'Under Repair') AS repair_vehicles,
					SUM(status IN ('Suspended', 'Retired')) AS off_vehicles
				  FROM vehicles";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function show_update_data($id){
		$fetch = $this->select . " WHERE v.id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function update_data($plate_number,$vehicle_type,$make,$model,$year_model,$color,$capacity,$engine_number,$chassis_number,$operator_id,$date_registered,$status,$id){
		$update = "UPDATE vehicles
				   SET plate_number = :plate_number,
				       vehicle_type = :vehicle_type,
				       make = :make,
				       model = :model,
				       year_model = :year_model,
				       color = :color,
				       capacity = :capacity,
				       engine_number = :engine_number,
				       chassis_number = :chassis_number,
				       operator_id = :operator_id,
				       date_registered = :date_registered,
				       status = :status
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":plate_number" => $plate_number,
			":vehicle_type" => $vehicle_type,
			":make" => $make,
			":model" => $model,
			":year_model" => $year_model,
			":color" => $color,
			":capacity" => $capacity,
			":engine_number" => $engine_number,
			":chassis_number" => $chassis_number,
			":operator_id" => $operator_id,
			":date_registered" => $date_registered,
			":status" => $status,
			":id" => $id,
		]);
	}

	public function plate_exists($plate_number,$id){
		$fetch = "SELECT COUNT(*) AS total FROM vehicles WHERE plate_number = :plate_number AND id != :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":plate_number" => $plate_number,
			":id" => $id,
		]);
		$result = $prep->fetch();
		return $result['total'] > 0;
	}

	public function fetch_documents($id){
		$fetch = "SELECT * FROM documents WHERE vehicle_id = :id ORDER BY expiry_date ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function fetch_inspections($id){
		$fetch = "SELECT * FROM inspections WHERE vehicle_id = :id ORDER BY schedule_date DESC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function latest_result($id){
		$fetch = "SELECT result FROM inspections
				  WHERE vehicle_id = :id AND result != 'Pending'
				  ORDER BY inspection_date DESC, id DESC
				  LIMIT 1";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		$result = $prep->fetch();
		if($result){
			return $result['result'];
		}
		return "";
	}

	public function delete_data($id){
		$delete = "DELETE FROM vehicles WHERE id = :id";
		$prep = $this->pdo->prepare($delete);
		return $prep->execute([
			":id" => $id,
		]);
	}

}

class Routes{
	function __construct(private PDO $pdo){}

	public function insert_data($route_code,$route_name,$origin,$destination,$description,$travel_mode,$fare){
		$insert = "INSERT INTO routes(route_code, route_name, origin, destination, description, travel_mode, fare, status)
				   VALUES(:route_code, :route_name, :origin, :destination, :description, :travel_mode, :fare, 'Active')";
		$prep = $this->pdo->prepare($insert);
		$prep->execute([
			":route_code" => $route_code,
			":route_name" => $route_name,
			":origin" => $origin,
			":destination" => $destination,
			":description" => $description,
			":travel_mode" => $travel_mode,
			":fare" => $fare,
		]);
		return $this->pdo->lastInsertId();
	}

	public function fetch_data($search = "", $status = ""){
		$fetch = "SELECT r.*,
					(SELECT COUNT(*) FROM route_stops s WHERE s.route_id = r.id) AS stop_count,
					(SELECT COUNT(*) FROM assignments a WHERE a.route_id = r.id AND a.date_ended IS NULL) AS vehicle_count
				  FROM routes r
				  WHERE 1 = 1";
		$params = [];

		if($search != ""){
			$fetch .= " AND (r.route_code LIKE :s1 OR r.route_name LIKE :s2 OR r.origin LIKE :s3 OR r.destination LIKE :s4
					   OR r.id IN (SELECT route_id FROM route_stops WHERE stop_name LIKE :s5))";
			$params[":s1"] = "%" . $search . "%";
			$params[":s2"] = "%" . $search . "%";
			$params[":s3"] = "%" . $search . "%";
			$params[":s4"] = "%" . $search . "%";
			$params[":s5"] = "%" . $search . "%";
		}

		if($status != ""){
			$fetch .= " AND r.status = :status";
			$params[":status"] = $status;
		}

		$fetch .= " ORDER BY r.route_code ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function fetch_list(){
		$fetch = "SELECT id, route_code, route_name, status FROM routes ORDER BY route_code ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

	public function count_status(){
		$fetch = "SELECT
					COUNT(*) AS total_routes,
					SUM(status = 'Active') AS active_routes,
					SUM(status = 'Inactive') AS inactive_routes,
					(SELECT COUNT(*) FROM route_stops) AS total_stops
				  FROM routes";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function show_update_data($id){
		$fetch = "SELECT * FROM routes WHERE id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function update_data($route_code,$route_name,$origin,$destination,$description,$travel_mode,$fare,$status,$id){
		$update = "UPDATE routes
				   SET route_code = :route_code,
				       route_name = :route_name,
				       origin = :origin,
				       destination = :destination,
				       description = :description,
				       travel_mode = :travel_mode,
				       fare = :fare,
				       status = :status
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":route_code" => $route_code,
			":route_name" => $route_name,
			":origin" => $origin,
			":destination" => $destination,
			":description" => $description,
			":travel_mode" => $travel_mode,
			":fare" => $fare,
			":status" => $status,
			":id" => $id,
		]);
	}

	public function code_exists($route_code,$id){
		$fetch = "SELECT COUNT(*) AS total FROM routes WHERE route_code = :route_code AND id != :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":route_code" => $route_code,
			":id" => $id,
		]);
		$result = $prep->fetch();
		return $result['total'] > 0;
	}

	public function fetch_stops($id){
		$fetch = "SELECT * FROM route_stops WHERE route_id = :id ORDER BY stop_order ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function save_stops($id,$stops){
		$delete = "DELETE FROM route_stops WHERE route_id = :id";
		$prep = $this->pdo->prepare($delete);
		$prep->execute([
			":id" => $id,
		]);

		$insert = "INSERT INTO route_stops(route_id, stop_order, stop_name) VALUES(:route_id, :stop_order, :stop_name)";
		$prep = $this->pdo->prepare($insert);
		$order = 0;
		foreach($stops as $stop){
			$order++;
			$prep->execute([
				":route_id" => $id,
				":stop_order" => $order,
				":stop_name" => $stop,
			]);
		}
	}

	public function fetch_assigned_vehicles($id){
		$fetch = "SELECT v.*, o.full_name AS operator_name, a.date_assigned
				  FROM assignments a
				  JOIN vehicles v ON a.vehicle_id = v.id
				  JOIN operators o ON a.operator_id = o.id
				  WHERE a.route_id = :id AND a.date_ended IS NULL
				  ORDER BY v.plate_number ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function count_assignments($id){
		$fetch = "SELECT COUNT(*) AS total FROM assignments WHERE route_id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetch();
	}

	public function delete_data($id){
		$delete = "DELETE FROM routes WHERE id = :id";
		$prep = $this->pdo->prepare($delete);
		return $prep->execute([
			":id" => $id,
		]);
	}

}

class Assignments{
	function __construct(private PDO $pdo){}

	private $select = "SELECT a.*, v.plate_number, v.vehicle_type, v.status AS vehicle_status,
						o.full_name AS operator_name, r.route_code, r.route_name
					   FROM assignments a
					   JOIN vehicles v ON a.vehicle_id = v.id
					   JOIN operators o ON a.operator_id = o.id
					   JOIN routes r ON a.route_id = r.id";

	public function current_start($vehicle_id){
		$fetch = "SELECT date_assigned FROM assignments WHERE vehicle_id = :vehicle_id AND date_ended IS NULL";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":vehicle_id" => $vehicle_id,
		]);
		$result = $prep->fetch();
		if($result){
			return $result['date_assigned'];
		}
		return "";
	}

	public function assign($vehicle_id,$route_id,$date_assigned,$remarks){
		$fetch = "SELECT operator_id FROM vehicles WHERE id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $vehicle_id,
		]);
		$vehicle = $prep->fetch();

		$end = "UPDATE assignments
				SET date_ended = :date_ended
				WHERE vehicle_id = :vehicle_id AND date_ended IS NULL";
		$prep = $this->pdo->prepare($end);
		$prep->execute([
			":date_ended" => $date_assigned,
			":vehicle_id" => $vehicle_id,
		]);

		$insert = "INSERT INTO assignments(vehicle_id, operator_id, route_id, date_assigned, remarks)
				   VALUES(:vehicle_id, :operator_id, :route_id, :date_assigned, :remarks)";
		$prep = $this->pdo->prepare($insert);
		return $prep->execute([
			":vehicle_id" => $vehicle_id,
			":operator_id" => $vehicle['operator_id'],
			":route_id" => $route_id,
			":date_assigned" => $date_assigned,
			":remarks" => $remarks,
		]);
	}

	public function fetch_current($search,$route_id){
		$fetch = $this->select . " WHERE a.date_ended IS NULL";
		$params = [];

		if($search != ""){
			$fetch .= " AND (v.plate_number LIKE :s1 OR o.full_name LIKE :s2 OR r.route_name LIKE :s3)";
			$params[":s1"] = "%" . $search . "%";
			$params[":s2"] = "%" . $search . "%";
			$params[":s3"] = "%" . $search . "%";
		}

		if($route_id != ""){
			$fetch .= " AND a.route_id = :route_id";
			$params[":route_id"] = $route_id;
		}

		$fetch .= " ORDER BY r.route_code ASC, v.plate_number ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function fetch_history($vehicle_id,$route_id,$operator_id){
		$fetch = $this->select . " WHERE 1 = 1";
		$params = [];

		if($vehicle_id != ""){
			$fetch .= " AND a.vehicle_id = :vehicle_id";
			$params[":vehicle_id"] = $vehicle_id;
		}

		if($route_id != ""){
			$fetch .= " AND a.route_id = :route_id";
			$params[":route_id"] = $route_id;
		}

		if($operator_id != ""){
			$fetch .= " AND a.operator_id = :operator_id";
			$params[":operator_id"] = $operator_id;
		}

		$fetch .= " ORDER BY a.date_assigned DESC, a.id DESC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function fetch_unassigned(){
		$fetch = "SELECT v.id, v.plate_number, v.vehicle_type, v.status, o.full_name AS operator_name
				  FROM vehicles v
				  JOIN operators o ON v.operator_id = o.id
				  WHERE v.id NOT IN (SELECT vehicle_id FROM assignments WHERE date_ended IS NULL)
				  ORDER BY v.plate_number ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetchAll();
	}

	public function count_status(){
		$fetch = "SELECT
					(SELECT COUNT(*) FROM assignments WHERE date_ended IS NULL) AS active_assignments,
					(SELECT COUNT(*) FROM vehicles
					 WHERE id NOT IN (SELECT vehicle_id FROM assignments WHERE date_ended IS NULL)) AS unassigned_vehicles,
					(SELECT COUNT(*) FROM assignments WHERE date_ended IS NOT NULL) AS ended_assignments,
					(SELECT COUNT(DISTINCT route_id) FROM assignments WHERE date_ended IS NULL) AS routes_served";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function end_assignment($id,$date_ended,$remarks){
		$update = "UPDATE assignments
				   SET date_ended = GREATEST(date_assigned, :date_ended),
				       remarks = :remarks
				   WHERE id = :id AND date_ended IS NULL";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":date_ended" => $date_ended,
			":remarks" => $remarks,
			":id" => $id,
		]);
	}

	public function sync_operator($vehicle_id,$date_changed){
		$fetch = "SELECT a.route_id, a.operator_id, v.operator_id AS new_operator
				  FROM assignments a
				  JOIN vehicles v ON a.vehicle_id = v.id
				  WHERE a.vehicle_id = :vehicle_id AND a.date_ended IS NULL";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":vehicle_id" => $vehicle_id,
		]);
		$current = $prep->fetch();
		if($current && $current['operator_id'] != $current['new_operator']){
			$start = $this->current_start($vehicle_id);
			if($date_changed < $start){
				$date_changed = $start;
			}
			$this->assign($vehicle_id, $current['route_id'], $date_changed, "Operator changed");
		}
	}

}

class Documents{
	function __construct(private PDO $pdo){}

	private $select = "SELECT d.*, o.full_name AS operator_name, o.registration_number,
						v.plate_number, v.vehicle_type
					   FROM documents d
					   LEFT JOIN operators o ON d.operator_id = o.id
					   LEFT JOIN vehicles v ON d.vehicle_id = v.id";

	public function insert_data($document_type,$document_number,$operator_id,$vehicle_id,$issued_date,$expiry_date,$remarks){
		$insert = "INSERT INTO documents(document_type, document_number, operator_id, vehicle_id, issued_date, expiry_date, verification_status, remarks)
				   VALUES(:document_type, :document_number, :operator_id, :vehicle_id, :issued_date, :expiry_date, 'Pending', :remarks)";
		$prep = $this->pdo->prepare($insert);
		return $prep->execute([
			":document_type" => $document_type,
			":document_number" => $document_number,
			":operator_id" => $operator_id,
			":vehicle_id" => $vehicle_id,
			":issued_date" => $issued_date,
			":expiry_date" => $expiry_date,
			":remarks" => $remarks,
		]);
	}

	public function fetch_data($search,$holder,$expiry,$verification){
		$fetch = $this->select . " WHERE 1 = 1";
		$params = [];

		if($search != ""){
			$fetch .= " AND (d.document_number LIKE :s1 OR d.document_type LIKE :s2 OR o.full_name LIKE :s3 OR v.plate_number LIKE :s4)";
			$params[":s1"] = "%" . $search . "%";
			$params[":s2"] = "%" . $search . "%";
			$params[":s3"] = "%" . $search . "%";
			$params[":s4"] = "%" . $search . "%";
		}

		if($holder == "Operator"){
			$fetch .= " AND d.operator_id IS NOT NULL";
		}
		if($holder == "Vehicle"){
			$fetch .= " AND d.vehicle_id IS NOT NULL";
		}

		if($expiry == "Valid"){
			$fetch .= " AND d.expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
		}
		if($expiry == "Expiring Soon"){
			$fetch .= " AND d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
		}
		if($expiry == "Expired"){
			$fetch .= " AND d.expiry_date < CURDATE()";
		}

		if($verification != ""){
			$fetch .= " AND d.verification_status = :verification";
			$params[":verification"] = $verification;
		}

		$fetch .= " ORDER BY d.expiry_date ASC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function count_status(){
		$fetch = "SELECT
					COUNT(*) AS total_documents,
					SUM(expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AS expiring_documents,
					SUM(expiry_date < CURDATE()) AS expired_documents,
					SUM(verification_status = 'Pending') AS pending_documents
				  FROM documents";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function show_update_data($id){
		$fetch = $this->select . " WHERE d.id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function update_data($document_type,$document_number,$operator_id,$vehicle_id,$issued_date,$expiry_date,$remarks,$id){
		$update = "UPDATE documents
				   SET document_type = :document_type,
				       document_number = :document_number,
				       operator_id = :operator_id,
				       vehicle_id = :vehicle_id,
				       issued_date = :issued_date,
				       expiry_date = :expiry_date,
				       remarks = :remarks
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":document_type" => $document_type,
			":document_number" => $document_number,
			":operator_id" => $operator_id,
			":vehicle_id" => $vehicle_id,
			":issued_date" => $issued_date,
			":expiry_date" => $expiry_date,
			":remarks" => $remarks,
			":id" => $id,
		]);
	}

	public function set_verification($verification_status,$remarks,$id){
		$verified_date = null;
		if($verification_status != "Pending"){
			$verified_date = date("Y-m-d");
		}
		$update = "UPDATE documents
				   SET verification_status = :verification_status,
				       verified_date = :verified_date,
				       remarks = :remarks
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":verification_status" => $verification_status,
			":verified_date" => $verified_date,
			":remarks" => $remarks,
			":id" => $id,
		]);
	}

	public function renew($id,$renewal_date,$new_expiry,$remarks){
		$fetch = "SELECT expiry_date FROM documents WHERE id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		$document = $prep->fetch();

		$insert = "INSERT INTO renewals(document_id, renewal_date, previous_expiry, new_expiry, remarks)
				   VALUES(:document_id, :renewal_date, :previous_expiry, :new_expiry, :remarks)";
		$prep = $this->pdo->prepare($insert);
		$prep->execute([
			":document_id" => $id,
			":renewal_date" => $renewal_date,
			":previous_expiry" => $document['expiry_date'],
			":new_expiry" => $new_expiry,
			":remarks" => $remarks,
		]);

		$update = "UPDATE documents
				   SET expiry_date = :new_expiry,
				       verification_status = 'Pending',
				       verified_date = NULL
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":new_expiry" => $new_expiry,
			":id" => $id,
		]);
	}

	public function fetch_renewals($id){
		$fetch = "SELECT * FROM renewals WHERE document_id = :id ORDER BY renewal_date DESC, id DESC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function delete_data($id){
		$delete = "DELETE FROM documents WHERE id = :id";
		$prep = $this->pdo->prepare($delete);
		return $prep->execute([
			":id" => $id,
		]);
	}

}

class Inspections{
	function __construct(private PDO $pdo){}

	private $select = "SELECT i.*, v.plate_number, v.vehicle_type, v.status AS vehicle_status, o.full_name AS operator_name
					   FROM inspections i
					   JOIN vehicles v ON i.vehicle_id = v.id
					   JOIN operators o ON v.operator_id = o.id";

	public function insert_data($vehicle_id,$schedule_date,$remarks){
		$insert = "INSERT INTO inspections(vehicle_id, schedule_date, result, remarks)
				   VALUES(:vehicle_id, :schedule_date, 'Pending', :remarks)";
		$prep = $this->pdo->prepare($insert);
		return $prep->execute([
			":vehicle_id" => $vehicle_id,
			":schedule_date" => $schedule_date,
			":remarks" => $remarks,
		]);
	}

	public function fetch_data($search,$status){
		$fetch = $this->select . " WHERE 1 = 1";
		$params = [];

		if($search != ""){
			$fetch .= " AND (v.plate_number LIKE :s1 OR o.full_name LIKE :s2 OR i.inspector LIKE :s3 OR i.defects LIKE :s4)";
			$params[":s1"] = "%" . $search . "%";
			$params[":s2"] = "%" . $search . "%";
			$params[":s3"] = "%" . $search . "%";
			$params[":s4"] = "%" . $search . "%";
		}

		if($status == "Scheduled"){
			$fetch .= " AND i.result = 'Pending' AND i.schedule_date >= CURDATE()";
		}
		if($status == "Overdue"){
			$fetch .= " AND i.result = 'Pending' AND i.schedule_date < CURDATE()";
		}
		if($status == "Passed"){
			$fetch .= " AND i.result = 'Passed'";
		}
		if($status == "Failed"){
			$fetch .= " AND i.result = 'Failed'";
		}

		$fetch .= " ORDER BY i.schedule_date DESC, i.id DESC";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute($params);
		return $prep->fetchAll();
	}

	public function count_status(){
		$fetch = "SELECT
					SUM(result = 'Pending' AND schedule_date >= CURDATE()) AS scheduled_inspections,
					SUM(result = 'Pending' AND schedule_date < CURDATE()) AS overdue_inspections,
					SUM(result = 'Passed') AS passed_inspections,
					SUM(result = 'Failed') AS failed_inspections
				  FROM inspections";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute();
		return $prep->fetch();
	}

	public function show_update_data($id){
		$fetch = $this->select . " WHERE i.id = :id";
		$prep = $this->pdo->prepare($fetch);
		$prep->execute([
			":id" => $id,
		]);
		return $prep->fetchAll();
	}

	public function update_data($vehicle_id,$schedule_date,$inspection_date,$inspector,$result,$defects,$remarks,$id){
		$update = "UPDATE inspections
				   SET vehicle_id = :vehicle_id,
				       schedule_date = :schedule_date,
				       inspection_date = :inspection_date,
				       inspector = :inspector,
				       result = :result,
				       defects = :defects,
				       remarks = :remarks
				   WHERE id = :id";
		$prep = $this->pdo->prepare($update);
		return $prep->execute([
			":vehicle_id" => $vehicle_id,
			":schedule_date" => $schedule_date,
			":inspection_date" => $inspection_date,
			":inspector" => $inspector,
			":result" => $result,
			":defects" => $defects,
			":remarks" => $remarks,
			":id" => $id,
		]);
	}

	public function delete_data($id){
		$delete = "DELETE FROM inspections WHERE id = :id";
		$prep = $this->pdo->prepare($delete);
		return $prep->execute([
			":id" => $id,
		]);
	}

}
?>
