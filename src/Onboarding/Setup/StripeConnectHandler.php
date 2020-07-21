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

	public static function maybeHandle() {
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

		$handler = new self( $get_vars );
		$handler->handle();
	}

	public function __construct( $vars ) {
		$this->stripe_accounts = give_stripe_get_all_accounts();
		$this->account_details = give_stripe_get_account_details( $vars['stripe_user_id'] );
		$this->access_token    = ! give_is_test_mode()
			? $vars['stripe_access_token']
			: $vars['stripe_access_token_test'];

		$this->liveSecretKey      = $vars['stripe_access_token'];
		$this->testSecretKey      = $vars['stripe_access_token_test'];
		$this->livePublishableKey = $vars['stripe_publishable_key'];
		$this->testPublishableKey = $vars['stripe_publishable_key_test'];
	}


	public function handle() {

		// Set API Key to fetch account details.
		\Stripe\Stripe::setApiKey( $this->access_token );

		// Setup Account Details for Connected Stripe Accounts.
		if ( empty( $this->account_details->id ) ) {
			$this->redirectToSetupPage( [ 'give_setup_stripe_error' => __( 'We are unable to connect Stripe account. Please contact support team for assistance', 'give' ) ] );
		}

		$account_name = ! empty( $this->account_details->business_profile->name ) ?
			$this->account_details->business_profile->name :
			$this->account_details->settings->dashboard->display_name;

		// Set first Stripe account as default.
		if ( ! $this->stripe_accounts ) {
			give_update_option( '_give_stripe_default_account', $this->account_details->id );
		}

		$this->stripe_accounts[ $account_slug ] = [
			'type'                 => 'connect',
			'account_name'         => $account_name,
			'account_slug'         => $this->account_details->id,
			'account_email'        => $this->account_details->email,
			'account_country'      => $this->account_details->country,
			'account_id'           => $this->account_details->id,
			'live_secret_key'      => $this->liveSecretKey,
			'test_secret_key'      => $this->testSecretKey,
			'live_publishable_key' => $this->livePublishableKey,
			'test_publishable_key' => $this->testPublishableKey,
		];

		// Update Stripe accounts to global settings.
		give_update_option( '_give_stripe_get_all_accounts', $this->stripe_accounts );

		$this->redirectToSetupPage( [ 'give_setup_stripe_connect' => 'connected' ] );
	}

	protected function redirectToSetupPage( $args ) {
		wp_redirect(
			add_query_arg( $args, admin_url( 'edit.php?post_type=give_forms&page=give-setup' ) )
		);
		die();
	}
}
