<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$vehicle_id = "";
	$route_id = "";
	$operator_id = "";
	if(isset($_GET['vehicle_id'])){
		$vehicle_id = $_GET['vehicle_id'];
	}
	if(isset($_GET['route_id'])){
		$route_id = $_GET['route_id'];
	}
	if(isset($_GET['operator_id'])){
		$operator_id = $_GET['operator_id'];
	}

	$conn = new Assignments($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$route_conn = new Routes($pdo);
	$operator_conn = new Operators($pdo);

	$rows = $conn->fetch_history($vehicle_id, $route_id, $operator_id);
	$vehicles = $vehicle_conn->fetch_list();
	$routes = $route_conn->fetch_list();
	$operators = $operator_conn->fetch_list();

	$per_page = 15;
	$page = 1;
	if(isset($_GET['page'])){
		$page = max(1, (int)$_GET['page']);
	}
	$total_rows = count($rows);
	$rows = array_slice($rows, ($page - 1) * $per_page, $per_page);

	$page_title = "Operator and Vehicle Assignment";
	$base = "../";
	include "../header.php";
?>
	<div class="tabs">
		<a href="manage_assignments.php">Current Assignments</a>
		<a class="current" href="assignment_history.php">Assignment History</a>
	</div>

	<div class="panel">
		<form method="get" class="toolbar">
			<select name="vehicle_id" onchange="this.form.submit()">
				<option value="">All Vehicles</option>
				<?php foreach($vehicles as $v){ ?>
				<option value="<?php echo (int)$v['id']; ?>" <?php if($vehicle_id == $v['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($v['plate_number']); ?></option>
				<?php } ?>
			</select>
			<select name="operator_id" onchange="this.form.submit()">
				<option value="">All Operators</option>
				<?php foreach($operators as $o){ ?>
				<option value="<?php echo (int)$o['id']; ?>" <?php if($operator_id == $o['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($o['full_name']); ?></option>
				<?php } ?>
			</select>
			<select name="route_id" onchange="this.form.submit()">
				<option value="">All Routes</option>
				<?php foreach($routes as $r){ ?>
				<option value="<?php echo (int)$r['id']; ?>" <?php if($route_id == $r['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($r['route_code']); ?></option>
				<?php } ?>
			</select>
			<?php if($vehicle_id != "" || $route_id != "" || $operator_id != ""){ ?>
				<a class="btn btn-light btn-small" href="assignment_history.php">Clear</a>
			<?php } ?>
		</form>

		<div class="table-wrap">
		<table>
			<thead>
			<tr>
				<th>Vehicle</th>
				<th>Operator</th>
				<th>Route</th>
				<th>Date Assigned</th>
				<th>Date Ended</th>
				<th>Status</th>
				<th>Remarks</th>
			</tr>
			</thead>
			<tbody>
			<?php if(count($rows) == 0){ ?>
			<tr><td colspan="7" class="empty">No assignment records found.</td></tr>
			<?php } ?>
			<?php foreach($rows as $row){ ?>
			<tr>
				<td><a class="id-link" href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a></td>
				<td><?php echo htmlspecialchars($row['operator_name']); ?></td>
				<td><?php echo htmlspecialchars($row['route_code']); ?><span class="sub"><?php echo htmlspecialchars($row['route_name']); ?></span></td>
				<td class="nowrap"><?php echo date("M d, Y", strtotime($row['date_assigned'])); ?></td>
				<td class="nowrap"><?php if($row['date_ended'] != null){ echo date("M d, Y", strtotime($row['date_ended'])); } else { echo '<span class="muted">-</span>'; } ?></td>
				<td>
					<?php if($row['date_ended'] == null){ ?>
						<span class="badge badge-green">Current</span>
					<?php } else { ?>
						<span class="badge badge-gray">Ended</span>
					<?php } ?>
				</td>
				<td class="muted"><?php echo htmlspecialchars((string)$row['remarks']); ?></td>
			</tr>
			<?php } ?>
			</tbody>
		</table>
		</div>

		<?php include "../page_links.php"; ?>
	</div>
<?php
	include "../footer.php";
?>
