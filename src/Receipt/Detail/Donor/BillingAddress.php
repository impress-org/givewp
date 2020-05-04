<?php
namespace Give\Receipt\Detail\Donor;

use Give\Receipt\Detail;
use function give_get_payment_donor_id as getDonationDonorId;
use function give_get_donor_address as getDonorAddress;

class BillingAddress extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'BILLING ADDRESS', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return getDonorAddress( getDonationDonorId( $this->donationId ) );
	}
}
