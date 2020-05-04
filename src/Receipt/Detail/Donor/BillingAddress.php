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
		$address = getDonorAddress( getDonationDonorId( $this->donationId ) );

		return sprintf(
			'%1$s<br>%2$s%3$s,%4$s%5$s<br>%6$s',
			$address['line1'],
			! empty( $address['line2'] ) ? $address['line2'] . '<br>' : '',
			$address['city'],
			$address['state'],
			$address['zip'],
			$address['country']
		);
	}
}
