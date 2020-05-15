<?php
namespace Give\Receipt\DonorDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_donation_address as getDonationDonorAddress;

/**
 * Class BillingAddress
 *
 * @since 2.7.0
 * @package Give\Receipt\DonorDetailsGroup\Details
 */
class BillingAddress extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return esc_html__( 'Billing Address', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		$address = getDonationDonorAddress( $this->donationId );

		if ( ! array_filter( $address ) ) {
			return '';
		}

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

	/**
	 * @inheritDoc
	 */
	public function getIcon() {
		return '<i class="fas fa-envelope"></i>';
	}
}
