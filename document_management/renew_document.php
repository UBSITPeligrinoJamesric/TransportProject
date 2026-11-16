<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Documents($pdo);

	if(isset($_POST['renew'])){
		$id = $_POST['id'];
		$renewal_date = $_POST['renewal_date'];
		$new_expiry = $_POST['new_expiry'];
		$remarks = trim($_POST['remarks']);
		if($remarks == ""){
			$remarks = null;
		}

		$rows = $conn->show_update_data($id);
		if(count($rows) > 0 && $new_expiry > $rows[0]['expiry_date']){
			$conn->renew($id, $renewal_date, $new_expiry, $remarks);
			header("location:view_document.php?id=" . (int)$id . "&renewed=1");
			exit();
		}
		header("location:view_document.php?id=" . (int)$id . "&error=expiry");
		exit();
	}

	header("location:manage_documents.php");
	exit();
?>
