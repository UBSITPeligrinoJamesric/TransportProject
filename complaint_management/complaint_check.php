<?php
	$error = "";
	$complaint_type = "";
	foreach($complaint_concerns as $type => $concerns){
		if(in_array($form['concern'], $concerns)){
			$complaint_type = $type;
		}
	}

	$vehicle_id = $form['vehicle_id'];
	$route_id = $form['route_id'];
	if($vehicle_id == ""){
		$vehicle_id = null;
	}
	if($route_id == ""){
		$route_id = null;
	}

	if($complaint_type == ""){
		$error = "Please choose what the complaint is about.";
	}
	else if($vehicle_id == null && $route_id == null){
		$error = "Please choose the vehicle or the route involved.";
	}
	else if($form['incident_date'] > $today){
		$error = "The date of the incident cannot be in the future.";
	}
	else if($form['description'] == ""){
		$error = "Please describe what happened.";
	}

	if($error == "" && $vehicle_id != null && $route_id == null){
		$vehicle_rows = $vehicle_conn->show_update_data($vehicle_id);
		if(count($vehicle_rows) > 0){
			$route_id = $vehicle_rows[0]['route_id'];
		}
	}

	$incident_location = $form['incident_location'];
	if($incident_location == ""){
		$incident_location = null;
	}
?>
