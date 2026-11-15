<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Vehicles($pdo);
	$assignment_conn = new Assignments($pdo);

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);
	$documents = $conn->fetch_documents($url_id);
	$inspections = $conn->fetch_inspections($url_id);
	$history = $assignment_conn->fetch_history($url_id, "", "");
	$latest_result = $conn->latest_result($url_id);

	$page_title = "Vehicle Details";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_vehicles.php"><?php echo $icon_back; ?> Back to Vehicles</a>

	<?php if(count($rows) == 0){ ?>
	<div class="alert">Vehicle not found.</div>
	<?php } ?>

	<?php
		foreach($rows as $row){
	?>
	<div class="split">

		<div class="stack">
			<div class="panel">
				<div class="panel-head">
					<span>Registration Documents</span>
					<a href="../document_management/add_document.php?vehicle_id=<?php echo (int)$row['id']; ?>" style="font-size:13px; font-weight:600;">+ Add document</a>
				</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Document</th>
						<th>Number</th>
						<th>Expiry</th>
						<th>Verification</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($documents) == 0){ ?>
					<tr><td colspan="4" class="empty">No documents on file.</td></tr>
					<?php } ?>
					<?php foreach($documents as $d){ ?>
					<tr>
						<td class="strong"><?php echo htmlspecialchars($d['document_type']); ?></td>
						<td><a class="id-link" href="../document_management/view_document.php?id=<?php echo (int)$d['id']; ?>"><?php echo htmlspecialchars($d['document_number']); ?></a></td>
						<td class="nowrap"><?php echo date("M d, Y", strtotime($d['expiry_date'])); ?><br><?php $badge_date = $d['expiry_date']; include "../badges/expiry.php"; ?></td>
						<td><?php $badge_status = $d['verification_status']; include "../badges/verification.php"; ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<span>Inspection Records</span>
					<a href="../inspection_management/manage_inspections.php?vehicle_id=<?php echo (int)$row['id']; ?>#add" style="font-size:13px; font-weight:600;">+ Schedule inspection</a>
				</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Schedule</th>
						<th>Inspected</th>
						<th>Result</th>
						<th>Defects</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($inspections) == 0){ ?>
					<tr><td colspan="4" class="empty">No inspections yet.</td></tr>
					<?php } ?>
					<?php foreach($inspections as $i){ ?>
					<tr>
						<td class="nowrap"><a class="id-link" href="../inspection_management/view_inspection.php?id=<?php echo (int)$i['id']; ?>"><?php echo date("M d, Y", strtotime($i['schedule_date'])); ?></a></td>
						<td class="nowrap"><?php if($i['inspection_date'] != null){ echo date("M d, Y", strtotime($i['inspection_date'])); } else { echo '<span class="muted">Not yet</span>'; } ?></td>
						<td><?php $badge_result = $i['result']; $badge_schedule = $i['schedule_date']; include "../badges/compliance.php"; ?></td>
						<td class="muted"><?php echo htmlspecialchars((string)$i['defects']); ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">Assignment History</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Route</th>
						<th>Operator</th>
						<th>From</th>
						<th>To</th>
						<th>Remarks</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($history) == 0){ ?>
					<tr><td colspan="5" class="empty">Never assigned to a route.</td></tr>
					<?php } ?>
					<?php foreach($history as $h){ ?>
					<tr>
						<td><?php echo htmlspecialchars($h['route_code']); ?><span class="sub"><?php echo htmlspecialchars($h['route_name']); ?></span></td>
						<td><?php echo htmlspecialchars($h['operator_name']); ?></td>
						<td class="nowrap"><?php echo date("M d, Y", strtotime($h['date_assigned'])); ?></td>
						<td class="nowrap">
							<?php if($h['date_ended'] == null){ ?>
								<span class="badge badge-green">Current</span>
							<?php } else { ?>
								<?php echo date("M d, Y", strtotime($h['date_ended'])); ?>
							<?php } ?>
						</td>
						<td class="muted"><?php echo htmlspecialchars((string)$h['remarks']); ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>

		<div class="panel">
			<div class="panel-head"><?php echo htmlspecialchars($row['plate_number']); ?></div>
			<div class="panel-body">
				<div class="form-section">Vehicle Details</div>
				<dl class="details">
					<dt>Type</dt>
					<dd><?php echo htmlspecialchars($row['vehicle_type']); ?></dd>
					<dt>Make / Model</dt>
					<dd><?php echo htmlspecialchars($row['make'] . " " . $row['model']); ?></dd>
					<dt>Year / Color</dt>
					<dd><?php echo (int)$row['year_model']; ?> &middot; <?php echo htmlspecialchars($row['color']); ?></dd>
					<dt>Capacity</dt>
					<dd><?php echo (int)$row['capacity']; ?> seats</dd>
					<dt>Engine No.</dt>
					<dd><?php echo htmlspecialchars($row['engine_number']); ?></dd>
					<dt>Chassis No.</dt>
					<dd><?php echo htmlspecialchars($row['chassis_number']); ?></dd>
					<dt>Status</dt>
					<dd><?php $badge_status = $row['status']; include "../badges/vehicle_status.php"; ?></dd>
					<dt>Compliance</dt>
					<dd>
						<?php if($latest_result == "Passed"){ ?>
							<span class="badge badge-green">Compliant</span>
						<?php } else if($latest_result == "Failed"){ ?>
							<span class="badge badge-red">Non-Compliant</span>
						<?php } else { ?>
							<span class="badge badge-gray">Not inspected</span>
						<?php } ?>
					</dd>
				</dl>

				<div class="form-section section-gap">Operator and Route</div>
				<dl class="details">
					<dt>Operator</dt>
					<dd><a href="../operator_management/view_operator.php?id=<?php echo (int)$row['operator_id']; ?>"><?php echo htmlspecialchars($row['operator_name']); ?></a><span class="sub"><?php echo htmlspecialchars($row['operator_number']); ?></span></dd>
					<dt>Registered</dt>
					<dd><?php echo date("M d, Y", strtotime($row['date_registered'])); ?></dd>
					<dt>Route</dt>
					<dd>
						<?php if($row['route_code'] != null){ ?>
							<a href="../route_management/view_route.php?id=<?php echo (int)$row['route_id']; ?>"><?php echo htmlspecialchars($row['route_code']); ?></a><span class="sub"><?php echo htmlspecialchars($row['route_name']); ?></span>
						<?php } else { ?>
							<span class="muted">Not assigned</span>
						<?php } ?>
					</dd>
				</dl>

				<div class="form-buttons" style="margin-top:20px;">
					<a class="btn" href="edit_vehicle.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
					<a class="btn btn-light" href="../assignment_management/manage_assignments.php?vehicle_id=<?php echo (int)$row['id']; ?>#assign">Assign Route</a>
				</div>
			</div>
		</div>

	</div>
	<?php
		}
	?>
<?php
	include "../footer.php";
?>
