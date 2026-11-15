<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$search = "";
	$status = "";
	$association = "";
	if(isset($_GET['search'])){
		$search = trim($_GET['search']);
	}
	if(isset($_GET['status'])){
		$status = $_GET['status'];
	}
	if(isset($_GET['association'])){
		$association = $_GET['association'];
	}

	$conn = new Operators($pdo);
	$association_conn = new Associations($pdo);

	$rows = $conn->fetch_data($search, $status, $association);
	$counts = $conn->count_status();
	$associations = $association_conn->fetch_data();

	$form = [
		"registration_number" => $conn->next_registration_number(),
		"date_registered" => $today,
		"full_name" => "",
		"contact_number" => "",
		"email" => "",
		"address" => "",
		"association_id" => "",
		"membership_number" => "",
		"member_since" => "",
	];

	$per_page = 10;
	$page = 1;
	if(isset($_GET['page'])){
		$page = max(1, (int)$_GET['page']);
	}
	$total_rows = count($rows);
	$rows = array_slice($rows, ($page - 1) * $per_page, $per_page);

	$page_title = "Operator Registration";
	$base = "../";
	include "../header.php";
?>
	<div class="tabs">
		<a class="current" href="manage_operators.php">Operators</a>
		<a href="manage_associations.php">Associations</a>
	</div>

	<?php if(isset($_GET['error'])){ ?>
	<div class="alert">This operator cannot be deleted because it still has vehicles, documents or assignment history. Set the status to Inactive instead.</div>
	<?php } ?>

	<div class="cards">
		<div class="card">
			<div>
				<div class="card-label">Registered Operators</div>
				<div class="card-number"><?php echo (int)$counts['total_operators']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_operator; ?></span>
		</div>
		<div class="card card-green">
			<div>
				<div class="card-label">Active</div>
				<div class="card-number"><?php echo (int)$counts['active_operators']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_check; ?></span>
		</div>
		<div class="card">
			<div>
				<div class="card-label">Association Members</div>
				<div class="card-number"><?php echo (int)$counts['member_operators']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_group; ?></span>
		</div>
		<div class="card card-red">
			<div>
				<div class="card-label">Suspended / Inactive</div>
				<div class="card-number"><?php echo (int)$counts['inactive_operators']; ?></div>
			</div>
			<span class="card-icon"><?php echo $icon_alert; ?></span>
		</div>
	</div>

	<div class="split">

		<div class="panel">
			<form method="get" class="toolbar">
				<label class="search-box">
					<?php echo $icon_search; ?>
					<input type="text" name="search" placeholder="Search name, reg. no. or contact..." value="<?php echo htmlspecialchars($search); ?>">
				</label>
				<select name="association" onchange="this.form.submit()">
					<option value="">All Associations</option>
					<?php foreach($associations as $as){ ?>
					<option value="<?php echo (int)$as['id']; ?>" <?php if($association == $as['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($as['association_name']); ?></option>
					<?php } ?>
					<option value="none" <?php if($association == "none"){ echo "selected"; } ?>>Not a member</option>
				</select>
				<select name="status" onchange="this.form.submit()">
					<option value="">All Status</option>
					<?php foreach($operator_statuses as $s){ ?>
					<option value="<?php echo $s; ?>" <?php if($status == $s){ echo "selected"; } ?>><?php echo $s; ?></option>
					<?php } ?>
				</select>
				<?php if($search != "" || $status != "" || $association != ""){ ?>
					<a class="btn btn-light btn-small" href="manage_operators.php">Clear</a>
				<?php } ?>
			</form>

			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Reg. No.</th>
					<th>Operator</th>
					<th>Contact</th>
					<th>Association</th>
					<th>Vehicles</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($rows) == 0){ ?>
				<tr>
					<td colspan="7" class="empty">No operators found.</td>
				</tr>
				<?php } ?>
				<?php
					foreach($rows as $row){
				?>
				<tr>
					<td><a class="id-link" href="view_operator.php?id=<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($row['registration_number']); ?></a></td>
					<td class="strong"><?php echo htmlspecialchars($row['full_name']); ?><span class="sub">Since <?php echo date("M d, Y", strtotime($row['date_registered'])); ?></span></td>
					<td class="nowrap"><?php echo htmlspecialchars($row['contact_number']); ?><?php if($row['email'] != ""){ ?><span class="sub"><?php echo htmlspecialchars($row['email']); ?></span><?php } ?></td>
					<td>
						<?php if($row['association_name'] != null){ ?>
							<?php echo htmlspecialchars($row['association_name']); ?><span class="sub"><?php echo htmlspecialchars($row['membership_number']); ?></span>
						<?php } else { ?>
							<span class="muted">Not a member</span>
						<?php } ?>
					</td>
					<td><?php echo (int)$row['vehicle_count']; ?></td>
					<td>
						<?php if($row['status'] == "Active"){ ?>
							<span class="badge badge-green">Active</span>
						<?php } else if($row['status'] == "Suspended"){ ?>
							<span class="badge badge-red">Suspended</span>
						<?php } else { ?>
							<span class="badge badge-gray"><?php echo htmlspecialchars($row['status']); ?></span>
						<?php } ?>
					</td>
					<td>
						<div class="actions">
							<a class="icon-btn" title="View" href="view_operator.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_view; ?></a>
							<a class="icon-btn" title="Edit" href="edit_operator.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_edit; ?></a>
							<form method="post" action="delete_operator.php" onsubmit="return confirm('Delete this operator?');">
								<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
								<button class="icon-btn danger" title="Delete" name="delete"><?php echo $icon_delete; ?></button>
							</form>
						</div>
					</td>
				</tr>
				<?php
					}
				?>
				</tbody>
			</table>
			</div>

			<?php include "../page_links.php"; ?>
		</div>

		<div class="panel" id="add">
			<div class="panel-head">Register Operator</div>
			<div class="panel-body">
				<form method="post" action="add_operator.php">
					<?php include "operator_form.php"; ?>
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
