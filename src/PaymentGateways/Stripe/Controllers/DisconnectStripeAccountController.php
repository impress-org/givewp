<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\DisconnectStripeAccountDto;

/**
 * Class DisconnectStripeAccountController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @unreleased
 */
class DisconnectStripeAccountController {

	/**
	 * @unreleased
	 */
	public function __invoke() {
		$this->validateRequest();

		$requestedData = DisconnectStripeAccountDto::fromArray( give_clean( $_GET ) );

		$this->securityCheck( $requestedData->accountSlug );

		give_stripe_disconnect_account( $requestedData->accountSlug );

		wp_send_json_success();
	}

	/**
	 * @unreleased
	 */
	private function validateRequest() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			die();
		}
	}

	/**
	 * @unreleased
	 *
	 * @param $accountSlug
	 */
	private  function securityCheck( $accountSlug ) {
		if ( ! check_admin_referer( 'give_disconnect_connected_stripe_account_' . $accountSlug ) ) {
			die();
		}
	}
}
