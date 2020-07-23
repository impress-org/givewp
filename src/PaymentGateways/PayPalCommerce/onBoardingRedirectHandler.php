<?php
namespace Give\PaymentGateways\PayPalCommerce;

use Give\ConnectClient\ConnectClient;
use Give_Admin_Settings;

/**
 * Class PayPalOnBoardingRedirectHandler
 * @package Give\PaymentGateways\PayPalCommerce
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
		$mode          = give( PayPalClient::class )->mode;
		$tokenInfo     = get_option( OptionId::$accessTokenOptionKey, [ 'accessToken' => '' ] );

		$allowedPayPalData = [
			'merchantId',
			'merchantIdInPayPal',
		];

		$payPalAccount      = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );
		$restApiCredentials = (array) $this->getSellerRestAPICredentials( $tokenInfo['accessToken'] );

		$this->didWeGetValidSellerRestApiCredentials( $restApiCredentials );
		$this->isAdminSuccessfullyObBoarded( $payPalAccount['merchantIdInPayPal'], $tokenInfo['accessToken'] );

		$payPalAccount[ $mode ]['clientId']     = $restApiCredentials['client_id'];
		$payPalAccount[ $mode ]['clientSecret'] = $restApiCredentials['client_secret'];
		$payPalAccount[ $mode ]['token']        = $tokenInfo;

		/* @var MerchantDetail $merchantDetails */
		$merchantDetails = give( MerchantDetail::class )->fromArray( $payPalAccount );
		$merchantDetails->save();

		$this->deleteTempOptions();

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-account-connected=1' ) );
		exit;
	}

	/**
	 * Get seller rest API credentials
	 *
	 * @param string $accessToken
	 *
	 * @since 2.8.0
	 *
	 * @return array
	 */
	private function getSellerRestAPICredentials( $accessToken ) {
		$request = wp_remote_post(
			give( ConnectClient::class )->getApiUrl(
				sprintf(
					'paypal?mode=%1$s&request=seller-credentials',
					give( PayPalClient::class )->mode
				)
			),
			[
				'body' => [
					'token' => $accessToken,
				],
			]
		);

		return json_decode( wp_remote_retrieve_body( $request ), true );
	}

	/**
	 * Get seller onboarding details from seller.
	 *
	 * @param string $merchantId
	 * @param string $accessToken
	 *
	 * @return array
	 * @since 2.8.0
	 *
	 */
	private function getSellerOnBoardingDetailsFromPayPal( $merchantId, $accessToken ) {
		$request = wp_remote_post(
			give( ConnectClient::class )->getApiUrl(
				sprintf(
					'paypal?mode=%1$s&request=seller-status',
					give( PayPalClient::class )->mode
				)
			),
			[
				'body' => [
					'merchant_id' => $merchantId,
					'token'       => $accessToken,
				],
			]
		);

		return json_decode( wp_remote_retrieve_body( $request ), true );
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
	 * @since 2.8.0
	 *
	 */
	private function didWeGetValidSellerRestApiCredentials( $array ) {

		$required = [ 'client_id', 'client_secret' ];
		$array    = array_filter( $array ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			$errorMessage = isset( $restApiCredentials['error_description'] ) ? urlencode( $restApiCredentials['error_description'] ) : '';
			$this->redirectWhenOnBoardingFail( $errorMessage );
		}
	}

	/**
	 * Validate seller on Boarding status
	 *
	 * @param string $merchantId
	 * @param string $accessToken
	 *
	 * @since 2.8.0
	 *
	 */
	private function isAdminSuccessfullyObBoarded( $merchantId, $accessToken ) {
		$onBoardedData = (array) $this->getSellerOnBoardingDetailsFromPayPal( $merchantId, $accessToken );
		$required      = [ 'payments_receivable', 'primary_email_confirmed' ];
		$onBoardedData = array_filter( $onBoardedData ); // Remove empty values.

		if ( array_diff( $required, array_keys( $onBoardedData ) ) ) {
			$this->redirectWhenOnBoardingFail();
		} elseif ( ! $onBoardedData['payments_receivable'] ) {
			$this->redirectWhenOnBoardingFail( esc_html__( 'We are unable to connect your seller account because your seller account can not receive payments. Please contact PayPal Support Team', 'give' ) );
		} elseif ( ! $onBoardedData['primary_email_confirmed'] ) {
			$this->redirectWhenOnBoardingFail( esc_html__( 'We are unable to connect your seller account because your seller account primary email has not been confirmed. Please contact PayPal Support Team', 'give' ) );
		}
	}

	/**
	 * Redirect admin to setting section with error.
	 *
	 * @param $errorMessage
	 *
	 * @since 2.8.0
	 */
	private function redirectWhenOnBoardingFail( $errorMessage = '' ) {
		$errorMessage = $errorMessage ?: esc_html__( 'We are unable to connect your seller account because required result did not return from PayPal. Please contact GiveWP Support Team', 'give' );

		wp_redirect(
			admin_url(
				sprintf(
					'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-error=%1$s',
					urlencode( $errorMessage )
				)
			)
		);

		exit();
	}
}
