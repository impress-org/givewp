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
        global $current_user;

        // check previously disabled notice ids.
        $already_dismiss_notices = ( $already_dismiss_notices = get_user_meta( $current_user->ID, '_give_hide_subscription_notices', true ) )
            ? $already_dismiss_notices
            : array();

        // Get notice id.
        $notice_id = absint( $_GET['_give_hide_subscription_notices'] );

        if( ! in_array( $notice_id, $already_dismiss_notices ) ) {
            $already_dismiss_notices[] = $notice_id;
        }

        // Store subscription ids.
        update_user_meta( $current_user->ID, '_give_hide_subscription_notices', $already_dismiss_notices );

        // Redirect user.
        wp_safe_redirect( remove_query_arg( '_give_hide_subscription_notices', $_SERVER['REQUEST_URI'] ) );
        exit();
    }
}

add_action( 'admin_init', 'give_hide_subscription_notices' );