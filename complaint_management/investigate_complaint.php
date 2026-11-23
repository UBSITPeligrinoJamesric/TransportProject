<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Complaints($pdo);

	if(isset($_POST['investigate'])){
		$id = $_POST['id'];
		$status = $_POST['status'];
		$action_taken = trim($_POST['action_taken']);

		if(!in_array($status, $complaint_statuses) || $action_taken == ""){
			header("location:view_complaint.php?id=" . (int)$id . "&error=1");
			exit();
		}

		$conn->add_action($id, $status, $action_taken, $_SESSION['full_name'], date("Y-m-d H:i:s"));
		header("location:view_complaint.php?id=" . (int)$id . "&saved=1");
		exit();
	}

	header("location:manage_complaints.php");
	exit();
?>
