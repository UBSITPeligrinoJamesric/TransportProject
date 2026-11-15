<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Associations($pdo);
	$rows = $conn->fetch_data();

	$page_title = "Operator Registration";
	$base = "../";
	include "../header.php";
?>
	<div class="tabs">
		<a href="manage_operators.php">Operators</a>
		<a class="current" href="manage_associations.php">Associations</a>
	</div>

	<?php if(isset($_GET['error']) && $_GET['error'] == "in_use"){ ?>
	<div class="alert">This association cannot be deleted because operators are still members. Change their association first.</div>
	<?php } ?>
	<?php if(isset($_GET['error']) && $_GET['error'] == "duplicate"){ ?>
	<div class="alert alert-red">That association name already exists.</div>
	<?php } ?>

	<div class="split">

		<div class="panel">
			<div class="table-wrap">
			<table>
				<thead>
				<tr>
					<th>Association</th>
					<th>Contact Person</th>
					<th>Contact No.</th>
					<th>Members</th>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody>
				<?php if(count($rows) == 0){ ?>
				<tr><td colspan="5" class="empty">No associations yet.</td></tr>
				<?php } ?>
				<?php foreach($rows as $row){ ?>
				<tr>
					<td class="strong"><?php echo htmlspecialchars($row['association_name']); ?></td>
					<td><?php echo htmlspecialchars($row['contact_person']); ?></td>
					<td class="nowrap"><?php echo htmlspecialchars($row['contact_number']); ?></td>
					<td><a class="id-link" href="manage_operators.php?association=<?php echo (int)$row['id']; ?>"><?php echo (int)$row['member_count']; ?> operators</a></td>
					<td>
						<div class="actions">
							<a class="icon-btn" title="Edit" href="edit_association.php?id=<?php echo (int)$row['id']; ?>"><?php echo $icon_edit; ?></a>
							<form method="post" action="delete_association.php" onsubmit="return confirm('Delete this association?');">
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
		</div>

		<div class="panel">
			<div class="panel-head">Add Association</div>
			<div class="panel-body">
				<form method="post" action="add_association.php">
					<div class="form-row">
						<label>Association Name</label>
						<input type="text" name="association_name" maxlength="150" required>
					</div>
					<div class="form-row">
						<label>Contact Person</label>
						<input type="text" name="contact_person" maxlength="100" required>
					</div>
					<div class="form-row">
						<label>Contact Number</label>
						<input type="text" name="contact_number" maxlength="30" required>
					</div>
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
