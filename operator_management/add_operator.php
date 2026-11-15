<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Operators($pdo);
	$association_conn = new Associations($pdo);
	$associations = $association_conn->fetch_data();

	$error = "";
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

	if(isset($_POST['submit'])){
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

		if($conn->registration_exists($form['registration_number'], 0)){
			$error = "Registration number " . $form['registration_number'] . " is already used.";
		}
		else{
			$conn->insert_data($form['registration_number'], $form['full_name'], $form['contact_number'], $email, $form['address'], $association_id, $membership_number, $member_since, $form['date_registered']);

			header("location:manage_operators.php");
			exit();
		}
	}

	$page_title = "Register Operator";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_operators.php"><?php echo $icon_back; ?> Back to Operators</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<div class="panel form-page">
		<div class="panel-head">Register Operator</div>
		<div class="panel-body">
			<form method="post">
				<?php include "operator_form.php"; ?>
				<div class="form-buttons">
					<button class="btn" name="submit">Save</button>
					<a class="btn btn-light" href="manage_operators.php">Cancel</a>
				</div>
			</form>
		</div>
	</div>
<?php
	include "../footer.php";
?>
