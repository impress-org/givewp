<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\ArrayDataSet;
use Give\ConnectClient\ConnectClient;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;

/**
 * Class AjaxRequestHandler
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @sicne 2.8.0
 */
class AjaxRequestHandler {
	/**
	 * @var Webhooks
	 */
	private $webhooksRepository;

	/**
	 * @var MerchantDetail
	 */
	private $merchantDetails;

	/**
	 * @var PayPalClient
	 */
	private $paypalClient;

	/**
	 * @var ConnectClient
	 */
	private $connectClient;

	public function __construct(
		Webhooks $webhooksRepository,
		MerchantDetail $merchantDetails,
		PayPalClient $paypalClient,
		ConnectClient $connectClient
	) {
		$this->webhooksRepository = $webhooksRepository;
		$this->merchantDetails    = $merchantDetails;
		$this->paypalClient       = $paypalClient;
		$this->connectClient      = $connectClient;
	}

	/**
	 *  give_paypal_commerce_user_onboarded ajax action handler
	 *
	 * @since 2.8.0
	 */
	public function onBoardedUserAjaxRequestHandler() {
		$this->validateAdminRequest();

		$partnerLinkInfo = get_option( OptionId::$partnerInfoOptionKey, [ 'nonce' => '' ] );

		$payPalResponse = wp_remote_retrieve_body(
			wp_remote_post(
				$this->paypalClient->getApiUrl( 'v1/oauth2/token' ),
				[
					'headers' => [
						'Authorization' => sprintf(
							'Basic %1$s',
							base64_encode( give_clean( $_GET['sharedId'] ) )
						),
						'Content-Type'  => 'application/x-www-form-urlencoded',
					],
					'body'    => [
						'grant_type'    => 'authorization_code',
						'code'          => give_clean( $_GET['authCode'] ),
						'code_verifier' => $partnerLinkInfo['nonce'], // Seller nonce.
					],
				]
			)
		);

		if ( ! $payPalResponse ) {
			wp_send_json_error();
		}

		$payPalResponse = ArrayDataSet::camelCaseKeys( json_decode( $payPalResponse, true ) );

		update_option( OptionId::$accessTokenOptionKey, $payPalResponse );

		give( RefreshToken::class )->registerCronJobToRefreshToken( $payPalResponse['expiresIn'] );

		wp_send_json_success();
	}

	/**
	 * give_paypal_commerce_get_partner_url action handler
	 *
	 * @since 2.8.0
	 */
	public function onGetPartnerUrlAjaxRequestHandler() {
		$this->validateAdminRequest();

		$response = wp_remote_retrieve_body(
			wp_remote_post(
				sprintf(
					$this->connectClient->getApiUrl( 'paypal?mode=%1$s&request=partner-link' ),
					$this->paypalClient->mode
				),
				[
					'body' => [
						'return_url' => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce' ),
					],
				]
			)
		);

		if ( ! $response ) {
			wp_send_json_error();
		}

		$data = json_decode( $response, true );
		update_option( OptionId::$partnerInfoOptionKey, $data );

		wp_send_json_success( $data );
	}

	/**
	 * give_paypal_commerce_disconnect_account ajax request handler.
	 *
	 * @since 2.8.0
	 */
	public function removePayPalAccount() {
		$this->validateAdminRequest();

		// Remove the webhook from PayPal if there is one
		if ( $webhookId = $this->webhooksRepository->getWebhookId() ) {
			$this->webhooksRepository->deleteWebhook( $this->merchantDetails->accessToken, $webhookId );
			$this->webhooksRepository->deleteWebhookId();
		}

		MerchantDetails::delete();

		wp_send_json_success();
	}

	/**
	 * Create order.
	 *
	 * @todo: handle payment create error on frontend.
	 *
	 * @since 2.8.0
	 */
	public function createOrder() {
		$this->validateFrontendRequest();

		$postData = give_clean( $_POST );
		$formId   = absint( $postData['give-form-id'] );

		$data = [
			'formId'         => $formId,
			'donationAmount' => $postData['give-amount'],
			'payer'          => [
				'firstName' => $postData['give_first'],
				'lastName'  => $postData['give_last'],
				'email'     => $postData['give_email'],
			],
		];

		try {
			$result = give( PayPalOrder::class )->createOrder( $data );

			wp_send_json_success(
				[
					'id' => $result,
				]
			);
		} catch ( \Exception $ex ) {
			wp_send_json_error(
				[
					'error' => json_decode( $ex->getMessage(), true ),
				]
			);
		}
	}

	/**
	 * Approve order.
	 *
	 * @todo: handle payment capture error on frontend.
	 *
	 * @since 2.8.0
	 */
	public function approveOrder() {
		$this->validateFrontendRequest();

		$orderId = give_clean( $_GET['order'] );

		try {
			$result = give( PayPalOrder::class )->approveOrder( $orderId );
			wp_send_json_success(
				[
					'order' => $result,
				]
			);
		} catch ( \Exception $ex ) {
			wp_send_json_error(
				[
					'error' => json_decode( $ex->getMessage(), true ),
				]
			);
		}
	}

	/**
	 * Validate admin ajax request.
	 *
	 * @since 2.8.0
	 */
	private function validateAdminRequest() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			wp_die();
		}
	}

	/**
	 * Validate frontend ajax request.
	 *
	 * @since 2.8.0
	 */
	private function validateFrontendRequest() {
		$formId = absint( $_POST['give-form-id'] );

		if ( ! $formId || ! give_verify_donation_form_nonce( give_clean( $_POST['give-form-hash'] ), $formId ) ) {
			wp_die();
		}
	}
}
