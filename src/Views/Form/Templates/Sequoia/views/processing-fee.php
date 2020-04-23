<?php

use Give\Addon\FeeRecovery;
use function give_currency_filter as filterCurrency;
use function give_maybe_sanitize_amount as sanitizeAmount;

if ( ! FeeRecovery::isActive() || ! FeeRecovery::canFormRecoverFee() ) {
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
			sanitizeAmount( give_get_meta( $donation_id, '_give_fee_amount', true ) ),
			[
				'donation_id' => $payment->ID,
				'currency'    => $payment->currency,
			]
		);
		?>
	</div>
</div>
