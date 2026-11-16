<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";

	$conn = new Documents($pdo);

	if(isset($_POST['delete'])){
		$id = $_POST['id'];
		$conn->delete_data($id);
	}

	header("location:manage_documents.php");
	exit();
?>
