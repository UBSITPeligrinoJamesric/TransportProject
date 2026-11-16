<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";

	$conn = new Assignments($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$route_conn = new Routes($pdo);

	if(isset($_POST['submit'])){
		$vehicle_id = $_POST['vehicle_id'];
		$route_id = $_POST['route_id'];
		$date_assigned = $_POST['date_assigned'];
		$remarks = trim($_POST['remarks']);
		if($remarks == ""){
			$remarks = null;
		}

		$vehicle = $vehicle_conn->show_update_data($vehicle_id);
		$route = $route_conn->show_update_data($route_id);

		if(count($vehicle) == 0 || count($route) == 0){
			header("location:manage_assignments.php");
			exit();
		}
		if($vehicle[0]['route_id'] == $route_id){
			header("location:manage_assignments.php?error=same");
			exit();
		}
		if($vehicle[0]['status'] != "Active" && $vehicle[0]['status'] != "Under Repair"){
			header("location:manage_assignments.php?error=vehicle");
			exit();
		}
		if($date_assigned < $conn->current_start($vehicle_id)){
			header("location:manage_assignments.php?error=date");
			exit();
		}
		if($route[0]['status'] != "Active"){
			header("location:manage_assignments.php?error=route");
			exit();
		}

		$conn->assign($vehicle_id, $route_id, $date_assigned, $remarks);
	}

	header("location:manage_assignments.php");
	exit();
?>
