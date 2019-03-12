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

/**
 * Disconnects user from the Give Stripe Connected App.
 */
function give_stripe_connect_deauthorize() {

	$get_vars = give_clean( $_GET ); // WPCS: input var ok.

	// Be sure only to deauthorize when param present.
	if ( ! isset( $get_vars['stripe_disconnected'] ) ) {
		return false;
	}

	// Show message if NOT disconnected.
	if (
		'false' === $get_vars['stripe_disconnected']
		&& isset( $get_vars['error_code'] )
	) {

		$class   = 'notice notice-warning give-stripe-disconnect-message';
		$message = sprintf(
			/* translators: %s Error Message */
			__( '<strong>Error:</strong> Give could not disconnect from the Stripe API. Reason: %s', 'give' ),
			esc_html( $get_vars['error_message'] )
		);

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );

	}

	// If user disconnects, remove the options regardless.
	// They can always click reconnect even if connected.
	give_stripe_connect_delete_options();

}
add_action( 'admin_notices', 'give_stripe_connect_deauthorize' );

/**
 * This function will display field to opt for refund in Stripe.
 *
 * @param int $donation_id Donation ID.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_opt_refund( $donation_id ) {

	$processed_gateway = Give()->payment_meta->get_meta( $donation_id, '_give_payment_gateway', true );

	// Bail out, if the donation is not processed with Stripe payment gateway.
	if ( 'stripe' !== $processed_gateway ) {
		return;
	}
	?>
	<div id="give-stripe-opt-refund-wrap" class="give-stripe-opt-refund give-admin-box-inside give-hidden">
		<p>
			<input type="checkbox" id="give-stripe-opt-refund" name="give_stripe_opt_refund" value="1"/>
			<label for="give-stripe-opt-refund">
				<?php esc_html_e( 'Refund Charge in Stripe?', 'give' ); ?>
			</label>
		</p>
	</div>

	<?php
}

add_action( 'give_view_donation_details_totals_after', 'give_stripe_opt_refund', 10, 1 );

/**
 * Process refund in Stripe.
 *
 * @since  2.5.0
 * @access public
 *
 * @param int    $donation_id Donation ID.
 * @param string $new_status  New Donation Status.
 * @param string $old_status  Old Donation Status.
 *
 * @return void
 */
function give_stripe_process_refund( $donation_id, $new_status, $old_status ) {

	// Only move forward if refund requested.
	$can_process_refund = ! empty( $_POST['give_stripe_opt_refund'] ) ? give_clean( $_POST['give_stripe_opt_refund'] ) : false;
	if ( ! $can_process_refund ) {
		return;
	}

	// Verify statuses.
	$should_process_refund = 'publish' !== $old_status ? false : true;
	$should_process_refund = apply_filters( 'give_stripe_should_process_refund', $should_process_refund, $donation_id, $new_status, $old_status );

	if ( false === $should_process_refund ) {
		return;
	}

	if ( 'refunded' !== $new_status ) {
		return;
	}

	$charge_id = give_get_payment_transaction_id( $donation_id );

	// If no charge ID, look in the payment notes.
	if ( empty( $charge_id ) || $charge_id == $donation_id ) {
		$charge_id = give_stripe_get_payment_txn_id_fallback( $donation_id );
	}

	// Bail if no charge ID was found.
	if ( empty( $charge_id ) ) {
		return;
	}

	try {

		$refund = \Stripe\Refund::create( array(
			'charge' => $charge_id,
		) );

		if ( isset( $refund->id ) ) {
			give_insert_payment_note(
				$donation_id,
				sprintf(
					/* translators: 1. Refund ID */
					esc_html__( 'Charge refunded in Stripe: %s', 'give' ),
					$refund->id
				)
			);
		}
	} catch ( \Stripe\Error\Base $e ) {
		// Refund issue occurred.
		$log_message = __( 'The Stripe payment gateway returned an error while refunding a donation.', 'give' ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Message: %s', 'give' ), $e->getMessage() ) . '<br><br>';
		$log_message .= sprintf( esc_html__( 'Code: %s', 'give' ), $e->getCode() );

		// Log it with DB.
		give_record_gateway_error( __( 'Stripe Error', 'give' ), $log_message );

	} catch ( Exception $e ) {

		// some sort of other error.
		$body = $e->getJsonBody();
		$err  = $body['error'];

		if ( isset( $err['message'] ) ) {
			$error = $err['message'];
		} else {
			$error = esc_html__( 'Something went wrong while refunding the charge in Stripe.', 'give' );
		}

		wp_die( $error, esc_html__( 'Error', 'give' ), array(
			'response' => 400,
		) );

	} // End try().

	do_action( 'give_stripe_donation_refunded', $donation_id );

}

add_action( 'give_update_payment_status', 'give_stripe_process_refund', 200, 3 );
