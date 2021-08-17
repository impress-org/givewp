<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\SetDefaultStripeAccountDto;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * Class SetDefaultStripeAccountController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @unreleased
 */
class SetDefaultStripeAccountController {
	/**
	 * @var AccountDetail
	 */
	private $accountDetailsRepository;
	/**
	 * @var Settings
	 */
	private $settingsRepository;

	/**
	 * @param AccountDetail $accountDetailsRepository
	 * @param Settings $settingsRepository
	 */
	public function __construct( AccountDetail $accountDetailsRepository, Settings $settingsRepository ) {
		$this->accountDetailsRepository = $accountDetailsRepository;
		$this->settingsRepository = $settingsRepository;
	}

	/**
	 * @unreleased
	 */
	public function __invoke() {
		$this->validateRequest();

		$requestData = SetDefaultStripeAccountDto::fromArray( give_clean( $_POST ) );

		try {
			if ( $requestData->formId ) {
				$this->accountDetailsRepository
					->setDefaultStripeAccountSlugForDonationForm(
					$requestData->formId,
					$requestData->accountSlug
				);

				wp_send_json_success();
			}

			$this->settingsRepository->setDefaultGlobalStripeAccountSlug( $requestData->accountSlug );
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
