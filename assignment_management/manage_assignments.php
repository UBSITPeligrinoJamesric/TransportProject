<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$search = "";
	$route_id = "";
	$vehicle_id = "";
	if(isset($_GET['search'])){
		$search = trim($_GET['search']);
	}
	if(isset($_GET['route_id'])){
		$route_id = $_GET['route_id'];
	}
	if(isset($_GET['vehicle_id'])){
		$vehicle_id = $_GET['vehicle_id'];
	}

	$conn = new Assignments($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$route_conn = new Routes($pdo);

	$rows = $conn->fetch_current($search, $route_id);
	$counts = $conn->count_status();
	$unassigned = $conn->fetch_unassigned();
	$vehicles = $vehicle_conn->fetch_data("", "", "");
	$routes = $route_conn->fetch_list();

	$page_title = "Operator and Vehicle Assignment";
	$base = "../";
	include "../header.php";
?>
	<div class="tabs">
		<a class="current" href="manage_assignments.php">Current Assignments</a>
		<a href="assignment_history.php">Assignment History</a>
	</div>

	<?php if(isset($_GET['error'])){ ?>
	<div class="alert alert-red">
		<?php if($_GET['error'] == "same"){ ?>That vehicle is already assigned to that route.<?php } ?>
		<?php if($_GET['error'] == "vehicle"){ ?>Only Active or Under Repair vehicles can be assigned. Change the vehicle status first.<?php } ?>
		<?php if($_GET['error'] == "route"){ ?>That route is Inactive. Activate it first.<?php } ?>
		<?php if($_GET['error'] == "date"){ ?>The date assigned cannot be earlier than the start of the vehicle's current assignment.<?php } ?>
	</div>
	<?php } ?>

	<div class="cards">
		<div class="card card-green">
			<div>
				<div class="card-label">Active Assignments</div>
				<div class="card-number"><?php echo (int)$counts['active_assignments']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_assign; ?></span>
		</div>
		<div class="card card-orange">
			<div>
				<div class="card-label">Unassigned Vehicles</div>
				<div class="card-number"><?php echo (int)$counts['unassigned_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_vehicle; ?></span>
		</div>
		<div class="card">
			<div>
				<div class="card-label">Routes Served</div>
				<div class="card-number"><?php echo (int)$counts['routes_served']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_route; ?></span>
		</div>
		<a class="card card-gray" href="assignment_history.php" style="color:inherit;">
			<div>
				<div class="card-label">Ended (History)</div>
				<div class="card-number"><?php echo (int)$counts['ended_assignments']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_clock; ?></span>
		</a>
	</div>

	<div class="split">

		<div class="stack">
			<div class="panel">
				<form method="get" class="toolbar">
					<label class="search-box">
						<?php echo $icon_search; ?>
						<input type="text" name="search" placeholder="Search plate, operator or route..." value="<?php echo htmlspecialchars($search); ?>">
					</label>
					<select name="route_id" onchange="this.form.submit()">
						<option value="">All Routes</option>
						<?php foreach($routes as $r){ ?>
						<option value="<?php echo (int)$r['id']; ?>" <?php if($route_id == $r['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($r['route_code'] . " - " . $r['route_name']); ?></option>
						<?php } ?>
					</select>
					<?php if($search != "" || $route_id != ""){ ?>
						<a class="btn btn-light btn-small" href="manage_assignments.php">Clear</a>
					<?php } ?>
				</form>

				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Vehicle</th>
						<th>Operator</th>
						<th>Route</th>
						<th>Since</th>
						<th>Vehicle Status</th>
						<th>Actions</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($rows) == 0){ ?>
					<tr><td colspan="6" class="empty">No current assignments found.</td></tr>
					<?php } ?>
					<?php foreach($rows as $row){ ?>
					<tr>
						<td><a class="id-link" href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($row['vehicle_type']); ?></span></td>
						<td><a href="../operator_management/view_operator.php?id=<?php echo (int)$row['operator_id']; ?>" style="color:inherit;"><?php echo htmlspecialchars($row['operator_name']); ?></a></td>
						<td><a class="id-link" href="../route_management/view_route.php?id=<?php echo (int)$row['route_id']; ?>"><?php echo htmlspecialchars($row['route_code']); ?></a><span class="sub"><?php echo htmlspecialchars($row['route_name']); ?></span></td>
						<td class="nowrap"><?php echo date("M d, Y", strtotime($row['date_assigned'])); ?></td>
						<td><?php $badge_status = $row['vehicle_status']; include "../badges/vehicle_status.php"; ?></td>
						<td>
							<div class="actions">
								<a class="icon-btn" title="Reassign" href="manage_assignments.php?vehicle_id=<?php echo (int)$row['vehicle_id']; ?>#assign"><?php echo $icon_assign; ?></a>
								<form method="post" action="end_assignment.php" onsubmit="return confirm('End this assignment? It will move to the history.');">
									<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
									<button class="icon-btn danger" title="End assignment" name="end"><?php echo $icon_minus; ?></button>
								</form>
							</div>
						</td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<span>Unassigned Vehicles</span>
					<span class="route-line"><?php echo count($unassigned); ?> <?php if(count($unassigned) == 1){ echo "vehicle"; } else { echo "vehicles"; } ?></span>
				</div>
				<div class="table-wrap">
				<table>
					<tbody>
					<?php if(count($unassigned) == 0){ ?>
					<tr><td class="empty">Every vehicle has a route.</td></tr>
					<?php } ?>
					<?php foreach($unassigned as $u){ ?>
					<tr>
						<td><a class="id-link" href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$u['id']; ?>"><?php echo htmlspecialchars($u['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($u['vehicle_type']); ?></span></td>
						<td><?php echo htmlspecialchars($u['operator_name']); ?></td>
						<td><?php $badge_status = $u['status']; include "../badges/vehicle_status.php"; ?></td>
						<td><a class="btn btn-light btn-small" href="manage_assignments.php?vehicle_id=<?php echo (int)$u['id']; ?>#assign">Assign</a></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>

		<div class="panel" id="assign">
			<div class="panel-head">Assign Vehicle to Route</div>
			<div class="panel-body">
				<form method="post" action="assign.php">
					<div class="form-row">
						<label>Vehicle</label>
						<select name="vehicle_id" required>
							<option value="">Select vehicle</option>
							<?php foreach($vehicles as $v){ ?>
							<option value="<?php echo (int)$v['id']; ?>" <?php if($vehicle_id == $v['id']){ echo "selected"; } ?>>
								<?php echo htmlspecialchars($v['plate_number']); ?> - <?php echo htmlspecialchars($v['operator_name']); ?><?php if($v['route_code'] != null){ echo " (now " . htmlspecialchars($v['route_code']) . ")"; } ?>
							</option>
							<?php } ?>
						</select>
						<span class="small-note">The vehicle's operator is saved with the assignment.</span>
					</div>
					<div class="form-row">
						<label>Route</label>
						<select name="route_id" required>
							<option value="">Select route</option>
							<?php foreach($routes as $r){ ?>
							<option value="<?php echo (int)$r['id']; ?>" <?php if($r['status'] != "Active"){ echo "disabled"; } ?>>
								<?php echo htmlspecialchars($r['route_code'] . " - " . $r['route_name']); ?><?php if($r['status'] != "Active"){ echo " (Inactive)"; } ?>
							</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-row">
						<label>Date Assigned</label>
						<input type="date" name="date_assigned" value="<?php echo $today; ?>" required>
					</div>
					<div class="form-row">
						<label>Remarks (optional)</label>
						<input type="text" name="remarks" maxlength="255">
					</div>
					<span class="small-note" style="margin:-4px 0 12px 0;">If the vehicle already has a route, that assignment ends on this date and moves to the history.</span>
					<div class="form-buttons">
						<button class="btn" name="submit">Assign</button>
						<button class="btn btn-light" type="reset">Cancel</button>
					</div>
				</form>
			</div>
		</div>

	</div>
<?php
	include "../footer.php";
?>
