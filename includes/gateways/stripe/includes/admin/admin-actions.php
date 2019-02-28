<?php
/**
 * Give - Stripe Core Admin Actions
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * This function is used as an AJAX callback to the click event of "Sync Again" button.
 *
 * @since 2.5.0
 *
 * @return void|array
 */
function give_stripe_check_webhook_status_callback() {

    // Set defaults.
    $data = array(
        'live_webhooks_setup'    => false,
        'sandbox_webhooks_setup' => false,
    );

    $give_stripe_webhook = new Give_Stripe_Webhooks();
    $webhook_id          = give_stripe_get_webhook_id();

    if ( ! empty( $webhook_id ) ) {

        // Get webhook details of an existing one.
        $webhook_details = $give_stripe_webhook->retrieve( $webhook_id );

		// Set WebHook details to DB.
        if ( ! empty( $webhook_details->id ) ) {
            $give_stripe_webhook->set_data_to_db( $webhook_details->id );
        }
    }

    // Recreate Webhook, if the details in DB mismatch with Stripe.
    if ( empty( $webhook_details->id ) ) {

        // Get webhook details after creating one.
        $webhook_details = $give_stripe_webhook->create();
    }

	if ( ! empty( $webhook_details->id ) ) {
        if ( give_is_test_mode() ) {
            $data['sandbox_webhooks_setup'] = true;
        } else {
            $data['live_webhooks_setup'] = true;
        }
    }

	wp_send_json_success( $data );

    give_die();
}
add_action( 'wp_ajax_give_stripe_check_webhook_status', 'give_stripe_check_webhook_status_callback' );

/**
 * This function is used to save the parameters returned after successfull connection of Stripe account.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_connect_save_options() {

	$get_vars = give_clean( filter_input_array( INPUT_GET ) );

	// If we don't have values here, bounce.
	if (
		! isset( $get_vars['stripe_publishable_key'] )
		|| ! isset( $get_vars['stripe_user_id'] )
		|| ! isset( $get_vars['stripe_access_token'] )
		|| ! isset( $get_vars['stripe_access_token_test'] )
		|| ! isset( $get_vars['connected'] )
	) {
		return false;
	}

	// Update keys.
	give_update_option( 'give_stripe_connected', $get_vars['connected'] );
	give_update_option( 'give_stripe_user_id', $get_vars['stripe_user_id'] );
	give_update_option( 'live_secret_key', $get_vars['stripe_access_token'] );
	give_update_option( 'test_secret_key', $get_vars['stripe_access_token_test'] );
	give_update_option( 'live_publishable_key', $get_vars['stripe_publishable_key'] );
	give_update_option( 'test_publishable_key', $get_vars['stripe_publishable_key_test'] );

	// Delete option for user API key.
	give_delete_option( 'stripe_user_api_keys' );

}
add_action( 'admin_init', 'give_stripe_connect_save_options' );
