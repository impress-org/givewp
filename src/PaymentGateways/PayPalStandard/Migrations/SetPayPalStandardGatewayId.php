<?php
namespace Give\PaymentGateways\PayPalStandard\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class SetPayPalStandardGatewayId
 * @package Give\PaymentGateways\PayPalStandard\Migrations
 *
 * @since 2.9.1
 */
class SetPayPalStandardGatewayId extends Migration {

	/**
	 * @inheritdoc
	 */
	public function run() {
		// Reset paypal gateway id to paypal.
		$give_settings = give_get_settings();
		$gateways      = $give_settings['gateways'];
		if ( array_key_exists( 'paypal-standard', $gateways ) ) {
			unset( $gateways['paypal-standard'] );
			$gateways['paypal'] = '1';
			give_update_option( 'gateways', $gateways );
		}

		// Reset paypal gateway custom label.
		$gateways_label = give_get_option( 'gateways_label' );
		if ( array_key_exists( 'paypal-standard', $gateways_label ) ) {
			$gateways_label['paypal'] = $gateways_label['paypal-standard'];
			unset( $gateways_label['paypal-standard'] );
			give_update_option( 'gateways_label', $gateways_label );
		}
	}

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return 'set_paypal_standard_id_to_paypal_from_paypal_standard';
	}

	/**
	 * @inheritdoc
	 */
	public static function timestamp() {
		return strtotime( '2020-10-28' );
	}
}
