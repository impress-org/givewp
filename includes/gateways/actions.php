<?php
/**
 * Gateway Actions
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes gateway select on checkout. Only for users without ajax / javascript
 *
 * @since 1.0
 *
 * @param $data
 */
function give_process_gateway_select( $data ) {
	if ( isset( $_POST['gateway_submit'] ) ) {
		wp_redirect( esc_url_raw( add_query_arg( 'payment-mode', $_POST['payment-mode'] ) ) );
		exit;
	}
}

add_action( 'give_gateway_select', 'give_process_gateway_select' );

/**
 * Loads a payment gateway via AJAX.
 *
 * @since 1.0
 *
 * @return void
 */
function give_load_ajax_gateway() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, CSRF ok.

	if (
		! isset( $post_data['nonce'] )
		|| ! give_verify_donation_form_nonce( $post_data['nonce'], $post_data['give_form_id'] )
	) {
		Give_Notices::print_frontend_notice( __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ), true, 'error' );
		exit();

	} elseif ( isset( $post_data['give_payment_mode'] ) ) {

		$form_id_prefix = ! empty( $post_data['give_form_id_prefix'] ) ? $post_data['give_form_id_prefix'] : '';

		$args = array(
			'id_prefix' => $form_id_prefix,
		);

		/**
		 * Fire to render donation form.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form', $post_data['give_form_id'], $args );

		exit();
	}
}

add_action( 'wp_ajax_give_load_gateway', 'give_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_give_load_gateway', 'give_load_ajax_gateway' );

/**
 * Create wp nonce using Ajax call.
 *
 * Use give_donation_form_nonce() js fn to create nonce.
 *
 * @since 2.0
 *
 * @return void
 */
function give_donation_form_nonce() {
	if ( isset( $_POST['give_form_id'] ) ) {

		// Get donation form id.
		$form_id = is_numeric( $_POST['give_form_id'] ) ? absint( $_POST['give_form_id'] ) : 0;

		// Send nonce json data.
		wp_send_json_success( wp_create_nonce( "give_donation_form_nonce_{$form_id}" ) );
	}
}

add_action( 'wp_ajax_give_donation_form_nonce', 'give_donation_form_nonce' );
add_action( 'wp_ajax_nopriv_give_donation_form_nonce', 'give_donation_form_nonce' );


/**
 * Create all nonce of donation form using Ajax call.
 * Note: only for internal use
 *
 * @since 2.2.0
 *
 * @return void
 */
function __give_donation_form_reset_all_nonce() {
	if ( isset( $_POST['give_form_id'] ) ) {

		// Get donation form id.
		$form_id = is_numeric( $_POST['give_form_id'] ) ? absint( $_POST['give_form_id'] ) : 0;

		$data = array(
			'give_form_hash'               => wp_create_nonce( "give_donation_form_nonce_{$form_id}" ),
			'give_form_user_register_hash' => wp_create_nonce( "give_form_create_user_nonce_{$form_id}" ),
		);

		/**
		 * Filter the ajax request data
		 *
		 * @since  2.2.0
		 */
		$data = apply_filters( 'give_donation_form_reset_all_nonce_data', $data );

		// Send nonce json data.
		wp_send_json_success( $data );
	}

	wp_send_json_error();
}

add_action( 'wp_ajax_give_donation_form_reset_all_nonce', '__give_donation_form_reset_all_nonce' );
add_action( 'wp_ajax_nopriv_give_donation_form_reset_all_nonce', '__give_donation_form_reset_all_nonce' );

/**
 * Sets an error within the donation form if no gateways are enabled.
 *
 * @todo: we can deprecate this function in future because gateways will not empty if get via Give API.
 *
 * @since 1.0
 *
 * @return void
 */
function give_no_gateway_error() {
	$gateways = give_get_enabled_payment_gateways();

	if ( empty( $gateways ) ) {
		give_set_error( 'no_gateways', esc_html__( 'You must enable a payment gateway to use Give.', 'give' ) );
	} else {
		give_unset_error( 'no_gateways' );
	}
}

add_action( 'init', 'give_no_gateway_error' );
