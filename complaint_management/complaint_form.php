<?php if(isset($form['complainant_name'])){ ?>
<div class="form-section">Complainant</div>
<div class="form-row">
	<label>Complainant Name</label>
	<input type="text" name="complainant_name" data-input="name" maxlength="100" value="<?php echo htmlspecialchars($form['complainant_name']); ?>" required>
</div>
<div class="form-row">
	<label>Contact Number (optional)</label>
	<input type="text" name="contact_number" data-input="phone" inputmode="numeric" maxlength="20" placeholder="09XXXXXXXXX" pattern="09[0-9]{9}" title="11 digits starting with 09" value="<?php echo htmlspecialchars($form['contact_number']); ?>">
</div>
<?php } ?>

<div class="form-section">Vehicle and Route</div>
<?php if(isset($scanned) && count($scanned) > 0){ ?>
<div class="form-row">
	<label>Vehicle</label>
	<div class="fixed-field">
		<b style="color:var(--text);"><?php echo htmlspecialchars($scanned[0]['plate_number']); ?></b> &middot; <?php echo htmlspecialchars($scanned[0]['vehicle_type']); ?>
		<span class="sub">Operator: <?php echo htmlspecialchars($scanned[0]['operator_name']); ?></span>
	</div>
	<input type="hidden" name="vehicle_id" value="<?php echo (int)$scanned[0]['id']; ?>">
</div>
<?php } else { ?>
<div class="form-row">
	<label>Vehicle (plate number)</label>
	<select name="vehicle_id">
		<option value="">Unknown / not specified</option>
		<?php foreach($vehicles as $v){ ?>
		<option value="<?php echo (int)$v['id']; ?>" <?php if($form['vehicle_id'] == $v['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($v['plate_number']); ?> - <?php echo htmlspecialchars($v['vehicle_type']); ?></option>
		<?php } ?>
	</select>
</div>
<?php } ?>
<div class="form-row">
	<label>Route</label>
	<select name="route_id">
		<option value="">Same as the vehicle's route</option>
		<?php foreach($routes as $r){ ?>
		<option value="<?php echo (int)$r['id']; ?>" <?php if($form['route_id'] == $r['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($r['route_code'] . " - " . $r['route_name']); ?></option>
		<?php } ?>
	</select>
	<span class="small-note">Choose at least the vehicle or the route.</span>
</div>

<div class="form-section">Complaint Details</div>
<div class="form-row">
	<label>What happened?</label>
	<select name="concern" required>
		<option value="">Select a concern</option>
		<?php foreach($complaint_concerns as $type => $concerns){ ?>
		<optgroup label="<?php echo htmlspecialchars($type); ?>">
			<?php foreach($concerns as $c){ ?>
			<option value="<?php echo htmlspecialchars($c); ?>" <?php if($form['concern'] == $c){ echo "selected"; } ?>><?php echo htmlspecialchars($c); ?></option>
			<?php } ?>
		</optgroup>
		<?php } ?>
	</select>
</div>
<div class="form-two">
	<div class="form-row">
		<label>Date of Incident</label>
		<input type="date" name="incident_date" max="<?php echo $today; ?>" value="<?php echo htmlspecialchars($form['incident_date']); ?>" required>
	</div>
	<div class="form-row">
		<label>Location (optional)</label>
		<input type="text" name="incident_location" maxlength="150" placeholder="e.g. Near San Luis chapel" value="<?php echo htmlspecialchars($form['incident_location']); ?>">
	</div>
</div>
<div class="form-row">
	<label>Description</label>
	<textarea name="description" rows="4" placeholder="Tell us what happened..." required><?php echo htmlspecialchars($form['description']); ?></textarea>
</div>
