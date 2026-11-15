<?php
	if(!isset($base)){
		$base = "";
	}

	include __DIR__ . "/icons.php";

	$current_page = $_SERVER['PHP_SELF'];
	$active = "dashboard";
	$modules = [
		"operator_management" => "operators",
		"vehicle_management" => "vehicles",
		"route_management" => "routes",
		"assignment_management" => "assignments",
		"document_management" => "documents",
		"inspection_management" => "inspections",
	];
	foreach($modules as $folder => $name){
		if(strpos($current_page, $folder) !== false){
			$active = $name;
		}
	}

	$global_search = "";
	if(isset($_GET['q'])){
		$global_search = $_GET['q'];
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Asin Transportation - <?php echo htmlspecialchars($page_title); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo $base; ?>style.css">
	<?php if(isset($use_transit)){ ?>
	<script src="<?php echo $base; ?>route_map.js"></script>
	<?php } ?>
</head>
<body>
<div class="layout">

	<aside class="sidebar">
		<a class="logo" href="<?php echo $base; ?>index.php">
			<span>Asin Transportation</span>
		</a>

		<nav class="nav">
			<a class="<?php if($active == "dashboard"){ echo "active"; } ?>" href="<?php echo $base; ?>index.php"><?php echo $icon_dashboard; ?> Dashboard</a>
			<a class="<?php if($active == "operators"){ echo "active"; } ?>" href="<?php echo $base; ?>operator_management/manage_operators.php"><?php echo $icon_operator; ?> Operators</a>
			<a class="<?php if($active == "vehicles"){ echo "active"; } ?>" href="<?php echo $base; ?>vehicle_management/manage_vehicles.php"><?php echo $icon_vehicle; ?> Vehicles</a>
			<a class="<?php if($active == "routes"){ echo "active"; } ?>" href="<?php echo $base; ?>route_management/manage_routes.php"><?php echo $icon_route; ?> Routes</a>
			<a class="<?php if($active == "assignments"){ echo "active"; } ?>" href="<?php echo $base; ?>assignment_management/manage_assignments.php"><?php echo $icon_assign; ?> Assignments</a>
			<a class="<?php if($active == "documents"){ echo "active"; } ?>" href="<?php echo $base; ?>document_management/manage_documents.php"><?php echo $icon_document; ?> Permits &amp; Documents</a>
			<a class="<?php if($active == "inspections"){ echo "active"; } ?>" href="<?php echo $base; ?>inspection_management/manage_inspections.php"><?php echo $icon_inspection; ?> Inspections</a>
		</nav>

		<div class="system-status">
			<span class="dot"></span>
			<div>
				<b>System Online</b>
				<small>v1.0</small>
			</div>
		</div>
	</aside>

	<div class="main">
		<header class="topbar">
			<h1><?php echo htmlspecialchars($page_title); ?></h1>

			<form class="global-search" method="get" action="<?php echo $base; ?>search.php">
				<?php echo $icon_search; ?>
				<input type="text" name="q" placeholder="Search system globally..." value="<?php echo htmlspecialchars($global_search); ?>">
			</form>

			<div class="user">
				<span class="avatar">AD</span>
				<div>
					<b>Admin User</b>
					<small>Dispatcher</small>
				</div>
			</div>
		</header>

		<div class="content">
