<?php

namespace Give\PaymentGateways\PaypalCommerce;

use Give_Admin_Settings;

/**
 * Class ScriptLoader
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @since 2.8.0
 */
class ScriptLoader {
	/**
	 * Setup hooks
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		add_action( 'admin_enqueue_scripts', [ $this, 'loadAdminScripts' ] );
	}

	/**
	 * Load admin scripts
	 *
	 * @since 2.8.0
	 */
	public function loadAdminScripts() {
		if ( Give_Admin_Settings::is_setting_page( 'gateway', 'paypal' ) ) {
			$nonce = wp_create_nonce( 'give_paypal_commerce_user_onboarded' );

			wp_enqueue_script(
				'give-paypal-partner-js',
				'https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js',
				[],
				null,
				true
			);

			wp_localize_script(
				'give-paypal-partner-js',
				'givePayPalCommerce',
				[
					'translations' => [
						'confirmPaypalAccountDisconnection' => esc_html__( 'Confirm PayPal account disconnection', 'give' ),
						'disconnectPayPalAccount' => esc_html__( 'Do you want to disconnect PayPal account?', 'give' ),
					],
				]
			);

			$script = <<<EOT
				function givePayPalOnBoardedCallback(authCode, sharedId) {
					const query = '&authCode=' + authCode + '&sharedId=' + sharedId;
					fetch( ajaxurl + '?action=give_paypal_commerce_user_on_boarded&_wpnonce={$nonce}' + query );
				}
EOT;

			wp_add_inline_script(
				'paypal-partner-js',
				$script
			);
		}
	}
}
