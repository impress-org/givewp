<?php
namespace Give\Receipt\DonationDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_payment_meta as getDonationMetaData;
use function give_get_gateway_admin_label as getGatewayLabel;

/**
 * Class PaymentGateway
 *
 * @since 2.7.0
 * @package Give\Receipt\DonationDetailsGroup\Details
 */
class PaymentGateway extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return esc_html__( 'Payment Method', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return getGatewayLabel( getDonationMetaData( $this->donationId, '_give_payment_gateway', true ) );
	}
}
