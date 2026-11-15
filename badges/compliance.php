<?php if($badge_result == "Passed"){ ?>
	<span class="badge badge-green">Compliant</span>
<?php } else if($badge_result == "Failed"){ ?>
	<span class="badge badge-red">Non-Compliant</span>
<?php } else if($badge_schedule < $today){ ?>
	<span class="badge badge-red">Overdue</span>
<?php } else { ?>
	<span class="badge badge-blue">Scheduled</span>
<?php } ?>
