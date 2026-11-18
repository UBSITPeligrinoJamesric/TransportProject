<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$search = "";
	$status = "";
	$vehicle_id = "";
	if(isset($_GET['search'])){
		$search = trim($_GET['search']);
	}
	if(isset($_GET['status'])){
		$status = $_GET['status'];
	}
	if(isset($_GET['vehicle_id'])){
		$vehicle_id = $_GET['vehicle_id'];
	}

	$conn = new Inspections($pdo);
	$vehicle_conn = new Vehicles($pdo);

	$rows = $conn->fetch_data($search, $status);
	$counts = $conn->count_status();
	$vehicles = $vehicle_conn->fetch_list();

	$per_page = 10;
	$page = 1;
	if(isset($_GET['page'])){
		$page = max(1, (int)$_GET['page']);
	}
	$total_rows = count($rows);
	$rows = array_slice($rows, ($page - 1) * $per_page, $per_page);

	$page_title = "Vehicle Inspection Records";
	$base = "../";
	include "../header.php";
?>
	<div class="cards">
		<a class="card" href="manage_inspections.php?status=Scheduled" style="color:inherit;">
			<div>
				<div class="card-label">Scheduled</div>
				<div class="card-number"><?php echo (int)$counts['scheduled_inspections']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_clock; ?></span>
		</a>
		<a class="card card-orange" href="manage_inspections.php?status=Overdue" style="color:inherit;">
			<div>
				<div class="card-label">Overdue</div>
				<div class="card-number"><?php echo (int)$counts['overdue_inspections']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</a>
		<a class="card card-green" href="manage_inspections.php?status=Passed" style="color:inherit;">
			<div>
				<div class="card-label">Passed (Compliant)</div>
				<div class="card-number"><?php echo (int)$counts['passed_inspections']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_check; ?></span>
		</a>
		<a class="card card-red" href="manage_inspections.php?status=Failed" style="color:inherit;">
			<div>
				<div class="card-label">Failed (Non-Compliant)</div>
				<div class="card-number"><?php echo (int)$counts['failed_inspections']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</a>
	</div>

	<div class="split">

		<div class="panel">
			<form method="get" class="toolbar">
				<label class="search-box">
					<?php echo $icon_search; ?>
					<input type="text" name="search" placeholder="Search plate, operator, defects..." value="<?php echo htmlspecialchars($search); ?>">
				</label>
				<select name="status" onchange="this.form.submit()">
					<option value="">All Inspections</option>
					<option value="Scheduled" <?php if($status == "Scheduled"){ echo "selected"; } ?>>Scheduled</option>
					<option value="Overdue" <?php if($status == "Overdue"){ echo "selected"; } ?>>Overdue</option>
					<option value="Passed" <?php if($status == "Passed"){ echo "selected"; } ?>>Passed</option>
					<option value="Failed" <?php if($status == "Failed"){ echo "selected"; } ?>>Failed</option>
				</select>
				<?php if($search != "" || $status != ""){ ?>
					<a class="btn btn-light btn-small" href="manage_inspections.php">Clear</a>
				<?php } ?>
			</form>

			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Vehicle</th>
					<th>Schedule</th>
					<th>Inspected</th>
					<th>Compliance</th>
					<th>Defects</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($rows) == 0){ ?>
				<tr><td colspan="6" class="empty">No inspections found.</td></tr>
				<?php } ?>
				<?php foreach($rows as $row){ ?>
				<tr>
					<td><a class="id-link" href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($row['operator_name']); ?></span></td>
					<td class="nowrap"><?php echo date("M d, Y", strtotime($row['schedule_date'])); ?></td>
					<td class="nowrap">
						<?php if($row['inspection_date'] != null){ ?>
							<?php echo date("M d, Y", strtotime($row['inspection_date'])); ?><span class="sub"><?php echo htmlspecialchars((string)$row['inspector']); ?></span>
						<?php } else { ?>
							<span class="muted">Not yet</span>
						<?php } ?>
					</td>
					<td><?php $badge_result = $row['result']; $badge_schedule = $row['schedule_date']; include "../badges/compliance.php"; ?></td>
					<td class="muted"><?php if($row['defects'] != ""){ echo htmlspecialchars(mb_strimwidth($row['defects'], 0, 40, "...")); } else { echo "-"; } ?></td>
					<td>
						<div class="actions">
							<a class="icon-btn" title="View" href="view_inspection.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_view; ?></a>
							<a class="icon-btn" title="Record result / Edit" href="edit_inspection.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_edit; ?></a>
							<form method="post" action="delete_inspection.php" onsubmit="return confirm('Delete this inspection record?');">
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
			<div class="panel-head">Schedule Inspection</div>
			<div class="panel-body">
				<form method="post" action="add_inspection.php">
					<div class="form-row">
						<label>Vehicle</label>
						<select name="vehicle_id" required>
							<option value="">Select vehicle</option>
							<?php foreach($vehicles as $v){ ?>
							<option value="<?php echo (int)$v['id']; ?>" <?php if($vehicle_id == $v['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($v['plate_number']); ?> - <?php echo htmlspecialchars($v['operator_name']); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-row">
						<label>Inspection Schedule</label>
						<input type="date" name="schedule_date" value="<?php echo date("Y-m-d", strtotime("+7 days")); ?>" required>
					</div>
					<div class="form-row">
						<label>Remarks (optional)</label>
						<textarea name="remarks" rows="2" placeholder="e.g. Annual inspection"></textarea>
					</div>
					<span class="small-note" style="margin:-4px 0 12px 0;">Record the result after the inspection using the edit button.</span>
					<div class="form-buttons">
						<button class="btn" name="submit">Schedule</button>
						<button class="btn btn-light" type="reset">Cancel</button>
					</div>
				</form>
			</div>
		</div>

	</div>
<?php
	include "../footer.php";
?>
