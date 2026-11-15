<?php if($badge_status == "Active"){ ?>
	<span class="badge badge-green">Active</span>
<?php } else if($badge_status == "Under Repair"){ ?>
	<span class="badge badge-orange">Under Repair</span>
<?php } else if($badge_status == "Suspended"){ ?>
	<span class="badge badge-red">Suspended</span>
<?php } else { ?>
	<span class="badge badge-gray"><?php echo htmlspecialchars($badge_status); ?></span>
<?php } ?>
