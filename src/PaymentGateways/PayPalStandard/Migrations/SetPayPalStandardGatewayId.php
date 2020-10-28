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
		$gateways = give_get_option( 'gateways' );
		if ( array_key_exists( 'paypal-standard', $gateways ) ) {
			unset( $gateways['paypal-standard'] );
			$gateways['paypal'] = '1';
			give_update_option( 'gateways', $gateways );
		}

		$gateways_label = give_get_option( 'gateways_label' );
		if ( array_key_exists( 'paypal-standard', $gateways_label ) ) {
			$gateways['paypal'] = $gateways['paypal-standard'];
			unset( $gateways['paypal-standard'] );
			give_update_option( 'gateways_label', $gateways );
		}
	}

	/**
	 * Return a unique identifier for the migration
	 *
	 * @return string
	 */
	public static function id() {
		return 'set-paypal-standard-id-to-paypal-from-paypal-standard';
	}

	/**
	 * Return a Unix Timestamp for when the migration was created
	 *
	 * Example: strtotime( '2020-09-16 ')
	 *
	 * @since 2.9.0
	 *
	 * @return int Unix timestamp for when the migration was created
	 */
	public static function timestamp() {
		return strtotime( '2020-10-28' );
	}
}
