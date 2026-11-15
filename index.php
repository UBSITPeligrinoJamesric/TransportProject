<?php
	require_once "dbconn.php";
	require_once "transport_functions.php";
	require_once "options.php";

	$conn = new Dashboard($pdo);
	$counts = $conn->fetch_counts();
	$documents = $conn->fetch_attention_documents();
	$inspections = $conn->fetch_due_inspections();

	$page_title = "Dashboard";
	include "header.php";
?>
	<h3>Overview</h3>
	<div class="cards">
		<a class="card" href="operator_management/manage_operators.php" style="color:inherit;">
			<div>
				<div class="card-label">Registered Operators</div>
				<div class="card-number"><?php echo (int)$counts['total_operators']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_operator; ?></span>
		</a>
		<a class="card" href="vehicle_management/manage_vehicles.php" style="color:inherit;">
			<div>
				<div class="card-label">Registered Vehicles</div>
				<div class="card-number"><?php echo (int)$counts['total_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_vehicle; ?></span>
		</a>
		<a class="card" href="route_management/manage_routes.php" style="color:inherit;">
			<div>
				<div class="card-label">Routes</div>
				<div class="card-number"><?php echo (int)$counts['total_routes']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_route; ?></span>
		</a>
		<a class="card card-green" href="assignment_management/manage_assignments.php" style="color:inherit;">
			<div>
				<div class="card-label">Active Assignments</div>
				<div class="card-number"><?php echo (int)$counts['active_assignments']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_assign; ?></span>
		</a>
	</div>

	<h3>Needs Attention</h3>
	<div class="cards">
		<a class="card card-orange" href="document_management/manage_documents.php?expiry=Expiring+Soon" style="color:inherit;">
			<div>
				<div class="card-label">Documents Expiring Soon</div>
				<div class="card-number"><?php echo (int)$counts['expiring_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_clock; ?></span>
		</a>
		<a class="card card-red" href="document_management/manage_documents.php?expiry=Expired" style="color:inherit;">
			<div>
				<div class="card-label">Expired Documents</div>
				<div class="card-number"><?php echo (int)$counts['expired_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</a>
		<a class="card card-orange" href="document_management/manage_documents.php?verification=Pending" style="color:inherit;">
			<div>
				<div class="card-label">Pending Verification</div>
				<div class="card-number"><?php echo (int)$counts['pending_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_shield; ?></span>
		</a>
		<a class="card card-red" href="inspection_management/manage_inspections.php?status=Overdue" style="color:inherit;">
			<div>
				<div class="card-label">Overdue Inspections</div>
				<div class="card-number"><?php echo (int)$counts['overdue_inspections']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_inspection; ?></span>
		</a>
		<a class="card card-red" href="inspection_management/manage_inspections.php?status=Failed" style="color:inherit;">
			<div>
				<div class="card-label">Non-Compliant Vehicles</div>
				<div class="card-number"><?php echo (int)$counts['noncompliant_vehicles']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</a>
	</div>

	<div class="split dash-split">

		<div class="panel">
			<div class="panel-head">
				<span>Permits and Documents to Check</span>
				<a href="document_management/manage_documents.php" style="font-size:13px; font-weight:600;">View all</a>
			</div>
			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Document</th>
					<th>Holder</th>
					<th>Expiration</th>
					<th>Verification</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($documents) == 0){ ?>
				<tr><td colspan="4" class="empty">All documents are valid and verified.</td></tr>
				<?php } ?>
				<?php foreach($documents as $d){ ?>
				<tr>
					<td><a class="id-link" href="document_management/view_document.php?id=<?php echo (int)$d['id']; ?>"><?php echo htmlspecialchars($d['document_number']); ?></a><span class="sub"><?php echo htmlspecialchars($d['document_type']); ?></span></td>
					<td><?php if($d['operator_id'] != null){ echo htmlspecialchars($d['operator_name']); } else { echo htmlspecialchars($d['plate_number']); } ?></td>
					<td class="nowrap"><?php echo date("M d, Y", strtotime($d['expiry_date'])); ?><br><?php $badge_date = $d['expiry_date']; include "badges/expiry.php"; ?></td>
					<td><?php $badge_status = $d['verification_status']; include "badges/verification.php"; ?></td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>

		<div class="panel">
			<div class="panel-head">
				<span>Upcoming Inspections</span>
				<a href="inspection_management/manage_inspections.php" style="font-size:13px; font-weight:600;">View all</a>
			</div>
			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Vehicle</th>
					<th>Schedule</th>
					<th>Status</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($inspections) == 0){ ?>
				<tr><td colspan="3" class="empty">No inspections scheduled.</td></tr>
				<?php } ?>
				<?php foreach($inspections as $i){ ?>
				<tr>
					<td><a class="id-link" href="inspection_management/view_inspection.php?id=<?php echo (int)$i['id']; ?>"><?php echo htmlspecialchars($i['plate_number']); ?></a><span class="sub"><?php echo htmlspecialchars($i['vehicle_type']); ?></span></td>
					<td class="nowrap"><?php echo date("M d, Y", strtotime($i['schedule_date'])); ?></td>
					<td><?php $badge_result = $i['result']; $badge_schedule = $i['schedule_date']; include "badges/compliance.php"; ?></td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>

	</div>
<?php
	include "footer.php";
?>
