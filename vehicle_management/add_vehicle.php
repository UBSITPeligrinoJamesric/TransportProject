<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Vehicles($pdo);
	$operator_conn = new Operators($pdo);
	$document_conn = new Documents($pdo);
	$operators = $operator_conn->fetch_list();

	$error = "";
	$form = [
		"plate_number" => "",
		"vehicle_type" => "Jeepney",
		"make" => "",
		"model" => "",
		"year_model" => date("Y"),
		"color" => "",
		"capacity" => "",
		"engine_number" => "",
		"chassis_number" => "",
		"operator_id" => "",
		"date_registered" => $today,
		"status" => "Active",
		"or_cr_number" => "",
		"or_cr_expiry" => "",
	];

	if(isset($_POST['submit'])){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		$form['plate_number'] = strtoupper($form['plate_number']);

		if($conn->plate_exists($form['plate_number'], 0)){
			$error = "Plate number " . $form['plate_number'] . " is already registered.";
		}
		else if($form['or_cr_number'] != "" && $form['or_cr_expiry'] == ""){
			$error = "Please enter the OR/CR expiry date, or leave the OR/CR number empty.";
		}
		else{
			$vehicle_id = $conn->insert_data($form['plate_number'], $form['vehicle_type'], $form['make'], $form['model'], $form['year_model'], $form['color'], $form['capacity'], $form['engine_number'], $form['chassis_number'], $form['operator_id'], $form['date_registered'], $form['status']);

			if($form['or_cr_number'] != ""){
				$document_conn->insert_data("OR/CR", $form['or_cr_number'], null, $vehicle_id, $form['date_registered'], $form['or_cr_expiry'], null);
			}

			header("location:view_vehicle.php?id=" . (int)$vehicle_id);
			exit();
		}
	}

	$page_title = "Register Vehicle";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_vehicles.php"><?php echo $icon_back; ?> Back to Vehicles</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<div class="panel form-page">
		<div class="panel-head">Register Vehicle</div>
		<div class="panel-body">
			<form method="post">
				<?php include "vehicle_form.php"; ?>
				<div class="form-buttons">
					<button class="btn" name="submit">Save</button>
					<a class="btn btn-light" href="manage_vehicles.php">Cancel</a>
				</div>
			</form>
		</div>
	</div>
<?php
	include "../footer.php";
?>
