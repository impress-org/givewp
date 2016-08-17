<?php
/**
 * Front-end Actions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks Give actions, when present in the $_GET superglobal. Every give_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function give_get_actions() {

	$_get_action = ! empty( $_GET['give_action'] ) ? $_GET['give_action'] : null;

	// Add backward compatibility to give-action param ( $_GET or $_POST )
	if(  doing_action( 'admin_init' ) && empty( $_get_action ) ) {
		$_get_action = ! empty( $_GET['give-action'] ) ? $_GET['give-action'] : null;
	}

	if ( isset( $_get_action ) ) {
		/**
		 * Fires in WordPress init or admin init, when give_action is present in $_GET.
		 *
		 * @since 1.0
		 *
		 * @param array $_GET Array of HTTP GET variables.
		 */
		do_action( "give_{$_get_action}", $_GET );
	}

}

add_action( 'init', 'give_get_actions' );
add_action( 'admin_init', 'give_get_actions' );

/**
 * Hooks Give actions, when present in the $_POST superglobal. Every give_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function give_post_actions() {

	$_post_action = ! empty( $_POST['give_action'] ) ? $_POST['give_action'] : null;


	// Add backward compatibility to give-action param ( $_GET or $_POST )
	if(  doing_action( 'admin_init' ) && empty( $_post_action ) ) {
		$_post_action = ! empty( $_POST['give-action'] ) ? $_POST['give-action'] : null;
	}

	if ( isset( $_post_action ) ) {
		/**
		 * Fires in WordPress init or admin init, when give_action is present in $_POST.
		 *
		 * @since 1.0
		 *
		 * @param array $_POST Array of HTTP POST variables.
		 */
		do_action( "give_{$_post_action}", $_POST );
	}

}

add_action( 'init', 'give_post_actions' );
add_action( 'admin_init', 'give_post_actions' );
