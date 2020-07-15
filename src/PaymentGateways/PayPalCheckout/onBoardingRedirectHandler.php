<?php
namespace Give\PaymentGateways\PayPalCheckout;

use Give_Admin_Settings;

/**
 * Class PayPalOnBoardingRedirectHandler
 * @package Give\PaymentGateways\PayPalCheckout
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

		// add_action( 'give-settings_start', [ $this, 'savePayPalMerchantDetails' ] );
		add_action( 'admin_init', [ $this, 'savePayPalMerchantDetails' ] );
		add_action( 'admin_init', [ $this, 'showNotice' ] );
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

		$payPalAccounts = (array) get_option( OptionId::$payPalAccountsOptionKey, [] );
		$paypalData     = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );

		$payPalAccounts[ $paypalData['merchantIdInPayPal'] ] = array_merge( $payPalAccounts[ $paypalData['merchantIdInPayPal'] ], $paypalData );

		$this->saveSellerRestAPICredentials( $payPalAccounts, $paypalData['merchantIdInPayPal'] );

		update_option( OptionId::$payPalAccountsOptionKey, $payPalAccounts );

		$this->deleteTempOption();

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-checkout&paypal-account-connected=1' ) );
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

		$payPalAccounts[ $this->mode ]                   = json_decode( $payPalResponse, true );
		$payPalAccounts[ $this->mode ]['tokenDetails']   = $tokenInfo;
		$payPalAccounts[ $this->mode ]['partnerDetails'] = get_option( OptionId::$partnerInfoOptionKey );
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
	public function showNotice(){
		if(
			! isset( $_GET['paypal-account-connected']) ||
			! Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' )
		){
			return;
		}

		Give_Admin_Settings::add_message( 'paypal-account-connected', esc_html__( 'PayPal account connected successfully', 'give' ) );
	}
}
