<?php
namespace Give\PaymentGateways\PayPalCheckout;

/**
 * Class PayPalOnBoardingRedirectHandler
 * @package Give\PaymentGateways\PayPalCheckout
 *
 * @since 2.8.0
 */
class PayPalOnBoardingRedirectHandler {
	/**
	 * Bootstrap class
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		// add_action( 'give-settings_start', [ $this, 'savePayPalMerchantDetails' ] );
		add_action( 'admin_init', [ $this, 'savePayPalMerchantDetails' ] );
	}

	/**
	 * Save PayPal merchant details
	 *
	 * @return void
	 * @since 2.8.0
	 */
	public function savePayPalMerchantDetails() {
		// Save PayPal merchant details only if admin redirected to PayPal connect setting page after completing onboarding process.
		if (
		! isset( $_GET['merchantIdInPayPal'] )
			// ! \Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' )
		) {
			return;
		}

		$paypalGetData = wp_parse_args( $_SERVER['QUERY_STRING'] );

		$allowedPayPalData = [
			'merchantIdInPayPal',
			'permissionsGranted',
			'accountStatus',
			'consentStatus',
			'productIntentId',
			'isEmailConfirmed',
			'returnMessage',
		];

		$allAccounts = (array) maybe_unserialize( give_update_option( 'paypalCheckoutAccounts', [] ) );
		$paypalData  = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );

		$allAccounts[ $paypalData['merchantIdInPayPal'] ] = $paypalData;

		give_update_option( 'paypalCheckoutAccounts', maybe_serialize( $allAccounts ) );

		$this->saveSellerRestAPICredentials( $paypalData['merchantIdInPayPal'] );
	}

	/**
	 * Save seller rest API credentials
	 *
	 * @param string $partnerMerchantId
	 * @return void
	 * @since 2.8.0
	 */
	private function saveSellerRestAPICredentials( $partnerMerchantId ) {
		$tokenInfo      = get_option( 'give_paypal_checkout_seller_access_token', [ 'access_token' => '' ] );
		$payPalResponse = wp_remote_retrieve_body(
			wp_remote_get(
				sprintf(
					'https://api.sandbox.paypal.com/v1/customer/partners/%1$s/merchant-integrations/credentials/',
					$partnerMerchantId
				),
				[
					'headers' => [
						'Authorization' => sprintf(
							'Bearer %1$s',
							$tokenInfo['access_token']
						),
						'Content-Type'  => 'application/json',
					],
				]
			)
		);

		$mode      = give_is_test_mode() ? 'sandbox' : 'live';
		$optionKey = 'give_paypal_checkout_seller_rest_api_credentials';

		$optionValue          = get_option( $optionKey, [] );
		$optionValue[ $mode ] = json_decode( $payPalResponse, true );

		update_option( $optionKey, $optionValue );
	}
}
