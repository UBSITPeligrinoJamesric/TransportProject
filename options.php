<?php
	$travel_modes = [
		"driving" => "Driving",
		"transit" => "Transit",
	];

	$vehicle_types = ["Jeepney", "Modern Jeepney", "Modern UV", "Mini Bus", "City Bus"];

	$vehicle_statuses = ["Active", "Under Repair", "Suspended", "Retired"];

	$operator_statuses = ["Active", "Suspended", "Inactive"];

	$document_types = ["Franchise (CPC)", "OR/CR", "Insurance (CTPL)", "LTFRB Permit", "Mayor's Permit", "Emission Test Certificate"];

	$verification_statuses = ["Pending", "Verified", "Rejected"];

	$inspection_results = ["Pending", "Passed", "Failed"];

	date_default_timezone_set("Asia/Manila");
	$today = date("Y-m-d");
	$soon = date("Y-m-d", strtotime("+30 days"));
?>
