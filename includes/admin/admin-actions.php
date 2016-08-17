<?php
/**
 * Admin Actions
 *
 * @package     Give
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes all Give actions sent via POST and GET by looking for the 'give-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function give_process_actions() {

	$_post_action = ! empty( $_POST['give-action'] ) ? $_POST['give-action'] : null;

	if ( isset( $_post_action ) ) {
		/**
		 * Fires in WordPress admin init, when give-action is present in $_POST.
		 *
		 * @since 1.0
		 *
		 * @param array $_POST Array of HTTP POST variables.
		 */
		do_action( "give_{$_post_action}", $_POST );
	}

	$_get_action = ! empty( $_GET['give_action'] ) ? $_GET['give_action'] : null;

	if ( isset( $_get_action ) ) {
		/**
		 * Fires in WordPress admin init, when give-action is present in $_GET.
		 *
		 * @since 1.0
		 *
		 * @param array $_GET Array of HTTP GET variables.
		 */
		do_action( "give_{$_get_action}", $_GET );
	}

}

add_action( 'admin_init', 'give_process_actions' );