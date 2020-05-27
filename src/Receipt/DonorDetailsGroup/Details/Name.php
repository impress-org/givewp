<?php
namespace Give\Receipt\DonorDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_payment_meta as getDonationDonorName;

/**
 * Class Name
 *
 * @since 2.7.0
 * @package Give\Receipt\DonorDetailsGroup\Details
 */
class Name extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return esc_html__( 'Donor Name', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$firstName = getDonationDonorName( $this->donationId, '_give_donor_billing_first_name', true );
		$lastName  = getDonationDonorName( $this->donationId, '_give_donor_billing_last_name', true );

		return trim( "{$firstName} {$lastName}" );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon() {
		return '<i class="fas fa-user"></i>';
	}
}
