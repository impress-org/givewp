<?php
/**
 * Give Shortcodes
 *
 * @package     Give
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Donation History Shortcode
 *
 * Displays a user's donation history.
 *
 * @since  1.0
 *
 * @return string
 */
function give_donation_history() {

	// If payment_key query arg exists, return receipt instead of donation history.
	if ( isset( $_GET['payment_key'] ) ) {
		ob_start();
		echo give_receipt_shortcode( array() );
		echo '<a href="' . esc_url( give_get_history_page_uri() ) . '">&laquo; ' . esc_html__( 'Return to All Donations', 'give' ) . '</a>';

		return ob_get_clean();
	}

	$email_access = give_get_option( 'email_access' );

	//Is user logged in? Does a session exist? Does an email-access token exist?
	if ( is_user_logged_in() || Give()->session->get_session_expiration() !== false || ( $email_access == 'on' && Give()->email_access->token_exists ) ) {
		ob_start();
		give_get_template_part( 'history', 'donations' );

		return ob_get_clean();
	} //Is Email-based access enabled?
	elseif ( $email_access == 'on' ) {

		ob_start();
		give_get_template_part( 'email', 'login-form' );

		return ob_get_clean();
	} else {
		$message = esc_html__( 'You must be logged in to view your donation history. Please login using your account or create an account using the same email you used to donate with.', 'give' );
		echo apply_filters( 'give_donation_history_nonuser_message', give_output_error( $message, false ), $message );
	}
}

add_shortcode( 'donation_history', 'give_donation_history' );

/**
 * Donation Form Shortcode
 *
 * Show the Give donation form.
 *
 * @since  1.0
 *
 * @param  array  $atts Shortcode attributes
 *
 * @return string
 */
function give_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'id'            => '',
		'show_title'    => true,
		'show_goal'     => true,
		'show_content'  => '',
		'float_labels'  => '',
		'display_style' => '',
	), $atts, 'give_form' );

	foreach ( $atts as $key => $value ) {
		//convert shortcode_atts values to booleans
		if ( $key == 'show_title' ) {
			$atts[ $key ] = filter_var( $atts[ $key ], FILTER_VALIDATE_BOOLEAN );
		} elseif ( $key == 'show_goal' ) {
			$atts[ $key ] = filter_var( $atts[ $key ], FILTER_VALIDATE_BOOLEAN );
		}

		//validate show_content value
		if ( $key == 'show_content' ) {
			if ( ! in_array( $value, array( 'none', 'above', 'below' ) ) ) {
				$atts[ $key ] = '';
			} else if ( $value == 'above' ) {
				$atts[ $key ] = 'give_pre_form';
			} else if ( $value == 'below' ) {
				$atts[ $key ] = 'give_post_form';
			}
		}

		//validate display_style and float_labels value
		if ( ( $key == 'display_style' && ! in_array( $value, array( 'onpage', 'reveal', 'modal' ) ) )
		     || ( $key == 'float_labels' && ! in_array( $value, array( 'enabled', 'disabled' ) ) )
		) {

			$atts[ $key ] = '';
		}
	}

	//get the Give Form
	ob_start();
	give_get_donation_form( $atts );
	$final_output = ob_get_clean();

	return apply_filters( 'give_donate_form', $final_output, $atts );
}

add_shortcode( 'give_form', 'give_form_shortcode' );

/**
 * Donation Form Goal Shortcode.
 *
 * Show the Give donation form goals.
 *
 * @since  1.0
 *
 * @param  array  $atts Shortcode attributes.
 *
 * @return string
 */
function give_goal_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'id'        => '',
		'show_text' => true,
		'show_bar'  => true,
	), $atts, 'give_goal' );


	//get the Give Form.
	ob_start();

	//Sanity check 1: ensure there is an ID Provided.
	if ( empty( $atts['id'] ) ) {
		give_output_error( esc_html__( 'The shortcode is missing Donation Form ID attribute.', 'give' ), true );
	}

	//Sanity check 2: Check the form even has Goals enabled.
	$goal_option = get_post_meta( $atts['id'], '_give_goal_option', true );
	if ( empty( $goal_option ) || $goal_option !== 'yes' ) {
		give_output_error( esc_html__( 'The form does not have Goals enabled.', 'give' ), true );
	} else {
		//Passed all sanity checks: output Goal.
		give_show_goal_progress( $atts['id'], $atts );
	}

	$final_output = ob_get_clean();

	return apply_filters( 'give_goal_shortcode_output', $final_output, $atts );
}

add_shortcode( 'give_goal', 'give_goal_shortcode' );


/**
 * Login Shortcode.
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the give_login_form function to display the login form.
 *
 * @since  1.0
 *
 * @param  array  $atts Shortcode attributes.
 *
 * @uses   give_login_form()
 *
 * @return string
 */
function give_login_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
        // Add backward compatibility for redirect attribute.
        'redirect'          => '',

		'login-redirect'    => '',
		'logout-redirect'   => '',
	), $atts, 'give_login' );

    // Check login-redirect attribute first, if it empty or not found then check for redirect attribute and add value of this to login-redirect attribute.
    $atts['login-redirect'] = ! empty( $atts['login-redirect'] ) ? $atts['login-redirect'] : ( ! empty( $atts['redirect' ] ) ? $atts['redirect'] : '' );

	return give_login_form( $atts['login-redirect'], $atts['logout-redirect'] );
}

add_shortcode( 'give_login', 'give_login_form_shortcode' );

/**
 * Register Shortcode.
 *
 * Shows a registration form allowing users to users to register for the site.
 *
 * @since  1.0
 *
 * @param  array  $atts Shortcode attributes.
 *
 * @uses   give_register_form()
 *
 * @return string
 */
function give_register_form_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'redirect' => '',
	), $atts, 'give_register' );

	return give_register_form( $atts['redirect'] );
}

add_shortcode( 'give_register', 'give_register_form_shortcode' );

/**
 * Receipt Shortcode.
 *
 * Shows a donation receipt.
 *
 * @since  1.0
 *
 * @param  array  $atts Shortcode attributes.
 *
 * @return string
 */
function give_receipt_shortcode( $atts ) {

	global $give_receipt_args, $payment;

	$give_receipt_args = shortcode_atts( array(
		'error'          => esc_html__( 'You are missing the payment key to view this donation receipt.', 'give' ),
		'price'          => true,
		'donor'          => true,
		'date'           => true,
		'payment_key'    => false,
		'payment_method' => true,
		'payment_id'     => true,
		'payment_status' => false,
		'status_notice'  => true,
	), $atts, 'give_receipt' );

	//set $session var
	$session = give_get_purchase_session();

	//set payment key var
	if ( isset( $_GET['payment_key'] ) ) {
		$payment_key = urldecode( $_GET['payment_key'] );
	} elseif ( $session ) {
		$payment_key = $session['purchase_key'];
	} elseif ( $give_receipt_args['payment_key'] ) {
		$payment_key = $give_receipt_args['payment_key'];
	}

	$email_access = give_get_option( 'email_access' );

	// No payment_key found & Email Access is Turned on:
	if ( ! isset( $payment_key ) && $email_access == 'on' && ! Give()->email_access->token_exists ) {

		ob_start();

		give_get_template_part( 'email-login-form' );

		return ob_get_clean();

	} elseif ( ! isset( $payment_key ) ) {

		return give_output_error( $give_receipt_args['error'], false, 'error' );

	}

	$payment_id    = give_get_purchase_id_by_key( $payment_key );
	$user_can_view = give_can_view_receipt( $payment_key );

	// Key was provided, but user is logged out. Offer them the ability to login and view the receipt.
	if ( ! $user_can_view && $email_access == 'on' && ! Give()->email_access->token_exists ) {

		ob_start();

		give_get_template_part( 'email-login-form' );

		return ob_get_clean();

	} elseif ( ! $user_can_view ) {

		global $give_login_redirect;

		$give_login_redirect = give_get_current_page_url();

		ob_start();

		give_output_error( apply_filters( 'give_must_be_logged_in_error_message', esc_html__( 'You must be logged in to view this donation receipt.', 'give' ) ) );

		give_get_template_part( 'shortcode', 'login' );

		$login_form = ob_get_clean();

		return $login_form;
	}

	/*
	 * Check if the user has permission to view the receipt.
	 *
	 * If user is logged in, user ID is compared to user ID of ID stored in payment meta
	 * or if user is logged out and donation was made as a guest, the donation session is checked for
	 * or if user is logged in and the user can view sensitive shop data.
	 *
	 */
	if ( ! apply_filters( 'give_user_can_view_receipt', $user_can_view, $give_receipt_args ) ) {
		return give_output_error( $give_receipt_args['error'], false, 'error' );
	}

	ob_start();

	give_get_template_part( 'shortcode', 'receipt' );

	$display = ob_get_clean();

	return $display;
}

add_shortcode( 'give_receipt', 'give_receipt_shortcode' );

/**
 * Profile Editor Shortcode.
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
 * @param  array  $atts Shortcode attributes.
 *
 * @return string Output generated from the profile editor
 */
function give_profile_editor_shortcode( $atts ) {

	ob_start();

	give_get_template_part( 'shortcode', 'profile-editor' );

	$display = ob_get_clean();

	return $display;
}

add_shortcode( 'give_profile_editor', 'give_profile_editor_shortcode' );

/**
 * Process Profile Updater Form.
 *
 * Processes the profile updater form by updating the necessary fields.
 *
 * @since  1.0
 *
 * @param  array $data Data sent from the profile editor.
 *
 * @return bool
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

	/**
	 * Fires before updating user profile.
	 *
	 * @since 1.0
	 *
	 * @param int   $user_id  The ID of the user.
	 * @param array $userdata User info, including ID, first name, last name, display name and email.
	 */
	do_action( 'give_pre_update_user_profile', $user_id, $userdata );

	// New password
	if ( ! empty( $data['give_new_user_pass1'] ) ) {
		if ( $data['give_new_user_pass1'] !== $data['give_new_user_pass2'] ) {
			give_set_error( 'password_mismatch', esc_html__( 'The passwords you entered do not match. Please try again.', 'give' ) );
		} else {
			$userdata['user_pass'] = $data['give_new_user_pass1'];
		}
	}

	if( empty( $email ) ) {
		// Make sure email should not be empty.
		give_set_error( 'email_empty', esc_html__( 'The email you entered is empty.', 'give' ) );

	}else if ( ! is_email( $email ) ){
		// Make sure email should be valid.
		give_set_error( 'email_not_valid', esc_html__( 'The email you entered is not valid. Please use another', 'give' ) );

	}else if ( $email != $old_user_data->user_email ) {
		// Make sure the new email doesn't belong to another user
		if ( email_exists( $email ) ) {
			give_set_error( 'email_exists', esc_html__( 'The email you entered belongs to another user. Please use another.', 'give' ) );
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

		/**
		 * Fires after updating user profile.
		 *
		 * @since 1.0
		 *
		 * @param int   $user_id  The ID of the user.
		 * @param array $userdata User info, including ID, first name, last name, display name and email.
		 */
		do_action( 'give_user_profile_updated', $user_id, $userdata );
		wp_redirect( add_query_arg( 'updated', 'true', $data['give_redirect'] ) );
		give_die();
	}

	return false;
}

add_action( 'give_edit_user_profile', 'give_process_profile_editor_updates' );
