<?php
namespace Give\Receipt\Detail\Donation;

use Give\Receipt\Detail;

class TotalAmount extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'DONATION TOTAL', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return '';
	}
}
