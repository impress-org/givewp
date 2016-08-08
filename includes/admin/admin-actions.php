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


/**
 * Hide subscription notice if admin click on "Click here if already renewed" in subscription notice.
 *
 * @since 1.6
 * @return void
 */
function give_hide_subscription_notices() {
    if ( ! empty( $_GET['_give_hide_subscription_notices'] ) ) {
        $data = get_option( '_give_hide_subscription_notices', array() );
        $data[] = absint( $_GET['_give_hide_subscription_notices'] );

        // Store subscription ids.
        update_option( '_give_hide_subscription_notices', $data );
    }
}

add_action( 'admin_init', 'give_hide_subscription_notices' );