<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give_Admin_Settings;
use PayPalCheckoutSdk\Core\AccessTokenRequest;

/**
 * Class ScriptLoader
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class ScriptLoader {
	/**
	 * Paypal SDK handle.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	private $paypalSdkScriptHandle = 'give-paypal-sdk-js';

	/**
	 * Load admin scripts
	 *
	 * @since 2.8.0
	 */
	public function loadAdminScripts() {
		if ( Give_Admin_Settings::is_setting_page( 'gateway', 'paypal' ) ) {
			wp_enqueue_script(
				'give-paypal-partner-js',
				$this->getPartnerJsUrl(),
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
					fetch( ajaxurl + '?action=give_paypal_commerce_user_on_boarded' + query )
						.then(function(res){ return res.json() })
						.then(function(res) {
							if ( true !== res.success ) {
								alert("Something went wrong!");
								}
							}
						);
				}
EOT;

			wp_add_inline_script(
				'give-paypal-partner-js',
				$script
			);
		}
	}

	/**
	 * Load public assets.
	 *
	 * @since 2.8.0
	 */
	public function loadPublicAssets() {
		/* @var MerchantDetail $merchant */
		$merchant = give( MerchantDetail::class );

		wp_enqueue_script(
			$this->paypalSdkScriptHandle,
			sprintf(
				'https://www.paypal.com/sdk/js?components=%1$s&client-id=%2$s&merchant-id=%3$s&currency=%4$s&intent=capture',
				'hosted-fields,buttons',
				$merchant->clientId,
				$merchant->merchantIdInPayPal,
				give_get_currency()
			),
			[ 'give' ],
			null,
			false
		);

		add_filter( 'script_loader_tag', [ $this, 'addAttributesToPayPalSdkScript' ], 10, 2 );

		wp_enqueue_script(
			'give-paypal-commerce-js',
			GIVE_PLUGIN_URL . 'assets/dist/js/paypal-commerce.js',
			[ $this->paypalSdkScriptHandle ],
			GIVE_VERSION,
			true
		);
	}

	/**
	 * Add attributes to PayPal sdk.
	 *
	 * @param string $tag
	 * @param string $handle
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function addAttributesToPayPalSdkScript( $tag, $handle ) {
		if ( $this->paypalSdkScriptHandle !== $handle ) {
			return $tag;
		}

		$tag = str_replace(
			'src=',
			sprintf(
				'data-partner-attribution-id="%1$s" data-client-token="%2$s" src=',
				PartnerDetails::$attributionId,
				MerchantDetails::getClientToken()
			),
			$tag
		);

		return $tag;
	}

	/**
	 * Get PayPal partner js url.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	private function getPartnerJsUrl() {
		return sprintf(
			'https://www.%1$spaypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js',
			$this->getPayPalScriptFileUrlPrefix()
		);
	}

	/**
	 * Get PayPal script file url prefix.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	private function getPayPalScriptFileUrlPrefix() {
		return 'sandbox' === give( PayPalClient::class )->mode ? 'sandbox.' : '';
	}
}
