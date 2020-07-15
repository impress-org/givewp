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
	 * Option key name.
	 *
	 * In this option we stores partner link rest api response temporary.
	 *
	 * @var string
	 * @since 2.8.0
	 */
	public static $partnerInfoOptionKey = 'temp_give_paypal_checkout_partner_link';

	/**
	 * Option key name.
	 *
	 * In this option we stores PayPal access token details temporary.
	 *
	 * @var string
	 * @since 2.8.0
	 */
	public static $accessTokenOptionKey = 'temp_give_paypal_checkout_seller_access_token';

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

		$payPalAccounts = (array) get_option( 'give_paypal_checkout_accounts', [] );
		$paypalData     = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );

		$payPalAccounts[ $paypalData['merchantIdInPayPal'] ] = array_merge( $payPalAccounts[ $paypalData['merchantIdInPayPal'] ], $paypalData );

		$this->saveSellerRestAPICredentials( $payPalAccounts, $paypalData['merchantIdInPayPal'] );

		update_option( 'give_paypal_checkout_accounts', $payPalAccounts );

		$this->deleteTempOption();
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
		$tokenInfo = get_option( self::$accessTokenOptionKey, [ 'access_token' => '' ] );

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
		$payPalAccounts[ $this->mode ]['partnerDetails'] = get_option( self::$partnerInfoOptionKey );
	}

	/**
	 * Delete temp data
	 *
	 * @return void
	 * @since 2.8.0
	 */
	private function deleteTempOption() {
		delete_option( self::$partnerInfoOptionKey );
		delete_option( self::$accessTokenOptionKey );
	}
}
