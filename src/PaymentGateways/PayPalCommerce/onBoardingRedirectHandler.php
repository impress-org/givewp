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
	 * Bootstrap class
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		if ( $this->isPayPalUserRedirected() ) {
			$this->savePayPalMerchantDetails();
		}

		if ( $this->isPayPalAccountDetailsSaved() ) {
			$this->registerPayPalAccountConnectedNotice();
		}

		if ( $this->isPayPalError() ) {
			$this->registerPayPalErrorNotice();
		}
	}

	/**
	 * Save PayPal merchant details
	 * @todo: Confirm `primary_email_confirmed` set to true via PayPal api to confirm onboarding process status.
	 *
	 * @return void
	 * @since 2.8.0
	 */
	private function savePayPalMerchantDetails() {
		$paypalGetData = wp_parse_args( $_SERVER['QUERY_STRING'] );
		$mode          = give()->make( PayPalClient::class )->mode;

		$allowedPayPalData = [
			'merchantId',
			'merchantIdInPayPal',
		];

		$payPalAccount = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );

		$restApiCredentials = $this->getSellerRestAPICredentials( $payPalAccount['merchantIdInPayPal'] );

		if ( ! $this->validateRestApiCredentials( $restApiCredentials ) ) {
			wp_redirect(
				admin_url(
					sprintf(
						'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-error=%1$s',
						urlencode( $restApiCredentials['message'] )
					)
				)
			);
			exit;
		}

		$payPalAccount[ $mode ]['clientId']     = $restApiCredentials['client_id'];
		$payPalAccount[ $mode ]['clientSecret'] = $restApiCredentials['client_secret'];

		/* @var MerchantDetail $merchantDetails */
		$merchantDetails = give()->make( MerchantDetail::class )->fromArray( $payPalAccount );
		$merchantDetails->save();

		$this->deleteTempOptions();

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-account-connected=1' ) );
		exit;
	}

	/**
	 * Get seller rest API credentials
	 *
	 * @param string $merchantId
	 *
	 * @since 2.8.0
	 *
	 * @return array
	 */
	private function getSellerRestAPICredentials( $merchantId ) {
		$tokenInfo = get_option( OptionId::$accessTokenOptionKey, [ 'access_token' => '' ] );

		$payPalResponse = wp_remote_retrieve_body(
			wp_remote_get(
				sprintf(
					'https://api.sandbox.paypal.com/v1/customer/partners/%1$s/merchant-integrations/credentials/',
					$merchantId
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

		return json_decode( $payPalResponse, true );
	}

	/**
	 * Delete temp data
	 *
	 * @return void
	 * @since 2.8.0
	 */
	private function deleteTempOptions() {
		delete_option( OptionId::$partnerInfoOptionKey );
		delete_option( OptionId::$accessTokenOptionKey );
	}

	/**
	 * Register notice if account connect success fully.
	 *
	 * @since 2.8.0
	 */
	private function registerPayPalAccountConnectedNotice() {
		Give_Admin_Settings::add_message( 'paypal-account-connected', esc_html__( 'PayPal account connected successfully.', 'give' ) );
	}

	/**
	 * Register notice if Paypal error set in url.
	 *
	 * @since 2.8.0
	 */
	private function registerPayPalErrorNotice() {
		Give_Admin_Settings::add_error( 'paypal-error', give_clean( $_GET['paypal-error'] ) );
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

	/**
	 * Return whether or not PayPal account details saved.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	private function isPayPalAccountDetailsSaved() {
		return isset( $_GET['paypal-account-connected'] ) && Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' );
	}

	/**
	 * Return whether or not PayPal account details saved.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	private function isPayPalError() {
		return isset( $_GET['paypal-error'] ) && Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' );
	}


	/**
	 * validate rest api credential.
	 *
	 * @param  array  $array
	 *
	 * @return bool
	 * @since 2.8.0
	 *
	 */
	private function validateRestApiCredentials( $array ) {

		$required = [ 'client_id', 'client_secret' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			return false;
		}

		return true;
	}
}
