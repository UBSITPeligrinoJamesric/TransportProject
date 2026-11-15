<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Associations($pdo);

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);

	$error = "";

	if(isset($_POST['submit'])){
		$association_name = trim($_POST['association_name']);
		$contact_person = trim($_POST['contact_person']);
		$contact_number = trim($_POST['contact_number']);

		if($conn->name_exists($association_name, $url_id)){
			$error = "That association name already exists.";
			$rows[0]['association_name'] = $association_name;
			$rows[0]['contact_person'] = $contact_person;
			$rows[0]['contact_number'] = $contact_number;
		}
		else{
			$conn->update_data($association_name, $contact_person, $contact_number, $url_id);

			header("location:manage_associations.php");
			exit();
		}
	}

	$page_title = "Edit Association";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_associations.php"><?php echo $icon_back; ?> Back to Associations</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<?php if(count($rows) == 0){ ?>
	<div class="alert">Association not found.</div>
	<?php } ?>

	<?php foreach($rows as $row){ ?>
	<div class="panel form-page">
		<div class="panel-head">Edit Association</div>
		<div class="panel-body">
			<form method="post">
				<div class="form-row">
					<label>Association Name</label>
					<input type="text" name="association_name" maxlength="150" value="<?php echo htmlspecialchars($row['association_name']); ?>" required>
				</div>
				<div class="form-row">
					<label>Contact Person</label>
					<input type="text" name="contact_person" maxlength="100" value="<?php echo htmlspecialchars($row['contact_person']); ?>" required>
				</div>
				<div class="form-row">
					<label>Contact Number</label>
					<input type="text" name="contact_number" maxlength="30" value="<?php echo htmlspecialchars($row['contact_number']); ?>" required>
				</div>
				<div class="form-buttons">
					<button class="btn" name="submit">Update</button>
					<a class="btn btn-light" href="manage_associations.php">Cancel</a>
				</div>
			</form>
		</div>
	</div>
	<?php } ?>
<?php
	include "../footer.php";
?>
