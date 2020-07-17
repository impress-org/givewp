<div class="section-card">
	<div class="section-card--label">
		<span class="dashicons dashicons-<?php echo $dashicon; ?>"></span>
		<?php echo $labelText; ?>
	</div>
	<div class="section-card--action">
		<a class="button button-primary" href="<?php echo isset( $actionLocation ) ? $actionLocation : '#'; ?>"><?php echo $actionText; ?></a>
	</div>
</div>
