<?php
/**
 * Admin Notices
 *
 * @package     Give
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Messages
 *
 * @since 1.0
 * @global $give_options Array of all the Give Options
 * @return void
 */
function give_admin_messages() {
	global $give_options;

	if ( isset( $_GET['give-message'] ) && 'payment_deleted' == $_GET['give-message'] && current_user_can( 'view_give_reports' ) ) {
		add_settings_error( 'give-notices', 'give-payment-deleted', __( 'The payment has been deleted.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'email_sent' == $_GET['give-message'] && current_user_can( 'view_give_reports' ) ) {
		add_settings_error( 'give-notices', 'give-payment-sent', __( 'The donation receipt has been resent.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'payment-note-deleted' == $_GET['give-message'] && current_user_can( 'view_give_reports' ) ) {
		add_settings_error( 'give-notices', 'give-payment-note-deleted', __( 'The payment note has been deleted.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['page'] ) && 'give-payment-history' == $_GET['page'] && current_user_can( 'view_give_reports' ) && give_is_test_mode() ) {
		add_settings_error( 'give-notices', 'give-payment-sent', sprintf( __( 'Note: Test Mode is enabled, only test donations are being displayed. <a href="%s">View Settings</a>', 'give' ), admin_url( 'edit.php?post_type=give_forms&page=give-settings' ) ), 'updated' );
	}


	if ( isset( $_GET['give-message'] ) && 'settings-imported' == $_GET['give-message'] && current_user_can( 'manage_give_settings' ) ) {
		add_settings_error( 'give-notices', 'give-settings-imported', __( 'The settings have been imported.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'note-added' == $_GET['give-message'] && current_user_can( 'edit_give_payments' ) ) {
		add_settings_error( 'give-notices', 'give-note-added', __( 'The payment note has been added successfully.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'payment-updated' == $_GET['give-message'] && current_user_can( 'edit_give_payments' ) ) {
		add_settings_error( 'give-notices', 'give-payment-updated', __( 'The payment has been successfully updated.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'api-key-generated' == $_GET['give-message'] && current_user_can( 'manage_give_settings' ) ) {
		add_settings_error( 'give-notices', 'give-api-key-generated', __( 'API keys successfully generated.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'api-key-failed' == $_GET['give-message'] && current_user_can( 'manage_give_settings' ) ) {
		add_settings_error( 'give-notices', 'give-api-key-failed', __( 'The specified user already has API keys or the specified user does not exist..', 'give' ), 'error' );
	}

	if ( isset( $_GET['give-message'] ) && 'api-key-regenerated' == $_GET['give-message'] && current_user_can( 'manage_give_settings' ) ) {
		add_settings_error( 'give-notices', 'give-api-key-regenerated', __( 'API keys successfully regenerated.', 'give' ), 'updated' );
	}

	if ( isset( $_GET['give-message'] ) && 'api-key-revoked' == $_GET['give-message'] && current_user_can( 'manage_give_settings' ) ) {
		add_settings_error( 'give-notices', 'give-api-key-revoked', __( 'API keys successfully revoked.', 'give' ), 'updated' );
	}


	if ( ! get_user_meta( get_current_user_id(), '_give_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_give_settings' ) && false !== get_transient( '_give_ajax_works' ) ) {

		if ( ! give_test_ajax_works() ) {

			echo '<div class="error">';
			echo '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may causes issues with Give.', 'give' ) . '</p>';
			echo '<p><a href="' . add_query_arg( array(
					'give_action' => 'dismiss_notices',
					'give_notice' => 'admin_ajax_inaccessible'
				) ) . '">' . __( 'Dismiss Notice', 'give' ) . '</a></p>';
			echo '</div>';

		}
	}

	settings_errors( 'give-notices' );
}

add_action( 'admin_notices', 'give_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since 1.0
 * @return void
 */
function give_admin_addons_notices() {
	add_settings_error( 'give-notices', 'give-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'give' ), 'error' );
	settings_errors( 'give-notices' );
}

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.0
 * @return void
 */
function give_dismiss_notices() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$notice = isset( $_GET['give_notice'] ) ? $_GET['give_notice'] : false;

	if ( ! $notice ) {
		return;
	} // No notice, so get out of here

	update_user_meta( get_current_user_id(), '_give_' . $notice . '_dismissed', 1 );

	wp_redirect( remove_query_arg( array( 'give_action', 'give_notice' ) ) );
	exit;

}

add_action( 'give_dismiss_notices', 'give_dismiss_notices' );
