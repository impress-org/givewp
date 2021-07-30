<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\NewStripeAccountOnBoardingDto;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use Give_Admin_Settings;
use Stripe\Stripe;

/**
 * Class NewStripeAccountOnBoardingController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @unreleased
 */
class NewStripeAccountOnBoardingController {
	/**
	 * @unreleased
	 */
	public function __invoke() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		$requestedData = NewStripeAccountOnBoardingDto::fromArray( give_clean( $_GET ) );

		if ( ! $requestedData->hasValidateData() ) {
			return;
		}

		$stripe_accounts = give_stripe_get_all_accounts();
		$secret_key      = ! give_is_test_mode() ? $requestedData->stripeAccessToken : $requestedData->stripeAccessTokenTest;

		Stripe::setApiKey( $secret_key );

		// Get Account Details.
		$account_details = give_stripe_get_account_details( $requestedData->stripeUserId );

		// Setup Account Details for Connected Stripe Accounts.
		if ( empty( $account_details->id ) ) {
			Give_Admin_Settings::add_error(
				'give-stripe-account-id-fetching-error',
				sprintf(
					'<strong>%1$s</strong> %2$s',
					esc_html__( 'Stripe Error:', 'give' ),
					esc_html__( 'We are unable to connect Stripe account. Please contact support team for assistance', 'give' )
				)
			);
			return;
		}

		$account_name    = ! empty( $account_details->business_profile->name ) ?
			$account_details->business_profile->name :
			$account_details->settings->dashboard->display_name;
		$account_slug    = $account_details->id;
		$account_email   = $account_details->email;
		$account_country = $account_details->country;

		// Set first Stripe account as default.
		if ( ! $stripe_accounts ) {
			give_update_option( '_give_stripe_default_account', $account_slug );
		}

		try {
			give( Settings::class )->addNewStripeAccount(
				AccountDetail::fromArray(
					[
						'type'                 => 'connect',
						'account_name'         => $account_name,
						'account_slug'         => $account_slug,
						'account_email'        => $account_email,
						'account_country'      => $account_country,
						'account_id'           => $requestedData->stripeUserId,
						'live_secret_key'      => $requestedData->stripeAccessToken,
						'test_secret_key'      => $requestedData->stripeAccessTokenTest,
						'live_publishable_key' => $requestedData->stripePublishableKey,
						'test_publishable_key' => $requestedData->stripePublishableKeyTest,
					]
				)
			);

			give_stripe_get_back_to_settings_page( [ 'stripe_account' => 'connected' ] );
		} catch ( \Exception $e ) {
			Give_Admin_Settings::add_error(
				'give-stripe-account-on-boarding-error',
				sprintf(
					'<strong>%1$s</strong> %2$s',
					esc_html__( 'Stripe Error:', 'give' ),
					esc_html__( 'We are unable to connect Stripe account. Please contact support team for assistance', 'give' )
				)
			);

			return;
		}
	}
}
