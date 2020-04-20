<?php

use Give\Addon\Recurring;

if ( ! Recurring::isActive() ) {
	return '';
}
?>
<div class="details">
	<h3 class="headline"><?php _e( 'Subscription Details', 'give' ); ?></h3>

	<div class="details-table">
		<div class="details-row">
			<div class="detail">
				<?php _e( 'Subscription', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo '$25 / Monthly <strong><a href="#" title="Edit amount of subscription">Edit</a></strong>'; ?>
			</div>
		</div>
		<div class="details-row">
			<div class="detail">
				<?php _e( 'Status', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo 'Active'; ?>
			</div>
		</div>

		<div class="details-row">
			<div class="detail">
				<?php _e( 'Renewal Date', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo 'May 20, 2020'; ?>
			</div>
		</div>

		<div class="details-row">
			<div class="detail">
				<?php _e( 'Progress', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo '1 / Ongoing'; ?>
			</div>
		</div>

	</div>
</div>
