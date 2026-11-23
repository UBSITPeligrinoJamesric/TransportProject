<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Complaints($pdo);

	if(isset($_POST['mediate'])){
		$id = (int)$_POST['id'];
		$rows = $conn->show_update_data($id);
		if(count($rows) == 0){
			header("location:manage_complaints.php");
			exit();
		}

		$mediation_officer = trim($_POST['mediation_officer']);
		$respondent = trim($_POST['respondent']);

		if($mediation_officer == "" || $respondent == "" || strlen($mediation_officer) > 100 || strlen($respondent) > 100 || !preg_match($name_pattern, $mediation_officer) || !preg_match($name_pattern, $respondent)){
			header("location:view_complaint.php?id=" . $id . "&mediation_error=1#mediation");
			exit();
		}

		$conn->set_mediation($mediation_officer, $respondent, $id);
		header("location:view_complaint.php?id=" . $id . "&mediation_saved=1#mediation");
		exit();
	}

	header("location:manage_complaints.php");
	exit();
?>
