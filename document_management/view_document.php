<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Documents($pdo);

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);
	$renewals = $conn->fetch_renewals($url_id);

	$page_title = "Document Details";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_documents.php"><?php echo $icon_back; ?> Back to Documents</a>

	<?php if(isset($_GET['renewed'])){ ?>
	<div class="alert" style="background:var(--green-soft); border-color:#86efac; color:#166534;">Renewal saved. The document is back to Pending until it is verified again.</div>
	<?php } ?>
	<?php if(isset($_GET['error'])){ ?>
	<div class="alert alert-red">The new expiration date must be later than the current one.</div>
	<?php } ?>

	<?php if(count($rows) == 0){ ?>
	<div class="alert">Document not found.</div>
	<?php } ?>

	<?php
		foreach($rows as $row){
			$suggested_expiry = date("Y-m-d", strtotime($row['expiry_date'] . " +1 year"));
	?>
	<div class="split">

		<div class="stack">
			<div class="panel">
				<div class="panel-head">
					<span><?php echo htmlspecialchars($row['document_type']); ?></span>
					<?php $badge_date = $row['expiry_date']; include "../badges/expiry.php"; ?>
				</div>
				<div class="panel-body">
					<div class="form-section">Permit Information</div>
					<dl class="details">
						<dt>Document No.</dt>
						<dd><?php echo htmlspecialchars($row['document_number']); ?></dd>
						<dt>Belongs To</dt>
						<dd>
							<?php if($row['operator_id'] != null){ ?>
								<a href="../operator_management/view_operator.php?id=<?php echo (int)$row['operator_id']; ?>"><?php echo htmlspecialchars($row['operator_name']); ?></a><span class="sub">Operator &middot; <?php echo htmlspecialchars($row['registration_number']); ?></span>
							<?php } else { ?>
								<a href="../vehicle_management/view_vehicle.php?id=<?php echo (int)$row['vehicle_id']; ?>"><?php echo htmlspecialchars($row['plate_number']); ?></a><span class="sub">Vehicle &middot; <?php echo htmlspecialchars($row['vehicle_type']); ?></span>
							<?php } ?>
						</dd>
						<dt>Issued</dt>
						<dd><?php echo date("M d, Y", strtotime($row['issued_date'])); ?></dd>
						<dt>Expires</dt>
						<dd><?php echo date("M d, Y", strtotime($row['expiry_date'])); ?></dd>
						<dt>Verification</dt>
						<dd><?php $badge_status = $row['verification_status']; include "../badges/verification.php"; ?><?php if($row['verified_date'] != null){ ?><span class="sub">on <?php echo date("M d, Y", strtotime($row['verified_date'])); ?></span><?php } ?></dd>
						<dt>Remarks</dt>
						<dd><?php if($row['remarks'] != ""){ echo htmlspecialchars($row['remarks']); } else { echo '<span class="muted">None</span>'; } ?></dd>
					</dl>
					<div class="form-buttons form-buttons-single" style="margin-top:18px;">
						<a class="btn" href="edit_document.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
					</div>
				</div>
			</div>

			<div class="panel">
				<div class="panel-head">
					<span>Renewal History</span>
					<span class="route-line"><?php echo count($renewals); ?> <?php if(count($renewals) == 1){ echo "renewal"; } else { echo "renewals"; } ?></span>
				</div>
				<div class="table-wrap">
				<table>
					<thead>
					<tr>
						<th>Renewed On</th>
						<th>Previous Expiry</th>
						<th>New Expiry</th>
						<th>Remarks</th>
					</tr>
					</thead>
					<tbody>
					<?php if(count($renewals) == 0){ ?>
					<tr><td colspan="4" class="empty">No renewals recorded yet.</td></tr>
					<?php } ?>
					<?php foreach($renewals as $r){ ?>
					<tr>
						<td class="nowrap strong"><?php echo date("M d, Y", strtotime($r['renewal_date'])); ?></td>
						<td class="nowrap muted"><?php echo date("M d, Y", strtotime($r['previous_expiry'])); ?></td>
						<td class="nowrap"><?php echo date("M d, Y", strtotime($r['new_expiry'])); ?></td>
						<td class="muted"><?php echo htmlspecialchars((string)$r['remarks']); ?></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
				</div>
			</div>
		</div>

		<div class="stack">
			<div class="panel">
				<div class="panel-head">Document Verification</div>
				<div class="panel-body">
					<form method="post" action="verify_document.php">
						<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
						<div class="form-row">
							<label>Verification Status</label>
							<select name="verification_status">
								<?php foreach($verification_statuses as $vs){ ?>
								<option value="<?php echo $vs; ?>" <?php if($row['verification_status'] == $vs){ echo "selected"; } ?>><?php echo $vs; ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="form-row">
							<label>Remarks</label>
							<input type="text" name="remarks" maxlength="255" value="<?php echo htmlspecialchars((string)$row['remarks']); ?>" placeholder="e.g. Checked against original copy">
						</div>
						<button class="btn" name="verify" style="width:100%;"><?php echo $icon_shield; ?> Save Verification</button>
					</form>
				</div>
			</div>

			<div class="panel" id="renew">
				<div class="panel-head">Renew Document</div>
				<div class="panel-body">
					<form method="post" action="renew_document.php">
						<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
						<div class="form-row">
							<label>Renewal Date</label>
							<input type="date" name="renewal_date" value="<?php echo $today; ?>" required>
						</div>
						<div class="form-row">
							<label>New Expiration Date</label>
							<input type="date" name="new_expiry" value="<?php echo $suggested_expiry; ?>" min="<?php echo date("Y-m-d", strtotime($row['expiry_date'] . " +1 day")); ?>" required>
							<span class="small-note">Current expiry: <?php echo date("M d, Y", strtotime($row['expiry_date'])); ?></span>
						</div>
						<div class="form-row">
							<label>Remarks (optional)</label>
							<input type="text" name="remarks" maxlength="255" placeholder="e.g. Yearly LTO renewal">
						</div>
						<button class="btn" name="renew" style="width:100%;"><?php echo $icon_renew; ?> Record Renewal</button>
					</form>
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
