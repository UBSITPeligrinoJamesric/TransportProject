<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$search = "";
	$holder = "";
	$expiry = "";
	$verification = "";
	if(isset($_GET['search'])){
		$search = trim($_GET['search']);
	}
	if(isset($_GET['holder'])){
		$holder = $_GET['holder'];
	}
	if(isset($_GET['expiry'])){
		$expiry = $_GET['expiry'];
	}
	if(isset($_GET['verification'])){
		$verification = $_GET['verification'];
	}

	$conn = new Documents($pdo);
	$operator_conn = new Operators($pdo);
	$vehicle_conn = new Vehicles($pdo);

	$rows = $conn->fetch_data($search, $holder, $expiry, $verification);
	$counts = $conn->count_status();
	$operators = $operator_conn->fetch_list();
	$vehicles = $vehicle_conn->fetch_list();

	$form = [
		"document_type" => "OR/CR",
		"document_number" => "",
		"holder_type" => "Vehicle",
		"operator_id" => "",
		"vehicle_id" => "",
		"issued_date" => $today,
		"expiry_date" => "",
		"remarks" => "",
	];

	$per_page = 10;
	$page = 1;
	if(isset($_GET['page'])){
		$page = max(1, (int)$_GET['page']);
	}
	$total_rows = count($rows);
	$rows = array_slice($rows, ($page - 1) * $per_page, $per_page);

	$page_title = "Permit and Document Monitoring";
	$base = "../";
	include "../header.php";
?>
	<div class="cards">
		<div class="card">
			<div>
				<div class="card-label">Documents on File</div>
				<div class="card-number"><?php echo (int)$counts['total_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_document; ?></span>
		</div>
		<a class="card card-orange" href="manage_documents.php?expiry=Expiring+Soon" style="color:inherit;">
			<div>
				<div class="card-label">Expiring in 30 Days</div>
				<div class="card-number"><?php echo (int)$counts['expiring_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_clock; ?></span>
		</a>
		<a class="card card-red" href="manage_documents.php?expiry=Expired" style="color:inherit;">
			<div>
				<div class="card-label">Expired</div>
				<div class="card-number"><?php echo (int)$counts['expired_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</a>
		<a class="card" href="manage_documents.php?verification=Pending" style="color:inherit;">
			<div>
				<div class="card-label">Pending Verification</div>
				<div class="card-number"><?php echo (int)$counts['pending_documents']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_shield; ?></span>
		</a>
	</div>

	<div class="split">

		<div class="panel">
			<form method="get" class="toolbar">
				<label class="search-box">
					<?php echo $icon_search; ?>
					<input type="text" name="search" placeholder="Search number, type, holder..." value="<?php echo htmlspecialchars($search); ?>">
				</label>
				<select name="holder" onchange="this.form.submit()">
					<option value="">All Holders</option>
					<option value="Operator" <?php if($holder == "Operator"){ echo "selected"; } ?>>Operators</option>
					<option value="Vehicle" <?php if($holder == "Vehicle"){ echo "selected"; } ?>>Vehicles</option>
				</select>
				<select name="expiry" onchange="this.form.submit()">
					<option value="">Any Expiry</option>
					<option value="Valid" <?php if($expiry == "Valid"){ echo "selected"; } ?>>Valid</option>
					<option value="Expiring Soon" <?php if($expiry == "Expiring Soon"){ echo "selected"; } ?>>Expiring Soon</option>
					<option value="Expired" <?php if($expiry == "Expired"){ echo "selected"; } ?>>Expired</option>
				</select>
				<select name="verification" onchange="this.form.submit()">
					<option value="">Any Verification</option>
					<?php foreach($verification_statuses as $vs){ ?>
					<option value="<?php echo $vs; ?>" <?php if($verification == $vs){ echo "selected"; } ?>><?php echo $vs; ?></option>
					<?php } ?>
				</select>
				<?php if($search != "" || $holder != "" || $expiry != "" || $verification != ""){ ?>
					<a class="btn btn-light btn-small" href="manage_documents.php">Clear</a>
				<?php } ?>
			</form>

			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Document</th>
					<th>Holder</th>
					<th>Expiration</th>
					<th>Verification</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($rows) == 0){ ?>
				<tr><td colspan="5" class="empty">No documents found.</td></tr>
				<?php } ?>
				<?php foreach($rows as $row){ ?>
				<tr>
					<td><a class="id-link" href="view_document.php?id=<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['document_number']); ?></a><span class="sub"><?php echo htmlspecialchars($row['document_type']); ?></span></td>
					<td>
						<?php if($row['operator_id'] != null){ ?>
							<?php echo htmlspecialchars($row['operator_name']); ?><span class="sub">Operator</span>
						<?php } else { ?>
							<?php echo htmlspecialchars($row['plate_number']); ?><span class="sub">Vehicle</span>
						<?php } ?>
					</td>
					<td class="nowrap"><?php echo date("M d, Y", strtotime($row['expiry_date'])); ?><br><?php $badge_date = $row['expiry_date']; include "../badges/expiry.php"; ?></td>
					<td><?php $badge_status = $row['verification_status']; include "../badges/verification.php"; ?></td>
					<td>
						<div class="actions">
							<a class="btn btn-light btn-small" href="view_document.php?id=<?php echo (int)$row['id']; ?>">View</a>
							<a class="btn btn-light btn-small" href="edit_document.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
							<form method="post" action="delete_document.php" onsubmit="return confirm('Delete this document and its renewal history?');">
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
			<div class="panel-head">Add Document</div>
			<div class="panel-body">
				<form method="post" action="add_document.php">
					<?php include "document_form.php"; ?>
					<span class="small-note" style="margin:-4px 0 12px 0;">New documents start as Pending until verified.</span>
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
