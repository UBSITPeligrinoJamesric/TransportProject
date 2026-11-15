<?php
	require_once "dbconn.php";
	require_once "transport_functions.php";
	require_once "options.php";

	$q = "";
	if(isset($_GET['q'])){
		$q = trim($_GET['q']);
	}

	$operator_conn = new Operators($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$route_conn = new Routes($pdo);
	$document_conn = new Documents($pdo);

	$operators = [];
	$vehicles = [];
	$routes = [];
	$documents = [];
	if($q != ""){
		$operators = $operator_conn->fetch_data($q, "", "");
		$vehicles = $vehicle_conn->fetch_data($q, "", "");
		$routes = $route_conn->fetch_data($q, "");
		$documents = $document_conn->fetch_data($q, "", "", "");
	}

	$page_title = "Search Results";
	include "header.php";
?>
	<?php if($q == ""){ ?>
	<div class="alert">Type something in the search box at the top.</div>
	<?php } else { ?>
	<p class="muted" style="margin-top:0;">Results for "<b><?php echo htmlspecialchars($q); ?></b>"</p>

	<div class="stack">
		<div class="panel">
			<div class="panel-head">Operators (<?php echo count($operators); ?>)</div>
			<div class="table-wrap">
			<table>
				<tbody>
				<?php if(count($operators) == 0){ ?>
				<tr><td class="empty">No operators match.</td></tr>
				<?php } ?>
				<?php foreach($operators as $o){ ?>
				<tr>
					<td><a class="id-link" href="operator_management/view_operator.php?id=<?php echo (int)$o['id']; ?>"><?php echo htmlspecialchars($o['registration_number']); ?></a></td>
					<td class="strong"><?php echo htmlspecialchars($o['full_name']); ?></td>
					<td><?php echo htmlspecialchars($o['contact_number']); ?></td>
					<td><?php $badge_status = $o['status']; include "badges/operator_status.php"; ?></td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>

		<div class="panel">
			<div class="panel-head">Vehicles (<?php echo count($vehicles); ?>)</div>
			<div class="table-wrap">
			<table>
				<tbody>
				<?php if(count($vehicles) == 0){ ?>
				<tr><td class="empty">No vehicles match.</td></tr>
				<?php } ?>
				<?php foreach($vehicles as $v){ ?>
				<tr>
					<td><a class="id-link" href="vehicle_management/view_vehicle.php?id=<?php echo (int)$v['id']; ?>"><?php echo htmlspecialchars($v['plate_number']); ?></a></td>
					<td><?php echo htmlspecialchars($v['vehicle_type']); ?></td>
					<td><?php echo htmlspecialchars($v['operator_name']); ?></td>
					<td><?php $badge_status = $v['status']; include "badges/vehicle_status.php"; ?></td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>

		<div class="panel">
			<div class="panel-head">Routes (<?php echo count($routes); ?>)</div>
			<div class="table-wrap">
			<table>
				<tbody>
				<?php if(count($routes) == 0){ ?>
				<tr><td class="empty">No routes match.</td></tr>
				<?php } ?>
				<?php foreach($routes as $r){ ?>
				<tr>
					<td><a class="id-link" href="route_management/view_route.php?id=<?php echo (int)$r['id']; ?>"><?php echo htmlspecialchars($r['route_code']); ?></a></td>
					<td class="strong"><?php echo htmlspecialchars($r['route_name']); ?></td>
					<td><?php echo htmlspecialchars($r['origin']); ?> &rarr; <?php echo htmlspecialchars($r['destination']); ?></td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>

		<div class="panel">
			<div class="panel-head">Permits &amp; Documents (<?php echo count($documents); ?>)</div>
			<div class="table-wrap">
			<table>
				<tbody>
				<?php if(count($documents) == 0){ ?>
				<tr><td class="empty">No documents match.</td></tr>
				<?php } ?>
				<?php foreach($documents as $d){ ?>
				<tr>
					<td><a class="id-link" href="document_management/view_document.php?id=<?php echo (int)$d['id']; ?>"><?php echo htmlspecialchars($d['document_number']); ?></a></td>
					<td><?php echo htmlspecialchars($d['document_type']); ?></td>
					<td><?php if($d['operator_id'] != null){ echo htmlspecialchars($d['operator_name']); } else { echo htmlspecialchars($d['plate_number']); } ?></td>
					<td><?php $badge_date = $d['expiry_date']; include "badges/expiry.php"; ?></td>
				</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>
	</div>
	<?php } ?>
<?php
	include "footer.php";
?>
