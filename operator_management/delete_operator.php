<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";

	$conn = new Operators($pdo);

	if(isset($_POST['delete'])){
		$id = $_POST['id'];

		$links = $conn->count_links($id);
		if($links['vehicles'] > 0 || $links['assignments'] > 0 || $links['documents'] > 0){
			header("location:manage_operators.php?error=in_use");
			exit();
		}

		$conn->delete_data($id);
	}

	header("location:manage_operators.php");
	exit();
?>
