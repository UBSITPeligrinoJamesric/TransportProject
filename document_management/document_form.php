<div class="form-section">Permit Information</div>
<div class="form-row">
	<label>Document Type</label>
	<select name="document_type" required>
		<?php foreach($document_types as $t){ ?>
		<option value="<?php echo htmlspecialchars($t); ?>" <?php if($form['document_type'] == $t){ echo "selected"; } ?>><?php echo htmlspecialchars($t); ?></option>
		<?php } ?>
	</select>
</div>
<div class="form-row">
	<label>Document / Permit No.</label>
	<input type="text" name="document_number" maxlength="50" value="<?php echo htmlspecialchars($form['document_number']); ?>" required>
</div>
<div class="form-row">
	<label>Belongs To</label>
	<select name="holder_type" class="holder-type" required>
		<option value="Operator" <?php if($form['holder_type'] == "Operator"){ echo "selected"; } ?>>Operator</option>
		<option value="Vehicle" <?php if($form['holder_type'] == "Vehicle"){ echo "selected"; } ?>>Vehicle</option>
	</select>
</div>
<div class="form-row holder-operator">
	<label>Operator</label>
	<select name="operator_id">
		<option value="">Select operator</option>
		<?php foreach($operators as $op){ ?>
		<option value="<?php echo (int)$op['id']; ?>" <?php if($form['operator_id'] == $op['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($op['full_name']); ?> (<?php echo htmlspecialchars($op['registration_number']); ?>)</option>
		<?php } ?>
	</select>
</div>
<div class="form-row holder-vehicle">
	<label>Vehicle</label>
	<select name="vehicle_id">
		<option value="">Select vehicle</option>
		<?php foreach($vehicles as $v){ ?>
		<option value="<?php echo (int)$v['id']; ?>" <?php if($form['vehicle_id'] == $v['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($v['plate_number']); ?> - <?php echo htmlspecialchars($v['operator_name']); ?></option>
		<?php } ?>
	</select>
</div>

<div class="form-section">Validity</div>
<div class="form-two">
	<div class="form-row">
		<label>Issued Date</label>
		<input type="date" name="issued_date" value="<?php echo htmlspecialchars($form['issued_date']); ?>" required>
	</div>
	<div class="form-row">
		<label>Expiration Date</label>
		<input type="date" name="expiry_date" value="<?php echo htmlspecialchars($form['expiry_date']); ?>" required>
	</div>
</div>
<div class="form-row">
	<label>Remarks (optional)</label>
	<input type="text" name="remarks" maxlength="255" value="<?php echo htmlspecialchars($form['remarks']); ?>">
</div>

<script>
	(function () {
		var form = document.currentScript.closest("form");
		var typeBox = form.querySelector(".holder-type");
		function showHolder() {
			form.querySelector(".holder-operator").style.display = typeBox.value == "Operator" ? "" : "none";
			form.querySelector(".holder-vehicle").style.display = typeBox.value == "Vehicle" ? "" : "none";
		}
		typeBox.onchange = showHolder;
		form.addEventListener("reset", function () { setTimeout(showHolder, 0); });
		showHolder();
	})();
</script>
