<?php
namespace Give\Receipt\Detail\Donation;

use Give\Receipt\Detail;

class Amount extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'DONATION AMOUNT', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return '';
	}
}
