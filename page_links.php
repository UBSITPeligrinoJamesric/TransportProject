<?php
	$total_pages = ceil($total_rows / $per_page);
	$first_shown = 0;
	$last_shown = 0;
	if($total_rows > 0){
		$first_shown = ($page - 1) * $per_page + 1;
		$last_shown = min($page * $per_page, $total_rows);
	}

	$query = $_GET;
?>
<div class="table-footer">
	<span>Showing <?php echo $first_shown; ?> to <?php echo $last_shown; ?> of <?php echo $total_rows; ?> entries</span>

	<?php if($total_pages > 1){ ?>
	<div class="pages">
		<?php $query['page'] = $page - 1; ?>
		<?php if($page > 1){ ?>
			<a href="?<?php echo htmlspecialchars(http_build_query($query)); ?>">Previous</a>
		<?php } else { ?>
			<span class="disabled">Previous</span>
		<?php } ?>
		<?php for($i = 1; $i <= $total_pages; $i++){ ?>
			<?php $query['page'] = $i; ?>
			<a class="<?php if($i == $page){ echo "current"; } ?>" href="?<?php echo htmlspecialchars(http_build_query($query)); ?>"><?php echo $i; ?></a>
		<?php } ?>
		<?php $query['page'] = $page + 1; ?>
		<?php if($page < $total_pages){ ?>
			<a href="?<?php echo htmlspecialchars(http_build_query($query)); ?>">Next</a>
		<?php } else { ?>
			<span class="disabled">Next</span>
		<?php } ?>
	</div>
	<?php } ?>
</div>
