<?php
namespace Give\Receipt\DonorDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_donation_donor_email as getDonationDonorEmail;

/**
 * Class Email
 *
 * @since 2.7.0
 * @package Give\Receipt\DonorDetailsGroup\Details
 */
class Email extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return esc_html__( 'Email Address', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return getDonationDonorEmail( $this->donationId );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon() {
		return '<i class="fas fa-envelope"></i>';
	}
}
