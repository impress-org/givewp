<?php
namespace Give\Receipt\Detail\Donation;

use Give\Receipt\Detail;

class Status extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'PAYMENT STATUS', 'give' );
	}

	public function getValue() {
		return '';
	}
}
