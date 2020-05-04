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

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return give_get_payment_statuses()[ get_post_status( $this->donationId ) ];
	}
}
