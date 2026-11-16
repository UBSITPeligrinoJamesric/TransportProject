<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Documents($pdo);

	if(isset($_POST['verify'])){
		$id = $_POST['id'];
		$verification_status = $_POST['verification_status'];
		$remarks = trim($_POST['remarks']);
		if($remarks == ""){
			$remarks = null;
		}
		if(in_array($verification_status, $verification_statuses)){
			$conn->set_verification($verification_status, $remarks, $id);
		}
		header("location:view_document.php?id=" . (int)$id);
		exit();
	}

	header("location:manage_documents.php");
	exit();
?>
