<div class="form-section">Registration Record</div>
<div class="form-row">
	<label>Registration No.</label>
	<input type="text" name="registration_number" maxlength="30" value="<?php echo htmlspecialchars($form['registration_number']); ?>" required>
</div>
<div class="form-row">
	<label>Date Registered</label>
	<input type="date" name="date_registered" value="<?php echo htmlspecialchars($form['date_registered']); ?>" required>
</div>

<div class="form-section">Operator Information</div>
<div class="form-row">
	<label>Full Name / Company Name</label>
	<input type="text" name="full_name" maxlength="100" value="<?php echo htmlspecialchars($form['full_name']); ?>" required>
</div>
<?php if(isset($form['status'])){ ?>
<div class="form-row">
	<label>Status</label>
	<select name="status">
		<?php foreach($operator_statuses as $s){ ?>
		<option value="<?php echo $s; ?>" <?php if($form['status'] == $s){ echo "selected"; } ?>><?php echo $s; ?></option>
		<?php } ?>
	</select>
</div>
<?php } ?>

<div class="form-section">Contact Information</div>
<div class="form-two">
	<div class="form-row">
		<label>Contact Number</label>
		<input type="text" name="contact_number" maxlength="30" placeholder="09XXXXXXXXX" value="<?php echo htmlspecialchars($form['contact_number']); ?>" required>
	</div>
	<div class="form-row">
		<label>Email (optional)</label>
		<input type="email" name="email" maxlength="100" value="<?php echo htmlspecialchars($form['email']); ?>">
	</div>
</div>
<div class="form-row">
	<label>Address</label>
	<input type="text" name="address" maxlength="150" placeholder="Barangay, City" value="<?php echo htmlspecialchars($form['address']); ?>" required>
</div>

<div class="form-section">Association Membership</div>
<div class="form-row">
	<label>Association</label>
	<select name="association_id">
		<option value="">Not a member</option>
		<?php foreach($associations as $as){ ?>
		<option value="<?php echo (int)$as['id']; ?>" <?php if($form['association_id'] == $as['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($as['association_name']); ?></option>
		<?php } ?>
	</select>
</div>
<div class="form-two">
	<div class="form-row">
		<label>Membership No.</label>
		<input type="text" name="membership_number" maxlength="30" value="<?php echo htmlspecialchars($form['membership_number']); ?>">
	</div>
	<div class="form-row">
		<label>Member Since</label>
		<input type="date" name="member_since" value="<?php echo htmlspecialchars($form['member_since']); ?>">
	</div>
</div>
