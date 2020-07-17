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
				'paypal-partner-js',
				'https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js',
				[],
				null,
				true
			);

			$script = <<<EOT
				function givePayPalOnBoardedCallback(authCode, sharedId) {
					console.log( authCode, sharedId );
					const query = '&authCode=' + authCode + '&sharedId=' + sharedId;
					fetch( ajaxurl + '?action=give_paypal_commerce_user_on_boarded&_wpnonce={$nonce}' + query )
						.then(function(res){
							return res.json()
						})
						.then(function(res) {
							console.log(res)
						});
				}
EOT;

			wp_add_inline_script(
				'paypal-partner-js',
				$script
			);
		}
	}
}
