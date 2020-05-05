<?php
namespace Give\Receipt\DonorDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_donation_donor_email as getDonationDonorEmail;

class Email extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'EMAIL ADDRESS', 'give' );
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
