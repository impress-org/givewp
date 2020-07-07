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

	}

	/**
	 * give_paypal_checkout_get_partner_url action handler
	 *
	 * @since 2.8.0
	 */
	public function onGetPartnerUrlAjaxRequestHandler() {
		$restApiUrl = sprintf(
			'http://connect.givewp.com/paypal?mode=%1$s&return_url=%2$s',
			give_is_test_mode() ? 'sandbox' : 'live',
			admin_url( 'http://give.test/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal&group=paypal-checkout' )
		);

		$response = wp_remote_get( $restApiUrl );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error();
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		wp_send_json_success( $data );
	}
}
