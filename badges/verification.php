<?php if($badge_status == "Verified"){ ?>
	<span class="badge badge-green">Verified</span>
<?php } else if($badge_status == "Rejected"){ ?>
	<span class="badge badge-red">Rejected</span>
<?php } else { ?>
	<span class="badge badge-orange">Pending</span>
<?php } ?>
