<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Inspections($pdo);

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);

	$page_title = "Inspection Details";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_inspections.php"><?php echo $icon_back; ?> Back to Inspections</a>

	<?php if(count($rows) == 0){ ?>
	<div class="alert">Inspection not found.</div>
	<?php } ?>

	<?php foreach($rows as $row){ ?>
	<div class="split">

		<div class="panel">
			<div class="panel-head">
				<span>Defects and Remarks</span>
				<?php $badge_result = $row['result']; $badge_schedule = $row['schedule_date']; include "../badges/compliance.php"; ?>
			</div>
			<div class="panel-body">
				<div class="form-section">Defects Found</div>
				<?php if($row['defects'] != ""){ ?>
				<p class="note-text" style="margin:0 0 16px 0;"><?php echo htmlspecialchars($row['defects']); ?></p>
				<?php } else { ?>
				<p class="muted" style="margin:0 0 16px 0;"><?php if($row['result'] == "Pending"){ echo "Not inspected yet."; } else { echo "No defects found."; } ?></p>
				<?php } ?>
				<div class="form-section section-gap">Remarks</div>
				<?php if($row['remarks'] != ""){ ?>
				<p class="note-text" style="margin:0;"><?php echo htmlspecialchars($row['remarks']); ?></p>
				<?php } else { ?>
				<p class="muted" style="margin:0;">None</p>
				<?php } ?>
			</div>
		</div>

		<div class="panel">
			<div class="panel-head">Inspection of <?php echo htmlspecialchars($row['plate_number']); ?></div>
			<div class="panel-body">
				<dl class="details">
					<dt>Vehicle</dt>
					<dd><a href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($row['vehicle_type']); ?> &middot; <?php echo htmlspecialchars($row['operator_name']); ?></span></dd>
					<dt>Scheduled</dt>
					<dd><?php echo date("M d, Y", strtotime($row['schedule_date'])); ?></dd>
					<dt>Inspected</dt>
					<dd><?php if($row['inspection_date'] != null){ echo date("M d, Y", strtotime($row['inspection_date'])); } else { echo '<span class="muted">Not yet</span>'; } ?></dd>
					<dt>Inspector</dt>
					<dd><?php if($row['inspector'] != ""){ echo htmlspecialchars($row['inspector']); } else { echo '<span class="muted">-</span>'; } ?></dd>
					<dt>Result</dt>
					<dd><?php echo htmlspecialchars($row['result']); ?></dd>
					<dt>Compliance</dt>
					<dd><?php $badge_result = $row['result']; $badge_schedule = $row['schedule_date']; include "../badges/compliance.php"; ?></dd>
				</dl>
				<div class="form-buttons" style="margin-top:18px;">
					<a class="btn" href="edit_inspection.php?id=<?php echo (int)$row['id']; ?>"><?php if($row['result'] == "Pending"){ echo "Record Result"; } else { echo "Edit"; } ?></a>
					<a class="btn btn-light" href="manage_inspections.php">Close</a>
				</div>
			</div>
		</div>

	</div>
	<?php } ?>
<?php
	include "../footer.php";
?>
