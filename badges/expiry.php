<?php if($badge_date < $today){ ?>
	<span class="badge badge-red">Expired</span>
<?php } else if($badge_date <= $soon){ ?>
	<span class="badge badge-orange">Expiring Soon</span>
<?php } else { ?>
	<span class="badge badge-green">Valid</span>
<?php } ?>
