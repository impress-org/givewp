<?php
/**
 * This file contain code to handle email notification setting ajax.
 *
 * Register settings Include and setup custom metaboxes and fields.
 *
 * @package    Give
 * @subpackage Classes/Emails
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @link       https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

/**
 * Enabled & disable notification
 *
 * @since 2.0
 */
function give_set_notification_status_handler() {
	// Is user have permission to edit give setting.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	$notification_id = isset( $_POST['notification_id'] ) ? give_clean( $_POST['notification_id'] ) : '';
	if ( ! empty( $notification_id ) && give_update_option( "{$notification_id}_notification", give_clean( $_POST['status'] ) ) ) {
		wp_send_json_success();
	}

	wp_send_json_error();
}

add_action( 'wp_ajax_give_set_notification_status', 'give_set_notification_status_handler' );
