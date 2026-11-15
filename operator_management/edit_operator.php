<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Operators($pdo);
	$association_conn = new Associations($pdo);
	$associations = $association_conn->fetch_data();

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);

	$error = "";
	$form = null;
	if(count($rows) > 0){
		$form = [
			"registration_number" => $rows[0]['registration_number'],
			"date_registered" => $rows[0]['date_registered'],
			"full_name" => $rows[0]['full_name'],
			"contact_number" => $rows[0]['contact_number'],
			"email" => (string)$rows[0]['email'],
			"address" => $rows[0]['address'],
			"association_id" => (string)$rows[0]['association_id'],
			"membership_number" => (string)$rows[0]['membership_number'],
			"member_since" => (string)$rows[0]['member_since'],
			"status" => $rows[0]['status'],
		];
	}

	if(isset($_POST['submit']) && $form != null){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		$form['registration_number'] = strtoupper($form['registration_number']);

		$email = $form['email'];
		$association_id = $form['association_id'];
		$membership_number = $form['membership_number'];
		$member_since = $form['member_since'];
		if($email == ""){
			$email = null;
		}
		if($association_id == ""){
			$association_id = null;
			$membership_number = null;
			$member_since = null;
		}
		if($member_since == ""){
			$member_since = null;
		}

		if($conn->registration_exists($form['registration_number'], $url_id)){
			$error = "Registration number " . $form['registration_number'] . " is already used by another operator.";
		}
		else{
			$conn->update_data($form['registration_number'], $form['full_name'], $form['contact_number'], $email, $form['address'], $association_id, $membership_number, $member_since, $form['date_registered'], $form['status'], $url_id);

			header("location:view_operator.php?id=" . (int)$url_id);
			exit();
		}
	}

	$page_title = "Edit Operator";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_operators.php"><?php echo $icon_back; ?> Back to Operators</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<?php if($form == null){ ?>
	<div class="alert">Operator not found.</div>
	<?php } else { ?>
	<div class="panel form-page">
		<div class="panel-head">Edit Operator</div>
		<div class="panel-body">
			<form method="post">
				<?php include "operator_form.php"; ?>
				<div class="form-buttons">
					<button class="btn" name="submit">Update</button>
					<a class="btn btn-light" href="view_operator.php?id=<?php echo (int)$url_id; ?>">Cancel</a>
				</div>
			</form>
		</div>
	</div>
	<?php } ?>
<?php
	include "../footer.php";
?>
