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

	$conn = new Complaints($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$route_conn = new Routes($pdo);

	$rows = $conn->fetch_data($search, $type, $status);
	$counts = $conn->count_status();
	$vehicles = $vehicle_conn->fetch_list();
	$routes = $route_conn->fetch_list();

	$form = [
		"complainant_name" => "",
		"contact_number" => "",
		"vehicle_id" => "",
		"route_id" => "",
		"concern" => "",
		"incident_date" => $today,
		"incident_location" => "",
		"description" => "",
	];

	$tabs = [
		"" => "All Complaints",
		"Passenger Complaint" => "Passenger Complaints",
		"Route-Related" => "Route-Related",
		"Vehicle-Related" => "Vehicle-Related",
	];

	$per_page = 10;
	$page = 1;
	if(isset($_GET['page'])){
		$page = max(1, (int)$_GET['page']);
	}
	$total_rows = count($rows);
	$rows = array_slice($rows, ($page - 1) * $per_page, $per_page);

	$page_title = "Transportation Complaint Management";
	$base = "../";
	include "../header.php";
?>
	<div class="tabs">
		<?php foreach($tabs as $key => $label){ ?>
		<a class="<?php if($type == $key){ echo "current"; } ?>" href="manage_complaints.php?type=<?php echo urlencode($key); ?>"><?php echo $label; ?></a>
		<?php } ?>
	</div>

	<div class="cards">
		<div class="card">
			<div>
				<div class="card-label">Total Complaints</div>
				<div class="card-number"><?php echo (int)$counts['total_complaints']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_complaint; ?></span>
		</div>
		<a class="card card-orange" href="manage_complaints.php?status=Pending" style="color:inherit;">
			<div>
				<div class="card-label">Pending</div>
				<div class="card-number"><?php echo (int)$counts['pending_complaints']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_clock; ?></span>
		</a>
		<a class="card" href="manage_complaints.php?status=Under+Investigation" style="color:inherit;">
			<div>
				<div class="card-label">Under Investigation</div>
				<div class="card-number"><?php echo (int)$counts['investigating_complaints']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_search; ?></span>
		</a>
		<a class="card card-green" href="manage_complaints.php?status=Resolved" style="color:inherit;">
			<div>
				<div class="card-label">Resolved</div>
				<div class="card-number"><?php echo (int)$counts['resolved_complaints']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_check; ?></span>
		</a>
	</div>

	<div class="split">

		<div class="panel">
			<form method="get" class="toolbar">
				<input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
				<label class="search-box">
					<?php echo $icon_search; ?>
					<input type="text" name="search" placeholder="Search no., plate, operator, name..." value="<?php echo htmlspecialchars($search); ?>">
				</label>
				<select name="status" onchange="this.form.submit()">
					<option value="">All Status</option>
					<?php foreach($complaint_statuses as $s){ ?>
					<option value="<?php echo $s; ?>" <?php if($status == $s){ echo "selected"; } ?>><?php echo $s; ?></option>
					<?php } ?>
				</select>
				<?php if($search != "" || $status != ""){ ?>
					<a class="btn btn-light btn-small" href="manage_complaints.php?type=<?php echo urlencode($type); ?>">Clear</a>
				<?php } ?>
			</form>

			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Complaint No.</th>
					<th>Concern</th>
					<th>Vehicle / Route</th>
					<th>Complainant</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($rows) == 0){ ?>
				<tr><td colspan="6" class="empty">No complaints found.</td></tr>
				<?php } ?>
				<?php foreach($rows as $row){ ?>
				<tr>
					<td class="nowrap"><a class="id-link" href="view_complaint.php?id=<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['complaint_number']); ?></a><span class="sub"><?php echo date("M d, Y", strtotime($row['date_filed'])); ?></span></td>
					<td class="strong"><?php echo htmlspecialchars($row['concern']); ?><span class="sub"><?php echo htmlspecialchars($row['complaint_type']); ?></span></td>
					<td>
						<?php if($row['plate_number'] != null){ ?>
							<a class="id-link" href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a>
						<?php } else { ?>
							<span class="muted">No plate</span>
						<?php } ?>
						<span class="sub"><?php if($row['route_code'] != null){ echo htmlspecialchars($row['route_code']); } else { echo "No route"; } ?></span>
					</td>
					<td><?php echo htmlspecialchars($row['complainant_name']); ?><span class="sub"><?php if($row['user_id'] != null){ echo "Online report"; } else if($row['account_deleted'] == 1){ echo "Online (account deleted)"; } else { echo "Walk-in"; } ?></span></td>
					<td><?php $badge_status = $row['status']; include "../badges/complaint_status.php"; ?></td>
					<td>
						<div class="actions">
							<a class="btn btn-light btn-small" href="view_complaint.php?id=<?php echo (int)$row['id']; ?>">View</a>
							<form method="post" action="delete_complaint.php" onsubmit="return confirm('Delete this complaint and its investigation records?');">
								<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
								<button class="btn btn-light btn-small btn-end" name="delete">Delete</button>
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
			<div class="panel-head">Record Walk-in Complaint</div>
			<div class="panel-body">
				<form method="post" action="add_complaint.php">
					<?php include "complaint_form.php"; ?>
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
