<?php

namespace Give\PaymentGateways\PayPalCheckout;

/**
 * Class AjaxRequestHandler
 * @package Give\PaymentGateways\PayPalCheckout
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
		add_action( 'wp_ajax_give_paypal_checkout_user_on_boarded', [ $this, 'onBoardedUserAjaxRequestHandler' ] );
		add_action( 'wp_ajax_give_paypal_checkout_get_partner_url', [ $this, 'onGetPartnerUrlAjaxRequestHandler' ] );
	}

	/**
	 *  give_paypal_checkout_user_onboarded ajax action handler
	 *
	 * @since 2.8.0
	 */
	public function onBoardedUserAjaxRequestHandler() {
		$partnerLinkInfo = get_option( 'give_paypal_checkout_partner_link', [ 'nonce' => '' ] );

		$payPalResponse = wp_remote_retrieve_body(
			wp_remote_post(
				'https://api.sandbox.paypal.com/v1/oauth2/token',
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

		$payPalResponse = json_decode( $payPalResponse, true );

		update_option( 'give_paypal_checkout_seller_access_token', $payPalResponse );

		wp_send_json_success();
	}

	/**
	 * give_paypal_checkout_get_partner_url action handler
	 *
	 * @since 2.8.0
	 */
	public function onGetPartnerUrlAjaxRequestHandler() {
		$restApiUrl = sprintf(
			'https://connect.givewp.com/paypal?mode=%1$s&return_url=%2$s',
			give_is_test_mode() ? 'sandbox' : 'live',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-checkout' )
		);

		$response = wp_remote_get( $restApiUrl );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		update_option( 'give_paypal_checkout_partner_link', $data );

		wp_send_json_success( $data );
	}
}
