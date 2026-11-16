<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Documents($pdo);
	$operator_conn = new Operators($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$operators = $operator_conn->fetch_list();
	$vehicles = $vehicle_conn->fetch_list();

	$error = "";
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
	if(isset($_GET['vehicle_id'])){
		$form['vehicle_id'] = $_GET['vehicle_id'];
	}
	if(isset($_GET['operator_id'])){
		$form['holder_type'] = "Operator";
		$form['document_type'] = "Franchise (CPC)";
		$form['operator_id'] = $_GET['operator_id'];
	}

	if(isset($_POST['submit'])){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		include "save_check.php";

		if($error == ""){
			$conn->insert_data($form['document_type'], $form['document_number'], $operator_id, $vehicle_id, $form['issued_date'], $form['expiry_date'], $remarks);

			header("location:manage_documents.php");
			exit();
		}
	}

	$page_title = "Add Document";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_documents.php"><?php echo $icon_back; ?> Back to Documents</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<div class="panel form-page">
		<div class="panel-head">Add Document</div>
		<div class="panel-body">
			<form method="post">
				<?php include "document_form.php"; ?>
				<div class="form-buttons">
					<button class="btn" name="submit">Save</button>
					<a class="btn btn-light" href="manage_documents.php">Cancel</a>
				</div>
			</form>
		</div>
	</div>
<?php
	include "../footer.php";
?>
