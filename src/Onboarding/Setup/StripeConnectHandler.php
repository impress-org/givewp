<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup;

defined( 'ABSPATH' ) || exit;

/**
 * Fork of `give_stripe_connect_save_options()`
 */
class StripeConnectHandler {


	public function saveConnection() {
		// Is user have permission to edit give setting.
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		$get_vars = give_clean( $_GET );

		// If we don't have values here, bounce.
		if (
			! isset( $get_vars['stripe_publishable_key'] ) ||
			! isset( $get_vars['stripe_user_id'] ) ||
			! isset( $get_vars['stripe_access_token'] ) ||
			! isset( $get_vars['stripe_access_token_test'] ) ||
			! isset( $get_vars['connected'] )
		) {
			return;
		}

		// Unable to redirect, bail.
		if ( headers_sent() ) {
			return;
		}

		$stripe_account_id = $get_vars['stripe_user_id'];
		$stripe_accounts   = give_stripe_get_all_accounts();
		$secret_key        = ! give_is_test_mode() ? $get_vars['stripe_access_token'] : $get_vars['stripe_access_token_test'];

		// Set API Key to fetch account details.
		\Stripe\Stripe::setApiKey( $secret_key );

		// Get Account Details.
		$account_details = give_stripe_get_account_details( $stripe_account_id );

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

		$stripe_accounts[ $account_slug ] = [
			'type'                 => 'connect',
			'account_name'         => $account_name,
			'account_slug'         => $account_slug,
			'account_email'        => $account_email,
			'account_country'      => $account_country,
			'account_id'           => $stripe_account_id,
			'live_secret_key'      => $get_vars['stripe_access_token'],
			'test_secret_key'      => $get_vars['stripe_access_token_test'],
			'live_publishable_key' => $get_vars['stripe_publishable_key'],
			'test_publishable_key' => $get_vars['stripe_publishable_key_test'],
		];

		// Update Stripe accounts to global settings.
		give_update_option( '_give_stripe_get_all_accounts', $stripe_accounts );

		// Send back to settings page.
		wp_redirect(
			add_query_arg( [ 'stripe_account' => 'connected' ], admin_url( 'edit.php?post_type=give_forms&page=give-setup' ) )
		);
		die();
	}
}
