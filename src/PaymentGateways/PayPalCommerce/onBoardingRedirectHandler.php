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
	 * @since 2.8.0
	 *
	 * @var PayPalClient
	 */
	private $payPalClient;

	/**
	 * @since 2.8.0
	 *
	 * @var Webhooks
	 */
	private $webhooksRepository;

	/**
	 * @since 2.8.0
	 *
	 * @var MerchantDetails
	 */
	private $merchantRepository;

	/**
	 * onBoardingRedirectHandler constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param Webhooks        $webhooks
	 * @param PayPalClient    $payPalClient
	 * @param MerchantDetails $merchantRepository
	 */
	public function __construct( Webhooks $webhooks, PayPalClient $payPalClient, MerchantDetails $merchantRepository ) {
		$this->webhooksRepository = $webhooks;
		$this->payPalClient       = $payPalClient;
		$this->merchantRepository = $merchantRepository;
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

		if ( $this->isStatusRefresh() ) {
			$this->refreshAccountStatus();
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
		$paypalGetData   = wp_parse_args( $_SERVER['QUERY_STRING'] );
		$tokenInfo       = get_option( OptionId::ACCESS_TOKEN, [ 'accessToken' => '' ] );
		$partnerLinkInfo = get_option( OptionId::PARTNER_LINK_DETAIL, null );

		$allowedPayPalData = [
			'merchantId',
			'merchantIdInPayPal',
		];

		$payPalAccount      = array_intersect_key( $paypalGetData, array_flip( $allowedPayPalData ) );
		$restApiCredentials = (array) $this->getSellerRestAPICredentials( $tokenInfo['accessToken'] );

		$this->didWeGetValidSellerRestApiCredentials( $restApiCredentials );

		$payPalAccount['clientId']               = $restApiCredentials['client_id'];
		$payPalAccount['clientSecret']           = $restApiCredentials['client_secret'];
		$payPalAccount['token']                  = $tokenInfo;
		$payPalAccount['supportsCustomPayments'] = 'PPCP' === $partnerLinkInfo['product'];
		$payPalAccount['accountIsReady']         = true;

		$merchantDetails = MerchantDetail::fromArray( $payPalAccount );
		$this->merchantRepository->save( $merchantDetails );

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
		$this->refreshAccountStatus();

		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce&paypal-account-connected=1' ) );

		exit();
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
		delete_option( OptionId::PARTNER_LINK_DETAIL );
		delete_option( OptionId::ACCESS_TOKEN );
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
	 * Returns whether or not the current request is for refreshing the account status
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	private function isStatusRefresh() {
		return isset( $_GET['paypalStatusCheck'] ) && Give_Admin_Settings::is_setting_page( 'gateways', 'paypal' );
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
	 * Handles the request for refreshing the account status
	 *
	 * @since 2.8.0
	 */
	private function refreshAccountStatus() {
		$merchantDetails = $this->merchantRepository->getDetails();

		$statusErrors = $this->isAdminSuccessfullyOnBoarded( $merchantDetails->merchantIdInPayPal, $merchantDetails->accessToken, $merchantDetails->supportsCustomPayments );
		if ( $statusErrors !== true ) {
			$merchantDetails->accountIsReady = false;
			$this->merchantRepository->saveAccountErrors( $statusErrors );

		} else {
			$merchantDetails->accountIsReady = true;
			$this->merchantRepository->deleteAccountErrors();
		}

		$this->merchantRepository->save( $merchantDetails );
	}

	/**
	 * Validate seller on Boarding status
	 *
	 * @since 2.8.0
	 *
	 * @param string $merchantId
	 * @param string $accessToken
	 * @param bool   $usesCustomPayments
	 *
	 * @return true|string[]
	 */
	private function isAdminSuccessfullyOnBoarded( $merchantId, $accessToken, $usesCustomPayments ) {
		$onBoardedData = (array) $this->getSellerOnBoardingDetailsFromPayPal( $merchantId, $accessToken );
		$onBoardedData = array_filter( $onBoardedData ); // Remove empty values.
		$errorMessages = [];

		if ( ! is_ssl() ) {
			$errorMessages[] = esc_html__(
				'A valid SSL certificate is required to accept donations and set up your PayPal account. Once a
					certificate is installed and the site is using https, please disconnect and reconnect your account.',
				'give'
			);
		}

		if ( array_diff( [ 'payments_receivable', 'primary_email_confirmed' ], array_keys( $onBoardedData ) ) ) {
			$errorMessages[] = esc_html__( 'There was a problem with the status check for your account. Please try disconnecting and connecting again. If the problem persists, please contact support.', 'give' );

			// Return here since the rest of the validations will definitely fail
			return $errorMessages;
		}

		if ( ! $onBoardedData['payments_receivable'] ) {
			$errorMessages[] = esc_html__( 'Set up an account to receive payment from PayPal', 'give' );
		}

		if ( ! $onBoardedData['primary_email_confirmed'] ) {
			$errorMessage[] = esc_html__( 'Confirm your primary email address', 'give' );
		}

		if ( ! $usesCustomPayments ) {
			return empty( $errorMessages ) ? true : $errorMessages;
		}

		if ( array_diff( [ 'products', 'capabilities' ], array_keys( $onBoardedData ) ) ) {
			$errorMessages[] = esc_html__( 'There was a problem with the status check for your account. Please try disconnecting and connecting again. If the problem persists, please contact support.', 'give' );

			// Return here since the rest of the validations will definitely fail
			return $errorMessages;
		}

		// Grab the PPCP_CUSTOM product from the status data
		$customProduct = current(
			array_filter(
				$onBoardedData['products'],
				function ( $product ) {
					return $product['name'] === 'PPCP_CUSTOM';
				}
			)
		);

		if ( empty( $customProduct ) || $customProduct['vetting_status'] !== 'SUBSCRIBED' ) {
			$errorMessages[] = esc_html__( 'Reach out to PayPal to enable PPCP_CUSTOM for your account', 'give' );
		}

		// Loop through the capabilities and see if any are not active
		$invalidCapabilities = [];
		foreach ( $onBoardedData['capabilities'] as $capability ) {
			if ( $capability['status'] !== 'ACTIVE' ) {
				$invalidCapabilities[] = $capability['name'];
			}
		}

		if ( ! empty( $invalidCapabilities ) ) {
			$errorMessages[] = esc_html__( 'Reach out to PayPal to resolve the following capabilities:', 'give' ) . ' ' . implode( ', ', $invalidCapabilities );
		}

		// If there were errors then redirect the user with notices
		return empty( $errorMessages ) ? true : $errorMessages;
	}

	/**
	 * Redirect admin to setting section with error.
	 *
	 * @since 2.8.0
	 *
	 * @param $errorMessage
	 *
	 */
	private function redirectWhenOnBoardingFail( $errorMessage ) {
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
		if ( is_ssl() && empty( $this->webhooksRepository->getWebhookId() ) ) {
			Give_Admin_Settings::add_error(
				'paypal-webhook-error',
				esc_html__(
					'There was a problem creating a webhook for your account. Please try disconnecting and then
					reconnect. If the problem persists, please contact support',
					'give'
				)
			);
		}
	}
}
