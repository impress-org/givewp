<?php
/**
 * Give Shortcodes
 *
 * @package     Give
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Purchase History Shortcode
 *
 * Displays a user's purchase history.
 *
 * @since 1.0
 * @return string
 */
function give_donation_history() {
	if ( is_user_logged_in() ) {
		ob_start();
		give_get_template_part( 'history', 'donations' );

		return ob_get_clean();
	}
}

add_shortcode( 'donation_history', 'give_donation_history' );

/**
 * Donation Form Shortcode
 *
 * Show the Give donation form.
 *
 * @since 1.0
 *
 * @param array  $atts Shortcode attributes
 * @param string $content
 *
 * @return string
 */
function give_form_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'id'         => '',
		'show_title' => true,
	), $atts, 'give_form' );

	//get the Give Form
	ob_start();
	give_get_donation_form( $atts );
	$final_output = ob_get_clean();

	return apply_filters( 'give_donate_form', $final_output, $atts );
}

add_shortcode( 'give_form', 'give_form_shortcode' );


/**
 * Login Shortcode
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the give_login_form function to display the login form.
 *
 * @since 1.0
 *
 * @param array  $atts Shortcode attributes
 * @param string $content
 *
 * @uses  give_login_form()
 * @return string
 */
function give_login_form_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'give_login' )
	);

	return give_login_form( $redirect );
}

add_shortcode( 'give_login', 'give_login_form_shortcode' );

/**
 * Register Shortcode
 *
 * Shows a registration form allowing users to users to register for the site
 *
 * @since 1.0
 *
 * @param array  $atts Shortcode attributes
 * @param string $content
 *
 * @uses  give_register_form()
 * @return string
 */
function give_register_form_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts, 'give_register' )
	);

	return give_register_form( $redirect );
}

add_shortcode( 'give_register', 'give_register_form_shortcode' );


/**
 * Receipt Shortcode
 *
 * Shows an order receipt.
 *
 * @since 1.0
 *
 * @param array  $atts Shortcode attributes
 * @param string $content
 *
 * @return string
 */
function give_receipt_shortcode( $atts, $content = null ) {

	global $give_receipt_args;

	$give_receipt_args = shortcode_atts( array(
		'error'          => __( 'Sorry, we\'re having trouble retrieving your donation receipt.', 'give' ),
		'price'          => true,
		'date'           => true,
		'notes'          => true,
		'payment_key'    => false,
		'payment_method' => true,
		'payment_id'     => true
	), $atts, 'give_receipt' );

	$session = give_get_purchase_session();

	if ( isset( $_GET['payment_key'] ) ) {
		$payment_key = urldecode( $_GET['payment_key'] );
	} elseif ( $give_receipt_args['payment_key'] ) {
		$payment_key = $give_receipt_args['payment_key'];
	} else if ( $session ) {
		$payment_key = $session['purchase_key'];
	}

	// No key found
	if ( ! isset( $payment_key ) ) {
		return $give_receipt_args['error'];
	}

	$give_receipt_args['id'] = give_get_purchase_id_by_key( $payment_key );
	$customer_id             = give_get_payment_user_id( $give_receipt_args['id'] );

	/*
	 * Check if the user has permission to view the receipt
	 *
	 * If user is logged in, user ID is compared to user ID of ID stored in payment meta
	 *
	 * Or if user is logged out and donation was made as a guest, the donation session is checked for
	 *
	 * Or if user is logged in and the user can view sensitive donor data
	 */
	$user_can_view = ( is_user_logged_in() && $customer_id == get_current_user_id() ) || ( ( $customer_id == 0 || $customer_id == '-1' ) && ! is_user_logged_in() && give_get_purchase_session() ) || current_user_can( 'view_give_sensitive_data' );

	if ( ! apply_filters( 'give_user_can_view_receipt', $user_can_view, $give_receipt_args ) ) {
		return $give_receipt_args['error'];
	}

	ob_start();

	give_get_template_part( 'shortcode', 'receipt' );

	$display = ob_get_clean();

	return $display;
}

add_shortcode( 'give_receipt', 'give_receipt_shortcode' );

/**
 * Profile Editor Shortcode
 *
 * Outputs the Give Profile Editor to allow users to amend their details from the
 * front-end. This function uses the Give templating system allowing users to
 * override the default profile editor template. The profile editor template is located
 * under templates/profile-editor.php, however, it can be altered by creating a
 * file called profile-editor.php in the give_template directory in your active theme's
 * folder. Please visit the Give Documentation for more information on how the
 * templating system is used.
 *
 * @since  1.0
 *
 * @param array $atts attributes
 * @param null  $content
 *
 * @return string Output generated from the profile editor
 */
function give_profile_editor_shortcode( $atts, $content = null ) {

	ob_start();

	give_get_template_part( 'shortcode', 'profile-editor' );

	$display = ob_get_clean();

	return $display;
}

add_shortcode( 'give_profile_editor', 'give_profile_editor_shortcode' );

/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @since  1.0
 * @author Sunny Ratilal
 *
 * @param array $data Data sent from the profile editor
 *
 * @return void
 */
function give_process_profile_editor_updates( $data ) {
	// Profile field change request
	if ( empty( $_POST['give_profile_editor_submit'] ) && ! is_user_logged_in() ) {
		return false;
	}

	// Nonce security
	if ( ! wp_verify_nonce( $data['give_profile_editor_nonce'], 'give-profile-editor-nonce' ) ) {
		return false;
	}

	$user_id       = get_current_user_id();
	$old_user_data = get_userdata( $user_id );

	$display_name = isset( $data['give_display_name'] ) ? sanitize_text_field( $data['give_display_name'] ) : $old_user_data->display_name;
	$first_name   = isset( $data['give_first_name'] ) ? sanitize_text_field( $data['give_first_name'] ) : $old_user_data->first_name;
	$last_name    = isset( $data['give_last_name'] ) ? sanitize_text_field( $data['give_last_name'] ) : $old_user_data->last_name;
	$email        = isset( $data['give_email'] ) ? sanitize_email( $data['give_email'] ) : $old_user_data->user_email;
	$line1        = ( isset( $data['give_address_line1'] ) ? sanitize_text_field( $data['give_address_line1'] ) : '' );
	$line2        = ( isset( $data['give_address_line2'] ) ? sanitize_text_field( $data['give_address_line2'] ) : '' );
	$city         = ( isset( $data['give_address_city'] ) ? sanitize_text_field( $data['give_address_city'] ) : '' );
	$state        = ( isset( $data['give_address_state'] ) ? sanitize_text_field( $data['give_address_state'] ) : '' );
	$zip          = ( isset( $data['give_address_zip'] ) ? sanitize_text_field( $data['give_address_zip'] ) : '' );
	$country      = ( isset( $data['give_address_country'] ) ? sanitize_text_field( $data['give_address_country'] ) : '' );

	$userdata = array(
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email
	);


	$address = array(
		'line1'   => $line1,
		'line2'   => $line2,
		'city'    => $city,
		'state'   => $state,
		'zip'     => $zip,
		'country' => $country
	);

	do_action( 'give_pre_update_user_profile', $user_id, $userdata );

	// New password
	if ( ! empty( $data['give_new_user_pass1'] ) ) {
		if ( $data['give_new_user_pass1'] !== $data['give_new_user_pass2'] ) {
			give_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'give' ) );
		} else {
			$userdata['user_pass'] = $data['give_new_user_pass1'];
		}
	}

	// Make sure the new email doesn't belong to another user
	if ( $email != $old_user_data->user_email ) {
		if ( email_exists( $email ) ) {
			give_set_error( 'email_exists', __( 'The email you entered belongs to another user. Please use another.', 'give' ) );
		}
	}

	// Check for errors
	$errors = give_get_errors();

	if ( $errors ) {
		// Send back to the profile editor if there are errors
		wp_redirect( $data['give_redirect'] );
		give_die();
	}

	// Update the user
	$meta    = update_user_meta( $user_id, '_give_user_address', $address );
	$updated = wp_update_user( $userdata );

	if ( $updated ) {
		do_action( 'give_user_profile_updated', $user_id, $userdata );
		wp_redirect( add_query_arg( 'updated', 'true', $data['give_redirect'] ) );
		give_die();
	}
}

add_action( 'give_edit_user_profile', 'give_process_profile_editor_updates' );

