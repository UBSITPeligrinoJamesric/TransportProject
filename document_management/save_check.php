<?php
	$error = "";
	$operator_id = null;
	$vehicle_id = null;
	if($form['holder_type'] == "Operator"){
		$operator_id = $form['operator_id'];
		if($operator_id == ""){
			$error = "Please choose the operator this document belongs to.";
		}
	}
	else{
		$vehicle_id = $form['vehicle_id'];
		if($vehicle_id == ""){
			$error = "Please choose the vehicle this document belongs to.";
		}
	}
	if($error == "" && $form['expiry_date'] <= $form['issued_date']){
		$error = "The expiration date must be after the issued date.";
	}
	$remarks = $form['remarks'];
	if($remarks == ""){
		$remarks = null;
	}
?>
