<?php

use Give\Addon\FeeRecovery;
use function give_currency_filter as filterCurrency;
use function give_sanitize_amount as sanitizeAmount;

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
		$fees = $payment->total - $payment->subtotal;
		echo filterCurrency(
			sanitizeAmount( $fees ),
			[
				'currency_code'   => $payment->currency,
				'decode_currency' => true,
				'form_id'         => $payment->form_id,
			]
		);
		?>
	</div>
</div>
