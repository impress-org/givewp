<?php
namespace Give\Receipt\DonationDetailsGroup\Details;

use Give\Receipt\Detail;
use function give_get_payment_meta as getDonationMetaData;
use function give_get_gateway_admin_label as getGatewayLabel;

class PaymentGateway extends Detail {
	/**
	 * @inheritDoc
	 */
	public function getLabel() {
		return __( 'PAYMENT METHOD', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getValue() {
		return getGatewayLabel( getDonationMetaData( $this->donationId, '_give_payment_gateway', true ) );
	}
}
