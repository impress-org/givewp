<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\ArrayDataSet;
use PayPalCheckoutSdk\Core\PayPalHttpClient;

/**
 * Class AjaxRequestHandler
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @sicne 2.8.0
 */
class AjaxRequestHandler {
	/**
	 * Setup hooks
	 *
	 * @since 2.8.0
	 */
	public function boot() {
		add_action( 'wp_ajax_give_paypal_commerce_user_on_boarded', [ $this, 'onBoardedUserAjaxRequestHandler' ] );
		add_action( 'wp_ajax_give_paypal_commerce_get_partner_url', [ $this, 'onGetPartnerUrlAjaxRequestHandler' ] );
		add_action( 'wp_ajax_give_paypal_commerce_disconnect_account', [ $this, 'removePayPalAccount' ] );
	}

	/**
	 *  give_paypal_commerce_user_onboarded ajax action handler
	 *
	 * @since 2.8.0
	 */
	public function onBoardedUserAjaxRequestHandler() {
		$this->sendErrorOnAjaxRequestIfUserDoesNotHasPermission();

		$partnerLinkInfo = get_option( OptionId::$partnerInfoOptionKey, [ 'nonce' => '' ] );

		$payPalResponse = wp_remote_retrieve_body(
			wp_remote_post(
				give( PayPalClient::class )->getEnvironment()->baseUrl() . '/v1/oauth2/token',
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
		$this->sendErrorOnAjaxRequestIfUserDoesNotHasPermission();

		$restApiUrl = sprintf(
			'https://connect.givewp.com/paypal?mode=%1$s&return_url=%2$s',
			give( PayPalClient::class )->mode,
			urlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-commerce' ) )
		);

		$response = wp_remote_get( $restApiUrl );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		update_option( OptionId::$partnerInfoOptionKey, $data );

		wp_send_json_success( $data );
	}

	/**
	 * give_paypal_commerce_disconnect_account ajax request handler.
	 *
	 * @since 2.8.0
	 */
	public function removePayPalAccount() {
		$this->sendErrorOnAjaxRequestIfUserDoesNotHasPermission();

		give( MerchantDetail::class )->delete();

		wp_send_json_success();
	}

	/**
	 * Send error if user does not has capability to manage GiveWP settings.
	 *
	 * @since 2.8.0
	 */
	private function sendErrorOnAjaxRequestIfUserDoesNotHasPermission() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			wp_send_json_error();
		}
	}
}
