<?php
namespace Give\Receipt\Detail\Donor;

use Give\Receipt\Detail;
use function give_get_payment_donor_id as getDonationDonorId;
use function give_get_donor_name_by as getDonorNameBy;

class Name extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'DONOR NAME', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return getDonorNameBy( getDonationDonorId( $this->donationId ) );
	}
}
