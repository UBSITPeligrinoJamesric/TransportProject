<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Documents($pdo);
	$operator_conn = new Operators($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$operators = $operator_conn->fetch_list();
	$vehicles = $vehicle_conn->fetch_list();

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);

	$error = "";
	$form = null;
	if(count($rows) > 0){
		$holder_type = "Vehicle";
		if($rows[0]['operator_id'] != null){
			$holder_type = "Operator";
		}
		$form = [
			"document_type" => $rows[0]['document_type'],
			"document_number" => $rows[0]['document_number'],
			"holder_type" => $holder_type,
			"operator_id" => (string)$rows[0]['operator_id'],
			"vehicle_id" => (string)$rows[0]['vehicle_id'],
			"issued_date" => $rows[0]['issued_date'],
			"expiry_date" => $rows[0]['expiry_date'],
			"remarks" => (string)$rows[0]['remarks'],
		];
	}

	if(isset($_POST['submit']) && $form != null){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		include "save_check.php";

		if($error == ""){
			$conn->update_data($form['document_type'], $form['document_number'], $operator_id, $vehicle_id, $form['issued_date'], $form['expiry_date'], $remarks, $url_id);

			header("location:view_document.php?id=" . (int)$url_id);
			exit();
		}
	}

	$page_title = "Edit Document";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="view_document.php?id=<?php echo (int)$url_id; ?>"><?php echo $icon_back; ?> Back to Document</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<?php if($form == null){ ?>
	<div class="alert">Document not found.</div>
	<?php } else { ?>
	<div class="panel form-page">
		<div class="panel-head">Edit Document</div>
		<div class="panel-body">
			<form method="post">
				<?php include "document_form.php"; ?>
				<span class="small-note" style="margin:-4px 0 12px 0;">To record a renewal, use Renew on the document page so it is saved in the renewal history.</span>
				<div class="form-buttons">
					<button class="btn" name="submit">Update</button>
					<a class="btn btn-light" href="view_document.php?id=<?php echo (int)$url_id; ?>">Cancel</a>
				</div>
			</form>
		</div>
	</div>
	<?php } ?>
<?php
	include "../footer.php";
?>
