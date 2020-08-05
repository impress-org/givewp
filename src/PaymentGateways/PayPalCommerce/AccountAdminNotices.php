<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give_Admin_Settings;

class AccountAdminNotices {
	/**
	 * @since 2.8.0
	 *
	 * @var MerchantDetail
	 */
	private $merchantDetails;

	public function __construct( MerchantDetail $merchantDetails ) {
		$this->merchantDetails = $merchantDetails;
	}

	public function displayNotices() {
		$this->checkForConnectedLiveAccount();
	}

	public function checkForConnectedLiveAccount() {
		if ( Utils::gatewayIsActive() && ! give_is_test_mode() && ! Utils::isConnected() ) {
			$connectUrl = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal' );
			Give_Admin_Settings::add_message(
				'paypal-commerce-not-connected',
				"<strong>PayPal Donations: </strong> Please <a href=\"{$connectUrl}\">connect your account</a> so donations may be processed."
			);
		}
	}

	public function checkForAccountReadiness() {
		if ( Utils::gatewayIsActive() && ! give_is_test_mode() && Utils::isConnected() && ! $this->merchantDetails->accountIsReady ) {
			$connectUrl = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal' );
			Give_Admin_Settings::add_message(
				'paypal-commerce-account-not-ready',
				"<strong>PayPal Donations: </strong> Please <a href=\"{$connectUrl}\">check your account</a> as additional setup is needed before you are ready to receive donations."
			);
		}
	}
}
