<?php

namespace Give\PaymentGateways\PayPalStandard\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class RemovePayPalIPNVerificationSetting extends Migration {

	/**
	 * @inheritDoc
	 */
	public function run() {
		// Reset paypal gateway id to paypal.
		$give_settings  = give_get_settings();

		if ( array_key_exists( 'paypal_verification', $give_settings ) ) {
			give_delete_option( 'paypal_verification' );
		}
	}

	/**
	 * @unreleased
	 * @return string
	 */
	public static function id() {
		return 'remove-paypal=ipn-verification-setting';
	}

	/**
	 * @unreleased
	 * @return int
	 */
	public static function timestamp() {
		return strtotime( '2021-09-28' );
	}
}
