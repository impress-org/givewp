<?php
/**
 * Login / Register Functions
 *
 * @package     Give
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Form
 *
 * @since 1.0
 * @global       $give_options
 * @global       $post
 *
 * @param string $redirect Redirect page URL
 *
 * @return string Login form
 */
function give_login_form( $redirect = '' ) {
	global $give_options, $give_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = give_get_current_page_url();
	}

	$give_login_redirect = $redirect;

	ob_start();

	give_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'give_login_form', ob_get_clean() );
}

/**
 * Registration Form
 *
 * @since 2.0
 * @global       $give_options
 * @global       $post
 *
 * @param string $redirect Redirect page URL
 *
 * @return string Register form
 */
function give_register_form( $redirect = '' ) {
	global $give_options, $give_register_redirect;

	if ( empty( $redirect ) ) {
		$redirect = give_get_current_page_url();
	}

	$give_register_redirect = $redirect;

	ob_start();

	if ( ! is_user_logged_in() ) {
		give_get_template_part( 'shortcode', 'register' );
	}

	return apply_filters( 'give_register_form', ob_get_clean() );
}

/**
 * Process Login Form
 *
 * @since 1.0
 *
 * @param array $data Data sent from the login form
 *
 * @return void
 */
function give_process_login_form( $data ) {
	if ( wp_verify_nonce( $data['give_login_nonce'], 'give-login-nonce' ) ) {
		$user_data = get_user_by( 'login', $data['give_user_login'] );
		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['give_user_login'] );
		}
		if ( $user_data ) {
			$user_ID    = $user_data->ID;
			$user_email = $user_data->user_email;
			if ( wp_check_password( $data['give_user_pass'], $user_data->user_pass, $user_data->ID ) ) {
				give_log_user_in( $user_data->ID, $data['give_user_login'], $data['give_user_pass'] );
			} else {
				give_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'give' ) );
			}
		} else {
			give_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'give' ) );
		}
		// Check for errors and redirect if none present
		$errors = give_get_errors();
		if ( ! $errors ) {
			$redirect = apply_filters( 'give_login_redirect', $data['give_redirect'], $user_ID );
			wp_redirect( $redirect );
			give_die();
		}
	}
}

add_action( 'give_user_login', 'give_process_login_form' );

/**
 * Log User In
 *
 * @since 1.0
 *
 * @param int    $user_id    User ID
 * @param string $user_login Username
 * @param string $user_pass  Password
 *
 * @return void
 */
function give_log_user_in( $user_id, $user_login, $user_pass ) {
	if ( $user_id < 1 ) {
		return;
	}

	wp_set_auth_cookie( $user_id );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'give_log_user_in', $user_id, $user_login, $user_pass );
}


/**
 * Process Register Form
 *
 * @since 2.0
 *
 * @param array $data Data sent from the register form
 *
 * @return void
 */
function give_process_register_form( $data ) {

	if ( is_user_logged_in() ) {
		return;
	}

	if ( empty( $_POST['give_register_submit'] ) ) {
		return;
	}

	do_action( 'give_pre_process_register_form' );

	if ( empty( $data['give_user_login'] ) ) {
		give_set_error( 'empty_username', __( 'Invalid username', 'give' ) );
	}

	if ( username_exists( $data['give_user_login'] ) ) {
		give_set_error( 'username_unavailable', __( 'Username already taken', 'give' ) );
	}

	if ( ! validate_username( $data['give_user_login'] ) ) {
		give_set_error( 'username_invalid', __( 'Invalid username', 'give' ) );
	}

	if ( email_exists( $data['give_user_email'] ) ) {
		give_set_error( 'email_unavailable', __( 'Email address already taken', 'give' ) );
	}

	if ( empty( $data['give_user_email'] ) || ! is_email( $data['give_user_email'] ) ) {
		give_set_error( 'email_invalid', __( 'Invalid email', 'give' ) );
	}

	if ( ! empty( $data['give_payment_email'] ) && $data['give_payment_email'] != $data['give_user_email'] && ! is_email( $data['give_payment_email'] ) ) {
		give_set_error( 'payment_email_invalid', __( 'Invalid payment email', 'give' ) );
	}

	if ( empty( $_POST['give_user_pass'] ) ) {
		give_set_error( 'empty_password', __( 'Please enter a password', 'give' ) );
	}

	if ( ( ! empty( $_POST['give_user_pass'] ) && empty( $_POST['give_user_pass2'] ) ) || ( $_POST['give_user_pass'] !== $_POST['give_user_pass2'] ) ) {
		give_set_error( 'password_mismatch', __( 'Passwords do not match', 'give' ) );
	}

	do_action( 'give_process_register_form' );

	// Check for errors and redirect if none present
	$errors = give_get_errors();

	if ( empty( $errors ) ) {

		$redirect = apply_filters( 'give_register_redirect', $data['give_redirect'] );

		give_register_and_login_new_user( array(
			'user_login'      => $data['give_user_login'],
			'user_pass'       => $data['give_user_pass'],
			'user_email'      => $data['give_user_email'],
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' )
		) );

		wp_redirect( $redirect );
		give_die();
	}
}

add_action( 'give_user_register', 'give_process_register_form' );