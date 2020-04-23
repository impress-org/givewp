<?php

use Give\Addon\FeeRecovery;
use function give_currency_filter as filterCurrency;
use function give_maybe_sanitize_amount as sanitizeAmount;

if ( ! $feeAmount ) {
	return;
}
?>
<div class="details-row">
	<div class="detail">
		<?php _e( 'Processing Fees', 'give' ); ?>
	</div>
	<div class="value">
		<?php
		echo give_fee_format_amount(
			sanitizeAmount( $feeAmount ),
			[
				'donation_id' => $payment->ID,
				'currency'    => $payment->currency,
			]
		);
		?>
	</div>
</div>
