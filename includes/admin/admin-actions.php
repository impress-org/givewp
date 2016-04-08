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
	if ( isset( $_POST['give-action'] ) ) {
		do_action( 'give_' . $_POST['give-action'], $_POST );
	}

	if ( isset( $_GET['give-action'] ) ) {
		do_action( 'give_' . $_GET['give-action'], $_GET );
	}
}

add_action( 'admin_init', 'give_process_actions' );