<?php
namespace Give\Receipt\Detail\Donation;

use Give\Receipt\Detail;

class PaymentGateway extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'PAYMENT METHOD', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return '';
	}
}
