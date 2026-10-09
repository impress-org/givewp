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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enabled & disable notification
 *
 * @since TBD Check that the status is set.
 * @since 2.0
 * @since 4.15.4 Add nonce verification.
 */
function give_set_notification_status_handler() {
	check_ajax_referer( 'give_set_notification_status', '_ajax_nonce' );

	// Is user have permission to edit give setting.
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.
	$notification_id = isset( $_POST['notification_id'] ) ? give_clean( $_POST['notification_id'] ) : '';
	if ( ! empty( $notification_id ) && isset( $_POST['status'] ) && give_update_option( "{$notification_id}_notification", give_clean( $_POST['status'] ) ) ) {
		wp_send_json_success();
	}
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	wp_send_json_error();
}

add_action( 'wp_ajax_give_set_notification_status', 'give_set_notification_status_handler' );
