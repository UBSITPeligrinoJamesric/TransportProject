<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";

	$conn = new Associations($pdo);

	if(isset($_POST['submit'])){
		$association_name = trim($_POST['association_name']);
		$contact_person = trim($_POST['contact_person']);
		$contact_number = trim($_POST['contact_number']);

		if($conn->name_exists($association_name, 0)){
			header("location:manage_associations.php?error=duplicate");
			exit();
		}

		$conn->insert_data($association_name, $contact_person, $contact_number);
	}

	header("location:manage_associations.php");
	exit();
?>
