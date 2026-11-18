<?php
	require_once "../dbconn.php";
	require_once "../transport_functions.php";
	require_once "../options.php";

	$conn = new Inspections($pdo);
	$vehicle_conn = new Vehicles($pdo);
	$vehicles = $vehicle_conn->fetch_list();

	$url_id = $_GET['id'];
	$rows = $conn->show_update_data($url_id);

	$error = "";
	$form = null;
	if(count($rows) > 0){
		$form = [
			"vehicle_id" => $rows[0]['vehicle_id'],
			"schedule_date" => $rows[0]['schedule_date'],
			"inspection_date" => (string)$rows[0]['inspection_date'],
			"inspector" => (string)$rows[0]['inspector'],
			"result" => $rows[0]['result'],
			"defects" => (string)$rows[0]['defects'],
			"remarks" => (string)$rows[0]['remarks'],
		];
		if($form['result'] == "Pending" && $form['inspection_date'] == ""){
			$form['inspection_date'] = $today;
		}
	}

	if(isset($_POST['submit']) && $form != null){
		foreach($form as $key => $value){
			$form[$key] = trim($_POST[$key]);
		}
		if(!in_array($form['result'], $inspection_results)){
			$form['result'] = "Pending";
		}

		$inspection_date = $form['inspection_date'];
		$inspector = $form['inspector'];
		$defects = $form['defects'];
		$remarks = $form['remarks'];

		if($form['result'] != "Pending" && ($inspection_date == "" || $inspector == "")){
			$error = "Please enter the inspection date and the inspector before saving a result.";
		}
		else if($form['result'] == "Failed" && $defects == ""){
			$error = "Please list the defects found for a failed inspection.";
		}
		else{
			if($form['result'] == "Pending"){
				$inspection_date = null;
			}
			if($inspector == ""){
				$inspector = null;
			}
			if($defects == ""){
				$defects = null;
			}
			if($remarks == ""){
				$remarks = null;
			}
			$conn->update_data($form['vehicle_id'], $form['schedule_date'], $inspection_date, $inspector, $form['result'], $defects, $remarks, $url_id);

			header("location:view_inspection.php?id=" . (int)$url_id);
			exit();
		}
	}

	$page_title = "Inspection Result";
	$base = "../";
	include "../header.php";
?>
	<a class="back-link" href="manage_inspections.php"><?php echo $icon_back; ?> Back to Inspections</a>

	<?php if($error != ""){ ?>
	<div class="alert alert-red"><?php echo htmlspecialchars($error); ?></div>
	<?php } ?>

	<?php if($form == null){ ?>
	<div class="alert">Inspection not found.</div>
	<?php } else { ?>
	<div class="panel form-page">
		<div class="panel-head">Record Inspection Result</div>
		<div class="panel-body">
			<form method="post">
				<div class="form-section">Inspection Schedule</div>
				<div class="form-row">
					<label>Vehicle</label>
					<select name="vehicle_id" required>
						<?php foreach($vehicles as $v){ ?>
						<option value="<?php echo (int)$v['id']; ?>" <?php if($form['vehicle_id'] == $v['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($v['plate_number']); ?> - <?php echo htmlspecialchars($v['operator_name']); ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-row">
					<label>Scheduled Date</label>
					<input type="date" name="schedule_date" value="<?php echo htmlspecialchars($form['schedule_date']); ?>" required>
				</div>

				<div class="form-section">Inspection Result</div>
				<div class="form-two">
					<div class="form-row">
						<label>Date Inspected</label>
						<input type="date" name="inspection_date" value="<?php echo htmlspecialchars($form['inspection_date']); ?>">
					</div>
					<div class="form-row">
						<label>Result</label>
						<select name="result">
							<?php foreach($inspection_results as $res){ ?>
							<option value="<?php echo $res; ?>" <?php if($form['result'] == $res){ echo "selected"; } ?>><?php echo $res; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-row">
					<label>Inspector</label>
					<input type="text" name="inspector" maxlength="100" value="<?php echo htmlspecialchars($form['inspector']); ?>">
				</div>

				<div class="form-section">Defects and Remarks</div>
				<div class="form-row">
					<label>Defects Found</label>
					<textarea name="defects" rows="3" placeholder="List each defect, e.g. worn brake pads"><?php echo htmlspecialchars($form['defects']); ?></textarea>
				</div>
				<div class="form-row">
					<label>Remarks</label>
					<textarea name="remarks" rows="2"><?php echo htmlspecialchars($form['remarks']); ?></textarea>
				</div>
				<span class="small-note" style="margin:-4px 0 12px 0;">Passed = Compliant. Failed = Non-Compliant. Pending stays Scheduled or Overdue.</span>
				<div class="form-buttons">
					<button class="btn" name="submit">Save</button>
					<a class="btn btn-light" href="view_inspection.php?id=<?php echo (int)$url_id; ?>">Cancel</a>
				</div>
			</form>
		</div>
	</div>
	<?php } ?>
<?php
	include "../footer.php";
?>
