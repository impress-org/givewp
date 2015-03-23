<?php
/**
 * Error Tracking
 *
 * @package     Give
 * @subpackage  Functions/Errors
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Print Errors
 *
 * Prints all stored errors. For use during checkout.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses  give_get_errors()
 * @uses  give_clear_errors()
 *
 * @param int $form_id
 *
 * @return void
 */
function give_print_errors( $form_id ) {

	$errors = give_get_errors();

	//Ensure errors show up on the appropriate form
	if ( ! isset( $_POST['give_ajax'] ) && isset( $_POST['give-form-id'] ) && intval( $_POST['give-form-id'] ) !== $form_id ) {
		return;
	} elseif ( ! isset( $_POST['give_ajax'] ) && isset( $_REQUEST['form_id'] ) && intval( $_REQUEST['form_id'] ) !== $form_id ) {
		return;
	}

	if ( $errors ) {
		$classes = apply_filters( 'give_error_class', array(
			'give_errors'
		) );
		echo '<div class="' . implode( ' ', $classes ) . '">';
		// Loop error codes and display errors
		foreach ( $errors as $error_id => $error ) {
			echo '<div class="give_error" id="give_error_' . $error_id . '"><p><strong>' . __( 'Error', 'give' ) . '</strong>: ' . $error . '</p></div>';
		}
		echo '</div>';
		give_clear_errors();
	}
}

add_action( 'give_purchase_form_before_personal_info', 'give_print_errors' );
add_action( 'give_ajax_checkout_errors', 'give_print_errors' );

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses  Give_Session::get()
 * @return mixed array if errors are present, false if none found
 */
function give_get_errors() {
	return Give()->session->get( 'give_errors' );
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses  Give_Session::get()
 *
 * @param int    $error_id      ID of the error being set
 * @param string $error_message Message to store with the error
 *
 * @return void
 */
function give_set_error( $error_id, $error_message ) {
	$errors = give_get_errors();
	if ( ! $errors ) {
		$errors = array();
	}
	$errors[ $error_id ] = $error_message;
	Give()->session->set( 'give_errors', $errors );
}

/**
 * Clears all stored errors.
 *
 * @since 1.0
 * @uses  Give_Session::set()
 * @return void
 */
function give_clear_errors() {
	Give()->session->set( 'give_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since 1.0
 * @uses  Give_Session::set()
 *
 * @param int $error_id ID of the error being set
 *
 * @return string
 */
function give_unset_error( $error_id ) {
	$errors = give_get_errors();
	if ( $errors ) {
		unset( $errors[ $error_id ] );
		Give()->session->set( 'give_errors', $errors );
	}
}

/**
 * Register die handler for give_die()
 *
 * @since  1.0
 * @return void
 */
function _give_die_handler() {
	if ( defined( 'GIVE_UNIT_TESTS' ) ) {
		return '_give_die_handler';
	} else {
		die();
	}
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using give_die() in the unit tests.
 *
 * @since  1.0
 * @return void
 */
function give_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_give_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_give_die_handler', 10, 3 );
	wp_die( $message, $title, array( 'response' => $status ) );
}
