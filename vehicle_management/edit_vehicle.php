<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Vehicles($pdo);
	$operator_conn = new Operators($pdo);
	$assignment_conn = new Assignments($pdo);
	$operators = $operator_conn->fetch_list();

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);

	$error = "";
	$form = null;
	if(count($rows) > 0){
		$form = [];
		$keys = ["plate_number", "vehicle_type", "make", "model", "year_model", "color", "capacity", "engine_number", "chassis_number", "operator_id", "date_registered", "status"];
		foreach($keys as $key){
			$form[$key] = (string)$rows[0][$key];
		}
	}

	if(isset($_POST['submit']) && $form != null){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		$form['plate_number'] = strtoupper($form['plate_number']);

		if($conn->plate_exists($form['plate_number'], $url_id)){
			$error = "Plate number " . $form['plate_number'] . " is already registered to another vehicle.";
		}
		else{
			$conn->update_data($form['plate_number'], $form['vehicle_type'], $form['make'], $form['model'], $form['year_model'], $form['color'], $form['capacity'], $form['engine_number'], $form['chassis_number'], $form['operator_id'], $form['date_registered'], $form['status'], $url_id);
			$assignment_conn->sync_operator($url_id, $today);

			header("location:view_vehicle.php?id=" . (int)$url_id);
			exit();
		}
	}

	$page_title = "Edit Vehicle";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="view_vehicle.php?id=<?php echo (int)$url_id; ?>"><?php echo $icon_back; ?> Back to Vehicle</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<?php if($form == null){ ?>
	<div class="alert">Vehicle not found.</div>
	<?php } else { ?>
	<div class="panel form-page">
		<div class="panel-head">Edit Vehicle</div>
		<div class="panel-body">
			<form method="post">
				<?php include "vehicle_form.php"; ?>
				<span class="small-note" style="margin:-6px 0 12px 0;">Changing the operator is saved in the assignment history.</span>
				<div class="form-buttons">
					<button class="btn" name="submit">Update</button>
					<a class="btn btn-light" href="view_vehicle.php?id=<?php echo (int)$url_id; ?>">Cancel</a>
				</div>
			</form>
		</div>
	</div>
	<?php } ?>
<?php
	include "../footer.php";
?>
