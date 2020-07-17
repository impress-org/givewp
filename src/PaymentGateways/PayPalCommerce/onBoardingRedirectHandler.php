<?php
namespace Give\PaymentGateways\PaypalCommerce;

use Give_Admin_Settings;

/**
 * Class PayPalOnBoardingRedirectHandler
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @since 2.8.0
 */
class onBoardingRedirectHandler {

	/**
	 * Environment type.
	 *
	 * @var string
	 * @since 2.8.0
	 */
	private $mode;

	/**
	 * Bootstrap class
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		$this->mode = give_is_test_mode() ? 'sandbox' : 'live';

		if ( $this->isPayPalUserRedirected() ) {
			$this->savePayPalMerchantDetails();
		}
	}

	/**
	 * Save PayPal merchant details
	 * @todo: Confirm `primary_email_confirmed` set to true via PayPal api to confirm onboarding process status.
	 *
	 * @return void
	 * @since 2.8.0
	 */
	public function savePayPalMerchantDetails() {
		$paypalGetData = wp_parse_args( $_SERVER['QUERY_STRING'] );

		$allowedPayPalData = [
			'merchantId',
			'merchantIdInPayPal',
		];

		$payPalAccounts = (array) get_option( OptionId::$payPalAccountsOptionKey, [] );
		$paypalData     = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );

		// Reset account details.
		$payPalAccounts[ $paypalData['merchantIdInPayPal'] ] = [];
		$payPalAccounts[ $paypalData['merchantIdInPayPal'] ] = array_merge( $payPalAccounts[ $paypalData['merchantIdInPayPal'] ], $paypalData );

		$this->saveSellerRestAPICredentials( $payPalAccounts, $paypalData['merchantIdInPayPal'] );

		update_option( OptionId::$payPalAccountsOptionKey, $payPalAccounts );

		$this->deleteTempOption();

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-account-connected=1' ) );
	}

	/**
	 * Save seller rest API credentials
	 *
	 * @param array $payPalAccounts
	 * @param string $partnerMerchantId
	 * @return void
	 * @since 2.8.0
	 */
	private function saveSellerRestAPICredentials( &$payPalAccounts, $partnerMerchantId ) {
		$tokenInfo = get_option( OptionId::$accessTokenOptionKey, [ 'access_token' => '' ] );

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

		$payPalAccounts[ $partnerMerchantId ][ $this->mode ] = json_decode( $payPalResponse, true );
	}

	/**
	 * Delete temp data
	 *
	 * @return void
	 * @since 2.8.0
	 */
	private function deleteTempOption() {
		delete_option( OptionId::$partnerInfoOptionKey );
		delete_option( OptionId::$accessTokenOptionKey );
	}

	/**
	 * Show notice if account connect success fully.
	 *
	 * @since 2.8.0
	 */
	public function showNotice() {
		if (
			! isset( $_GET['paypal-account-connected'] ) ||
			! Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' )
		) {
			return;
		}

		Give_Admin_Settings::add_message( 'paypal-account-connected', esc_html__( 'PayPal account connected successfully.', 'give' ) );
	}

	/**
	 * Return whether or not PayPal user redirect to GiveWP setting page after successful onboarding.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	private function isPayPalUserRedirected() {
		return isset( $_GET['merchantIdInPayPal'] ) && Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' );
	}
}
