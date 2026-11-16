<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Assignments($pdo);

	if(isset($_POST['end'])){
		$id = $_POST['id'];
		$conn->end_assignment($id, $today, "Ended manually");
	}

	header("location:manage_assignments.php");
	exit();
?>
