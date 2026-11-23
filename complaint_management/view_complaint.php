<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Complaints($pdo);
	$log_conn = new DriverLogs($pdo);
	$violation_conn = new Violations($pdo);

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);
	$actions = $conn->fetch_actions($url_id);
	$linked_violations = $violation_conn->fetch_by_complaint($url_id);

	$page_title = "Complaint Investigation";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_complaints.php"><?php echo $icon_back; ?> Back to Complaints</a>

	<?php if(isset($_GET['saved'])){ ?>
	<div class="alert alert-green">Investigation update saved. The complainant can see it in their account.</div>
	<?php } ?>
	<?php if(isset($_GET['error'])){ ?>
	<div class="alert alert-red">Please write the action taken before saving the update.</div>
	<?php } ?>

	<?php if(count($rows) == 0){ ?>
	<div class="alert">Complaint not found.</div>
	<?php } ?>

	<?php
		foreach($rows as $row){
			$on_duty = [];
			if($row['vehicle_id'] != null){
				$on_duty = $log_conn->driver_on($row['vehicle_id'], $row['incident_date']);
			}
			$mediation_set = $row['mediation_officer'] != null && $row['respondent'] != null;
			$mediation_edit = !$mediation_set || isset($_GET['change_mediation']) || isset($_GET['mediation_error']);
			$vehicle_complaints = 0;
			if($row['vehicle_id'] != null){
				$vehicle_complaints = count($conn->fetch_by_vehicle($row['vehicle_id']));
			}
	?>
	<div class="split">

		<div class="stack">
			<div class="panel">
				<div class="panel-head">
					<span><?php echo htmlspecialchars($row['complaint_number']); ?> &middot; <?php echo htmlspecialchars($row['concern']); ?></span>
					<?php $badge_status = $row['status']; include "../badges/complaint_status.php"; ?>
				</div>
				<div class="panel-body">
					<div class="form-section">Complaint Details</div>
					<dl class="details">
						<dt>Type</dt>
						<dd><?php echo htmlspecialchars($row['complaint_type']); ?></dd>
						<dt>Concern</dt>
						<dd><?php echo htmlspecialchars($row['concern']); ?></dd>
						<dt>Incident Date</dt>
						<dd><?php echo date("M d, Y", strtotime($row['incident_date'])); ?></dd>
						<dt>Location</dt>
						<dd><?php if($row['incident_location'] != ""){ echo htmlspecialchars($row['incident_location']); } else { echo '<span class="muted">Not given</span>'; } ?></dd>
						<dt>Filed On</dt>
						<dd><?php echo date("M d, Y h:i A", strtotime($row['date_filed'])); ?></dd>
						<?php if($row['date_closed'] != null){ ?>
						<dt>Closed On</dt>
						<dd><?php echo date("M d, Y", strtotime($row['date_closed'])); ?></dd>
						<?php } ?>
					</dl>
					<div class="form-section section-gap">Description</div>
					<p class="note-text" style="margin:0;"><?php echo htmlspecialchars($row['description']); ?></p>

					<div class="form-section section-gap">Complainant</div>
					<dl class="details">
						<dt>Name</dt>
						<dd><?php echo htmlspecialchars($row['complainant_name']); ?></dd>
						<dt>Contact</dt>
						<dd><?php if($row['contact_number'] != ""){ echo htmlspecialchars($row['contact_number']); } else { echo '<span class="muted">None</span>'; } ?></dd>
						<dt>Filed Through</dt>
						<dd><?php if($row['user_id'] != null){ echo "Passenger account (online)"; } else if($row['account_deleted'] == 1){ echo "Online (account deleted)"; } else { echo "Walk-in / recorded by office"; } ?></dd>
					</dl>

				</div>
			</div>

			<div class="panel" id="mediation">
				<div class="panel-head">
					<span>Mediation</span>
					<?php if(!$mediation_edit){ ?>
					<a class="btn btn-light btn-small" href="view_complaint.php?id=<?php echo (int)$row['id']; ?>&change_mediation=1#mediation">Change</a>
					<?php } ?>
				</div>
				<div class="panel-body">
					<?php if(isset($_GET['mediation_saved'])){ ?>
					<div class="alert alert-green" style="margin:0 0 14px 0;">The mediation was saved.</div>
					<?php } ?>
					<?php if(isset($_GET['mediation_error'])){ ?>
					<div class="alert alert-red" style="margin:0 0 14px 0;">Please type the mediation officer and the respondent. Names can only have letters, spaces, dots, hyphens and apostrophes.</div>
					<?php } ?>
					<?php if($mediation_edit){ ?>
					<form method="post" action="mediate_complaint.php">
						<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
						<div class="form-row">
							<label for="md-officer">Mediation Officer</label>
							<input type="text" name="mediation_officer" data-input="name" id="md-officer" maxlength="100" value="<?php echo htmlspecialchars((string)$row['mediation_officer']); ?>" required>
						</div>
						<div class="form-row">
							<label>Party 1 &middot; Complainant</label>
							<div class="auto-field"><?php echo htmlspecialchars($row['complainant_name']); ?><?php if($row['contact_number'] != ""){ echo " &middot; " . htmlspecialchars($row['contact_number']); } ?></div>
						</div>
						<div class="form-row">
							<label for="md-respondent">Party 2 &middot; Respondent</label>
							<input type="text" name="respondent" data-input="name" id="md-respondent" maxlength="100" value="<?php echo htmlspecialchars((string)$row['respondent']); ?>" required>
						</div>
						<div class="form-buttons">
							<button class="btn" name="mediate">Save Mediation</button>
							<?php if($mediation_set){ ?>
							<a class="btn btn-light" href="view_complaint.php?id=<?php echo (int)$row['id']; ?>#mediation">Cancel</a>
							<?php } ?>
						</div>
					</form>
					<?php } else { ?>
					<div class="mediation-officer">
						<span class="avatar"><?php
							$initials = "";
							foreach(array_slice(explode(" ", $row['mediation_officer']), 0, 2) as $w){
								$initials .= strtoupper(substr($w, 0, 1));
							}
							echo htmlspecialchars($initials);
						?></span>
						<div>
							<small>Mediation Officer</small>
							<b><?php echo htmlspecialchars($row['mediation_officer']); ?></b>
						</div>
					</div>
					<div class="mediation-parties">
						<div class="party">
							<div class="party-label">Party 1 &middot; Complainant</div>
							<span class="badge badge-gray"><?php if($row['user_id'] != null){ echo "Passenger"; } else if($row['account_deleted'] == 1){ echo "Account deleted"; } else { echo "Walk-in"; } ?></span>
							<b><?php echo htmlspecialchars($row['complainant_name']); ?></b>
							<span class="sub"><?php if($row['contact_number'] != ""){ echo htmlspecialchars($row['contact_number']); } ?><?php if($row['user_id'] != null){ echo " &middot; Passenger account"; } ?></span>
						</div>
						<div class="party">
							<div class="party-label">Party 2 &middot; Respondent</div>
							<b><?php echo htmlspecialchars($row['respondent']); ?></b>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<span>Investigation History</span>
					<span class="route-line"><?php echo count($actions); ?> <?php if(count($actions) == 1){ echo "update"; } else { echo "updates"; } ?></span>
				</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Date</th>
						<th>Status</th>
						<th>Action Taken</th>
						<th>Handled By</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($actions) == 0){ ?>
					<tr><td colspan="4" class="empty">No investigation yet.</td></tr>
					<?php } ?>
					<?php foreach($actions as $a){ ?>
					<tr>
						<td class="nowrap"><?php echo date("M d, Y", strtotime($a['action_date'])); ?><span class="sub"><?php echo date("h:i A", strtotime($a['action_date'])); ?></span></td>
						<td><?php $badge_status = $a['status']; include "../badges/complaint_status.php"; ?></td>
						<td class="note-text"><?php echo htmlspecialchars($a['action_taken']); ?></td>
						<td class="muted"><?php echo htmlspecialchars($a['handled_by']); ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<span>Violations from this Complaint</span>
					<a href="../violation_management/add_violation.php?kind=Vehicle&amp;complaint_id=<?php echo (int)$row['id']; ?>" style="font-size:13px; font-weight:600;">+ Record violation</a>
				</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Ticket / TOP No.</th>
						<th>Type / Agency</th>
						<th>Violation</th>
						<th>Status</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($linked_violations) == 0){ ?>
					<tr><td colspan="4" class="empty">No LTO, LTFRB or DOTr violation is linked to this complaint.</td></tr>
					<?php } ?>
					<?php foreach($linked_violations as $x){ ?>
					<tr>
						<td class="nowrap"><a class="id-link" href="../violation_management/view_violation.php?id=<?php echo (int)$x['id']; ?>"><?php echo htmlspecialchars($x['ticket_number']); ?></a><span class="sub"><?php echo date("M d, Y", strtotime($x['violation_date'])); ?></span></td>
						<td><?php $badge_kind = $x['violation_kind']; include "../badges/violation_kind.php"; ?><span class="sub"><?php echo htmlspecialchars($x['agency']); ?></span></td>
						<td class="strong"><?php echo htmlspecialchars($x['violation']); ?></td>
						<td><?php $badge_status = $x['status']; include "../badges/violation_status.php"; ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>

		<div class="stack">
			<div class="panel">
				<div class="panel-head">Investigation</div>
				<div class="panel-body">
					<form method="post" action="investigate_complaint.php">
						<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
						<div class="form-row">
							<label>Set Status To</label>
							<select name="status">
								<?php foreach($complaint_statuses as $s){ ?>
								<option value="<?php echo $s; ?>" <?php if($row['status'] == $s || ($row['status'] == "Pending" && $s == "Under Investigation")){ echo "selected"; } ?>><?php echo $s; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="form-row">
							<label>Action Taken / Findings</label>
							<textarea name="action_taken" rows="4" placeholder="e.g. Called the operator, driver will report to the office" required></textarea>
						</div>
						<button class="btn" name="investigate" style="width:100%;"><?php echo $icon_check; ?> Save Update</button>
					</form>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">Vehicle, Driver and Operator</div>
				<div class="panel-body">
					<dl class="details">
						<dt>Vehicle</dt>
						<dd>
							<?php if($row['vehicle_id'] != null){ ?>
								<a href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($row['vehicle_type']); ?></span>
							<?php } else { ?>
								<span class="muted">Not specified</span>
							<?php } ?>
						</dd>
						<dt>Driver on Duty</dt>
						<dd>
							<?php if(count($on_duty) > 0){ ?>
								<a href="../driver_management/view_driver.php?id=<?php echo (int)$on_duty[0]['driver_id']; ?>"><?php echo htmlspecialchars($on_duty[0]['driver_name']); ?></a><span class="sub">License <?php echo htmlspecialchars($on_duty[0]['license_number']); ?> &middot; <?php echo htmlspecialchars($on_duty[0]['contact_number']); ?></span>
								<span class="sub">From the driver log of <?php echo date("M d, Y", strtotime($row['incident_date'])); ?></span>
							<?php } else if($row['vehicle_id'] != null){ ?>
								<span class="muted">No driver logged on <?php echo date("M d, Y", strtotime($row['incident_date'])); ?></span>
								<span class="sub"><a href="../assignment_management/manage_assignments.php?date=<?php echo htmlspecialchars($row['incident_date']); ?>">Open the log book for that day</a></span>
							<?php } else { ?>
								<span class="muted">Unknown, no vehicle given</span>
							<?php } ?>
						</dd>
						<dt>Operator</dt>
						<dd>
							<?php if($row['operator_id'] != null){ ?>
								<a href="../operator_management/view_operator.php?id=<?php echo (int)$row['operator_id']; ?>"><?php echo htmlspecialchars($row['operator_name']); ?></a><span class="sub"><?php echo htmlspecialchars($row['operator_number']); ?></span>
							<?php } else { ?>
								<span class="muted">Not specified</span>
							<?php } ?>
						</dd>
						<dt>Route</dt>
						<dd>
							<?php if($row['route_id'] != null){ ?>
								<a href="../route_management/view_route.php?id=<?php echo (int)$row['route_id']; ?>"><?php echo htmlspecialchars($row['route_code']); ?></a><span class="sub"><?php echo htmlspecialchars($row['route_name']); ?></span>
							<?php } else { ?>
								<span class="muted">Not specified</span>
							<?php } ?>
						</dd>
					</dl>
					<?php if($vehicle_complaints > 1){ ?>
					<div class="alert" style="margin:16px 0 0 0;">This vehicle has <b><?php echo $vehicle_complaints; ?></b> complaints on record. <a href="manage_complaints.php?search=<?php echo urlencode($row['plate_number']); ?>">See all</a></div>
					<?php } ?>
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
