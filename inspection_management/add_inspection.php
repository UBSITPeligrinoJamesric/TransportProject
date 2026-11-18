<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";

	$conn = new Inspections($pdo);

	if(isset($_POST['submit'])){
		$vehicle_id = $_POST['vehicle_id'];
		$schedule_date = $_POST['schedule_date'];
		$remarks = trim($_POST['remarks']);
		if($remarks == ""){
			$remarks = null;
		}

		$conn->insert_data($vehicle_id, $schedule_date, $remarks);
	}

	header("location:manage_inspections.php");
	exit();
?>
