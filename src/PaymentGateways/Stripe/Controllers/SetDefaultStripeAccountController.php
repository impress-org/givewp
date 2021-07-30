<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\SetDefaultStripeAccountDto;

/**
 * Class SetDefaultStripeAccountController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @unreleased
 */
class SetDefaultStripeAccountController {
	public function __invoke() {
		$this->validateRequest();

		$requestData = SetDefaultStripeAccountDto::fromArray( give_clean( $_POST ) );

		try {
			if ( $requestData->formId ) {
				give()->form_meta->update_meta(
					$requestData->formId,
					'_give_stripe_default_account',
					$requestData->accountSlug
				);

				wp_send_json_success();
			}

			give_update_option( '_give_stripe_default_account', $requestData->accountSlug );
			wp_send_json_success();
		} catch ( \Exception $e ) {
			wp_send_json_error(
				[
					'error' => $e->getMessage(),
				]
			);
		}
	}

	/**
	 * @unreleased
	 */
	private function validateRequest() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			die();
		}
	}
}
