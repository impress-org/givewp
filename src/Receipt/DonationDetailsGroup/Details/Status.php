<?php
namespace Give\Receipt\DonationDetailsGroup\Details;

use Give\Receipt\Detail;

/**
 * Class Status
 *
 * @since 2.7.0
 * @package Give\Receipt\DonationDetailsGroup\Details
 */
class Status extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return esc_html__( 'Payment Status', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return give_get_payment_statuses()[ get_post_status( $this->donationId ) ];
	}
}
