<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Complaints($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$route_conn = new Routes($pdo);
	$vehicles = $vehicle_conn->fetch_list();
	$routes = $route_conn->fetch_list();

	$error = "";
	$form = [
		"complainant_name" => "",
		"contact_number" => "",
		"vehicle_id" => "",
		"route_id" => "",
		"concern" => "",
		"incident_date" => $today,
		"incident_location" => "",
		"description" => "",
	];

	if(isset($_POST['submit'])){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		include "complaint_check.php";
		if($error == "" && !preg_match($name_pattern, $form['complainant_name'])){
			$error = "The complainant name can only have letters, spaces, dots, hyphens and apostrophes.";
		}
		if($error == "" && $form['contact_number'] != "" && !preg_match($phone_pattern, $form['contact_number'])){
			$error = $phone_rule;
		}

		$contact_number = $form['contact_number'];
		if($contact_number == ""){
			$contact_number = null;
		}

		if($error == ""){
			$complaint_id = $conn->insert_data($conn->next_complaint_number(), null, $form['complainant_name'], $contact_number, $complaint_type, $form['concern'], $form['description'], $vehicle_id, $route_id, $form['incident_date'], $incident_location, date("Y-m-d H:i:s"));

			header("location:view_complaint.php?id=" . (int)$complaint_id);
			exit();
		}
	}

	$page_title = "Record Complaint";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_complaints.php"><?php echo $icon_back; ?> Back to Complaints</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<div class="panel form-page">
		<div class="panel-head">Record Walk-in Complaint</div>
		<div class="panel-body">
			<form method="post">
				<?php include "complaint_form.php"; ?>
				<div class="form-buttons">
					<button class="btn" name="submit">Save</button>
					<a class="btn btn-light" href="manage_complaints.php">Cancel</a>
				</div>
			</form>
		</div>
	</div>
<?php
	include "../footer.php";
?>
