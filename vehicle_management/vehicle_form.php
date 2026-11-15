<div class="form-section">Vehicle Details</div>
<div class="form-two">
	<div class="form-row">
		<label>Plate Number</label>
		<input type="text" name="plate_number" maxlength="20" placeholder="ABC 1234" value="<?php echo htmlspecialchars($form['plate_number']); ?>" required>
	</div>
	<div class="form-row">
		<label>Vehicle Type</label>
		<select name="vehicle_type" required>
			<?php foreach($vehicle_types as $t){ ?>
			<option value="<?php echo $t; ?>" <?php if($form['vehicle_type'] == $t){ echo "selected"; } ?>><?php echo $t; ?></option>
			<?php } ?>
		</select>
	</div>
</div>
<div class="form-two">
	<div class="form-row">
		<label>Make</label>
		<input type="text" name="make" maxlength="50" placeholder="Isuzu" value="<?php echo htmlspecialchars($form['make']); ?>" required>
	</div>
	<div class="form-row">
		<label>Model</label>
		<input type="text" name="model" maxlength="50" placeholder="QKR77" value="<?php echo htmlspecialchars($form['model']); ?>" required>
	</div>
</div>
<div class="form-two">
	<div class="form-row">
		<label>Year Model</label>
		<input type="number" name="year_model" min="1950" max="<?php echo date("Y") + 1; ?>" value="<?php echo htmlspecialchars($form['year_model']); ?>" required>
	</div>
	<div class="form-row">
		<label>Color</label>
		<input type="text" name="color" maxlength="30" value="<?php echo htmlspecialchars($form['color']); ?>" required>
	</div>
</div>
<div class="form-two">
	<div class="form-row">
		<label>Capacity (seats)</label>
		<input type="number" name="capacity" min="1" max="100" value="<?php echo htmlspecialchars($form['capacity']); ?>" required>
	</div>
	<div class="form-row">
		<label>Vehicle Status</label>
		<select name="status">
			<?php foreach($vehicle_statuses as $s){ ?>
			<option value="<?php echo $s; ?>" <?php if($form['status'] == $s){ echo "selected"; } ?>><?php echo $s; ?></option>
			<?php } ?>
		</select>
	</div>
</div>
<div class="form-two">
	<div class="form-row">
		<label>Engine No.</label>
		<input type="text" name="engine_number" maxlength="40" value="<?php echo htmlspecialchars($form['engine_number']); ?>" required>
	</div>
	<div class="form-row">
		<label>Chassis No.</label>
		<input type="text" name="chassis_number" maxlength="40" value="<?php echo htmlspecialchars($form['chassis_number']); ?>" required>
	</div>
</div>

<div class="form-section">Ownership and Registration</div>
<div class="form-row">
	<label>Operator (Owner)</label>
	<select name="operator_id" required>
		<option value="">Select operator</option>
		<?php foreach($operators as $op){ ?>
		<option value="<?php echo (int)$op['id']; ?>" <?php if($form['operator_id'] == $op['id']){ echo "selected"; } ?>>
			<?php echo htmlspecialchars($op['full_name']); ?> (<?php echo htmlspecialchars($op['registration_number']); ?>)<?php if($op['status'] != "Active"){ echo " - " . htmlspecialchars($op['status']); } ?>
		</option>
		<?php } ?>
	</select>
</div>
<div class="form-row">
	<label>Date Registered</label>
	<input type="date" name="date_registered" value="<?php echo htmlspecialchars($form['date_registered']); ?>" required>
</div>
<?php if(isset($form['or_cr_number'])){ ?>
<div class="form-section">Registration Document (OR/CR)</div>
<div class="form-two">
	<div class="form-row">
		<label>OR/CR Number</label>
		<input type="text" name="or_cr_number" maxlength="50" placeholder="OR-00000000" value="<?php echo htmlspecialchars($form['or_cr_number']); ?>">
	</div>
	<div class="form-row">
		<label>OR/CR Expiry</label>
		<input type="date" name="or_cr_expiry" value="<?php echo htmlspecialchars($form['or_cr_expiry']); ?>">
	</div>
</div>
<span class="small-note" style="margin:-6px 0 12px 0;">Optional. This adds the OR/CR to Permits &amp; Documents for verification.</span>
<?php } ?>
