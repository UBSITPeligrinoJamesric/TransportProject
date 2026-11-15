<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$search = "";
	$type = "";
	$status = "";
	if(isset($_GET['search'])){
		$search = trim($_GET['search']);
	}
	if(isset($_GET['type'])){
		$type = $_GET['type'];
	}
	if(isset($_GET['status'])){
		$status = $_GET['status'];
	}

	$conn = new Vehicles($pdo);
	$operator_conn = new Operators($pdo);

	$rows = $conn->fetch_data($search, $status, $type);
	$counts = $conn->count_status();
	$operators = $operator_conn->fetch_list();

	$form = [
		"plate_number" => "",
		"vehicle_type" => "Jeepney",
		"make" => "",
		"model" => "",
		"year_model" => date("Y"),
		"color" => "",
		"capacity" => "",
		"engine_number" => "",
		"chassis_number" => "",
		"operator_id" => "",
		"date_registered" => $today,
		"status" => "Active",
		"or_cr_number" => "",
		"or_cr_expiry" => "",
	];

	$per_page = 10;
	$page = 1;
	if(isset($_GET['page'])){
		$page = max(1, (int)$_GET['page']);
	}
	$total_rows = count($rows);
	$rows = array_slice($rows, ($page - 1) * $per_page, $per_page);

	$page_title = "Vehicle Registration";
	$base = "../";
	include "../header.php";
?>
	<div class="cards">
		<div class="card">
			<div>
				<div class="card-label">Registered Vehicles</div>
				<div class="card-number"><?php echo (int)$counts['total_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_vehicle; ?></span>
		</div>
		<div class="card card-green">
			<div>
				<div class="card-label">Active</div>
				<div class="card-number"><?php echo (int)$counts['active_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_check; ?></span>
		</div>
		<div class="card card-orange">
			<div>
				<div class="card-label">Under Repair</div>
				<div class="card-number"><?php echo (int)$counts['repair_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_clock; ?></span>
		</div>
		<div class="card card-red">
			<div>
				<div class="card-label">Suspended / Retired</div>
				<div class="card-number"><?php echo (int)$counts['off_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</div>
	</div>

	<div class="split">

		<div class="panel">
			<form method="get" class="toolbar">
				<label class="search-box">
					<?php echo $icon_search; ?>
					<input type="text" name="search" placeholder="Search plate, make, operator..." value="<?php echo htmlspecialchars($search); ?>">
				</label>
				<select name="type" onchange="this.form.submit()">
					<option value="">All Types</option>
					<?php foreach($vehicle_types as $t){ ?>
					<option value="<?php echo $t; ?>" <?php if($type == $t){ echo "selected"; } ?>><?php echo $t; ?></option>
					<?php } ?>
				</select>
				<select name="status" onchange="this.form.submit()">
					<option value="">All Status</option>
					<?php foreach($vehicle_statuses as $s){ ?>
					<option value="<?php echo $s; ?>" <?php if($status == $s){ echo "selected"; } ?>><?php echo $s; ?></option>
					<?php } ?>
				</select>
				<?php if($search != "" || $type != "" || $status != ""){ ?>
					<a class="btn btn-light btn-small" href="manage_vehicles.php">Clear</a>
				<?php } ?>
			</form>

			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Plate No.</th>
					<th>Type</th>
					<th>Capacity</th>
					<th>Operator</th>
					<th>Route</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($rows) == 0){ ?>
				<tr><td colspan="7" class="empty">No vehicles found.</td></tr>
				<?php } ?>
				<?php foreach($rows as $row){ ?>
				<tr>
					<td><a class="id-link" href="view_vehicle.php?id=<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($row['make'] . " " . $row['model']); ?></span></td>
					<td class="strong"><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
					<td class="nowrap"><?php echo (int)$row['capacity']; ?> seats</td>
					<td><a href="../operator_management/view_operator.php?id=<?php echo (int)$row['operator_id']; ?>" style="color:inherit;"><?php echo htmlspecialchars($row['operator_name']); ?></a></td>
					<td>
						<?php if($row['route_code'] != null){ ?>
							<a class="id-link" href="../route_management/view_route.php?id=<?php echo (int)$row['route_id']; ?>" title="<?php echo htmlspecialchars($row['route_name']); ?>"><?php echo htmlspecialchars($row['route_code']); ?></a>
						<?php } else { ?>
							<span class="muted">None</span>
						<?php } ?>
					</td>
					<td><?php $badge_status = $row['status']; include "../badges/vehicle_status.php"; ?></td>
					<td>
						<div class="actions">
							<a class="icon-btn" title="View" href="view_vehicle.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_view; ?></a>
							<a class="icon-btn" title="Edit" href="edit_vehicle.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_edit; ?></a>
							<form method="post" action="delete_vehicle.php" onsubmit="return confirm('Delete this vehicle? Its documents, inspections and assignment history will also be deleted. To keep the records, set its status to Retired instead.');">
								<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
								<button class="icon-btn danger" title="Delete" name="delete"><?php echo $icon_delete; ?></button>
							</form>
						</div>
					</td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>

			<?php include "../page_links.php"; ?>
		</div>

		<div class="panel" id="add">
			<div class="panel-head">Register Vehicle</div>
			<div class="panel-body">
				<?php if(count($operators) == 0){ ?>
				<div class="alert">Register an operator first.</div>
				<?php } ?>
				<form method="post" action="add_vehicle.php">
					<?php include "vehicle_form.php"; ?>
					<div class="form-buttons">
						<button class="btn" name="submit">Save</button>
						<button class="btn btn-light" type="reset">Cancel</button>
					</div>
				</form>
			</div>
		</div>

	</div>
<?php
	include "../footer.php";
?>
