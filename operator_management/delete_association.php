<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";

	$conn = new Associations($pdo);

	if(isset($_POST['delete'])){
		$id = $_POST['id'];

		$result = $conn->count_members($id);
		if($result['total'] > 0){
			header("location:manage_associations.php?error=in_use");
			exit();
		}

		$conn->delete_data($id);
	}

	header("location:manage_associations.php");
	exit();
?>
