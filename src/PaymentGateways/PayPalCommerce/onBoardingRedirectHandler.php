<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\ConnectClient\ConnectClient;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give_Admin_Settings;

/**
 * Class PayPalOnBoardingRedirectHandler
 * @since 2.8.0
 * @package Give\PaymentGateways\PayPalCommerce
 *
 */
class onBoardingRedirectHandler {
	/**
	 * @var PayPalClient
	 */
	private $payPalClient;

	/**
	 * @var Webhooks
	 */
	private $webhooksRepository;

	/**
	 * onBoardingRedirectHandler constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param Webhooks     $webhooks
	 * @param PayPalClient $payPalClient
	 */
	public function __construct( Webhooks $webhooks, PayPalClient $payPalClient ) {
		$this->webhooksRepository = $webhooks;
		$this->payPalClient       = $payPalClient;
	}

	/**
	 * Bootstrap class
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		if ( $this->isPayPalUserRedirected() ) {
			$details = $this->savePayPalMerchantDetails();
			$this->setUpWebhook( $details );
			$this->redirectAccountConnected( $details );
		}

		if ( $this->isPayPalAccountDetailsSaved() ) {
			$this->registerPayPalSSLNotice();
			$this->registerPayPalAccountConnectedNotice();
		}

		if ( $this->isPayPalError() ) {
			$this->registerPayPalErrorNotice();
		}
	}

	/**
	 * Save PayPal merchant details
	 *
	 * @todo: Confirm `primary_email_confirmed` set to true via PayPal api to confirm onboarding process status.
	 *
	 * @since 2.8.0
	 *
	 * @return MerchantDetail
	 */
	private function savePayPalMerchantDetails() {
		$paypalGetData = wp_parse_args( $_SERVER['QUERY_STRING'] );
		$mode          = $this->payPalClient->mode;
		$tokenInfo     = get_option( OptionId::$accessTokenOptionKey, [ 'accessToken' => '' ] );

		$allowedPayPalData = [
			'merchantId',
			'merchantIdInPayPal',
		];

		$payPalAccount      = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );
		$restApiCredentials = (array) $this->getSellerRestAPICredentials( $tokenInfo['accessToken'] );

		$this->didWeGetValidSellerRestApiCredentials( $restApiCredentials );

		$payPalAccount[ $mode ]['clientId']     = $restApiCredentials['client_id'];
		$payPalAccount[ $mode ]['clientSecret'] = $restApiCredentials['client_secret'];
		$payPalAccount[ $mode ]['token']        = $tokenInfo;

		$merchantDetails = MerchantDetail::fromArray( $payPalAccount );
		MerchantDetails::save( $merchantDetails );

		$this->deleteTempOptions();

		return $merchantDetails;
	}

	/**
	 * Redirects the user to the account connected url
	 *
	 * @since 2.8.0
	 *
	 * @param MerchantDetail $merchant_detail
	 */
	private function redirectAccountConnected( MerchantDetail $merchant_detail ) {
		$this->isAdminSuccessfullyOnBoarded( $merchant_detail->merchantIdInPayPal, $merchant_detail->accessToken );

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-account-connected=1' ) );
		exit;
	}

	/**
	 * Sets up the webhook for the connected account
	 *
	 * @since 2.8.0
	 *
	 * @param MerchantDetail $merchant_details
	 */
	private function setUpWebhook( MerchantDetail $merchant_details ) {
		if ( ! is_ssl() ) {
			return;
		}

		$webhookId = $this->webhooksRepository->createWebhook( $merchant_details->accessToken );

		$this->webhooksRepository->saveWebhookId( $webhookId );
	}

	/**
	 * Get seller rest API credentials
	 *
	 * @since 2.8.0
	 *
	 * @param string $accessToken
	 *
	 * @return array
	 */
	private function getSellerRestAPICredentials( $accessToken ) {
		$request = wp_remote_post(
			give( ConnectClient::class )->getApiUrl(
				sprintf(
					'paypal?mode=%1$s&request=seller-credentials',
					$this->payPalClient->mode
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
	 * @since 2.8.0
	 *
	 * @param string $accessToken
	 *
	 * @param string $merchantId
	 *
	 * @return array
	 */
	private function getSellerOnBoardingDetailsFromPayPal( $merchantId, $accessToken ) {
		$request = wp_remote_post(
			give( ConnectClient::class )->getApiUrl(
				sprintf(
					'paypal?mode=%1$s&request=seller-status',
					$this->payPalClient->mode
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
	 * @since 2.8.0
	 * @return void
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
		Give_Admin_Settings::add_error( 'paypal-error', wp_kses( $_GET['paypal-error'], [ 'br' => [] ] ) );
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
	 * @since 2.8.0
	 *
	 * @param array $array
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
	 * @since 2.8.0
	 *
	 * @param string $accessToken
	 *
	 * @param string $merchantId
	 */
	private function isAdminSuccessfullyOnBoarded( $merchantId, $accessToken ) {
		$onBoardedData = (array) $this->getSellerOnBoardingDetailsFromPayPal( $merchantId, $accessToken );
		$required      = [ 'payments_receivable', 'primary_email_confirmed' ];
		$onBoardedData = array_filter( $onBoardedData ); // Remove empty values.
		$errorMessage  = esc_html__( 'Your are successfully connected, but you need to do a few things within your PayPal account before you\'re ready to receive donations:', 'give' );
		$redirect      = false;

		if ( array_diff( $required, array_keys( $onBoardedData ) ) ) {
			$this->redirectWhenOnBoardingFail();
		}

		if ( ! $onBoardedData['payments_receivable'] ) {
			$redirect      = true;
			$errorMessage .= sprintf(
				'<br>- %1$s',
				esc_html__( 'Set up an account to receive payment from PayPal', 'give' )
			);
		}

		if ( ! $onBoardedData['primary_email_confirmed'] ) {
			$redirect      = true;
			$errorMessage .= sprintf(
				'<br>- %1$s',
				esc_html__( 'Confirm your primary email address', 'give' )
			);
		}

		if ( $redirect ) {
			$this->redirectWhenOnBoardingFail( $errorMessage );
		}
	}

	/**
	 * Redirect admin to setting section with error.
	 *
	 * @since 2.8.0
	 *
	 * @param $errorMessage
	 *
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

	/**
	 * Displays a notice of the site is not using SSL
	 *
	 * @since 2.8.0
	 */
	private function registerPayPalSSLNotice() {
		if ( ! is_ssl() ) {
			Give_Admin_Settings::add_error(
				'paypal-ssl-error',
				'There was a problem registering your site\'s webhook with PayPal. In order for to register
				the webhook your site must have a valid SSL certificate. You are connected, but your site will not
				receive donation payment events. To fix this, set up an SSL for the website, update your site URL to
				include https, and then disconnect and reconnect your PayPal account.'
			);
		} elseif ( empty( $this->webhooksRepository->getWebhookId() ) ) {
			Give_Admin_Settings::add_error(
				'paypal-webhook-error',
				'There was a problem creating a webhook for your account. Please try disconnecting and then
				reconnect. If the problem persists, please contact support'
			);
		}
	}
}
