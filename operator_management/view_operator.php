<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Operators($pdo);
	$assignment_conn = new Assignments($pdo);

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);
	$vehicles = $conn->fetch_vehicles($url_id);
	$documents = $conn->fetch_documents($url_id);
	$history = $assignment_conn->fetch_history("", "", $url_id);

	$page_title = "Operator Details";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_operators.php"><?php echo $icon_back; ?> Back to Operators</a>

	<?php if(count($rows) == 0){ ?>
	<div class="alert">Operator not found.</div>
	<?php } ?>

	<?php
		foreach($rows as $row){
	?>
	<div class="split">

		<div class="stack">
			<div class="panel">
				<div class="panel-head">
					<span>Vehicles Owned</span>
					<span class="route-line"><?php echo count($vehicles); ?> total</span>
				</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Plate No.</th>
						<th>Type</th>
						<th>Capacity</th>
						<th>Current Route</th>
						<th>Status</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($vehicles) == 0){ ?>
					<tr><td colspan="5" class="empty">No vehicles registered under this operator.</td></tr>
					<?php } ?>
					<?php foreach($vehicles as $v){ ?>
					<tr>
						<td><a class="id-link" href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$v['id']; ?>"><?php echo htmlspecialchars($v['plate_number']); ?></a></td>
						<td class="strong"><?php echo htmlspecialchars($v['vehicle_type']); ?></td>
						<td><?php echo (int)$v['capacity']; ?> seats</td>
						<td>
							<?php if($v['route_code'] != null){ ?>
								<?php echo htmlspecialchars($v['route_code']); ?><span class="sub"><?php echo htmlspecialchars($v['route_name']); ?></span>
							<?php } else { ?>
								<span class="muted">Not assigned</span>
							<?php } ?>
						</td>
						<td><?php $badge_status = $v['status']; include "../badges/vehicle_status.php"; ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<span>Permits &amp; Documents</span>
					<a href="../document_management/manage_documents.php" style="font-size:13px; font-weight:600;">Manage documents</a>
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
				<div class="panel-head">Assignment History</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Vehicle</th>
						<th>Route</th>
						<th>From</th>
						<th>To</th>
						<th>Remarks</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($history) == 0){ ?>
					<tr><td colspan="5" class="empty">No assignments yet.</td></tr>
					<?php } ?>
					<?php foreach($history as $h){ ?>
					<tr>
						<td class="strong"><?php echo htmlspecialchars($h['plate_number']); ?></td>
						<td><?php echo htmlspecialchars($h['route_code']); ?><span class="sub"><?php echo htmlspecialchars($h['route_name']); ?></span></td>
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
			<div class="panel-head"><?php echo htmlspecialchars($row['full_name']); ?></div>
			<div class="panel-body">
				<div class="form-section">Registration Record</div>
				<dl class="details">
					<dt>Reg. No.</dt>
					<dd><?php echo htmlspecialchars($row['registration_number']); ?></dd>
					<dt>Registered</dt>
					<dd><?php echo date("M d, Y", strtotime($row['date_registered'])); ?></dd>
					<dt>Status</dt>
					<dd><?php $badge_status = $row['status']; include "../badges/operator_status.php"; ?></dd>
				</dl>

				<div class="form-section section-gap">Contact Information</div>
				<dl class="details">
					<dt>Contact No.</dt>
					<dd><?php echo htmlspecialchars($row['contact_number']); ?></dd>
					<dt>Email</dt>
					<dd><?php if($row['email'] != ""){ echo htmlspecialchars($row['email']); } else { echo '<span class="muted">None</span>'; } ?></dd>
					<dt>Address</dt>
					<dd><?php echo htmlspecialchars($row['address']); ?></dd>
				</dl>

				<div class="form-section section-gap">Association Membership</div>
				<?php if($row['association_name'] != null){ ?>
				<dl class="details">
					<dt>Association</dt>
					<dd><?php echo htmlspecialchars($row['association_name']); ?></dd>
					<dt>Membership No.</dt>
					<dd><?php echo htmlspecialchars((string)$row['membership_number']); ?></dd>
					<dt>Member Since</dt>
					<dd><?php if($row['member_since'] != null){ echo date("M d, Y", strtotime($row['member_since'])); } ?></dd>
				</dl>
				<?php } else { ?>
				<p class="muted" style="margin:0;">Not a member of any association.</p>
				<?php } ?>

				<div class="form-buttons" style="margin-top:20px;">
					<a class="btn" href="edit_operator.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
					<a class="btn btn-light" href="manage_operators.php">Close</a>
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
