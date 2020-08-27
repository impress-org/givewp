<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give_Admin_Settings;

class AccountAdminNotices {
	/**
	 * @since 2.9.0
	 *
	 * @var MerchantDetails
	 */
	private $merchantRepository;

	/**
	 * AccountAdminNotices constructor.
	 *
	 * @param MerchantDetails $merchantRepository
	 */
	public function __construct( MerchantDetails $merchantRepository ) {
		$this->merchantRepository = $merchantRepository;
	}

	/**
	 * Displays the admin notices in the right conditions
	 *
	 * @since 2.9.0
	 */
	public function displayNotices() {
		if ( Utils::gatewayIsActive() && ! give_is_test_mode() ) {
			$this->checkForConnectedLiveAccount();
			$this->checkForAccountReadiness();
		}
	}

	/**
	 * Displays a notice if the account is not connected
	 *
	 * @since 2.9.0
	 */
	public function checkForConnectedLiveAccount() {
		if ( ! $this->merchantRepository->accountIsConnected() ) {
			$connectUrl = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal' );
			Give_Admin_Settings::add_message(
				'paypal-commerce-not-connected',
				sprintf(
					"<strong>%1\$s</strong> %2\$s <a href='{$connectUrl}'>%3\$s</a>",
					esc_html__( 'PayPal Donations:', 'give' ),
					esc_html__( 'Please connect to your account so donations may be processed.', 'give' ),
					esc_html__( 'Connect Account', 'give' )
				)
			);
		}
	}

	/**
	 * Displays a notice if the account is connected but not ready
	 *
	 * @since 2.9.0
	 */
	public function checkForAccountReadiness() {
		if ( ! $this->merchantRepository->accountIsConnected() ) {
			return;
		}

		$merchantDetails = $this->merchantRepository->getDetails();
		if ( $merchantDetails->accountIsReady ) {
			return;
		}

		$connectUrl = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal' );
		Give_Admin_Settings::add_message(
			'paypal-commerce-account-not-ready',
			sprintf(
				"<strong>%1\$s</strong> %2\$s <a href='{$connectUrl}'>%3\$s</a>",
				esc_html__( 'PayPal Donations:', 'give' ),
				esc_html__( 'Please check your account status as additional setup is needed before you may accept donations.', 'give' ),
				esc_html__( 'Account Status', 'give' )
			)
		);
	}
}
