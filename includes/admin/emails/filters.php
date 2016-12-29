<?php
/**
 * Filter for Email Notification
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.9
 */

/**
 * Add extra row actions to email notification table.
 *
 * @since 1.9
 *
 * @param array                   $row_actions
 * @param Give_Email_Notification $email
 *
 * @return array
 */
function give_email_notification_row_actions_callback( $row_actions, $email ) {
	if( $email->is_email_preview() ) {
		$preview_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			wp_nonce_url(
				add_query_arg(
					array( 'give_action' => 'preview_email', 'email_type' => $email->get_id() ),
					home_url()
				), 'give-preview-email'
			),
			__( 'Preview', 'give' )
		);

		$send_preview_email_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			wp_nonce_url(
				add_query_arg( array(
					'give_action'  => 'send_preview_email',
					'email_type' => $email->get_id(),
					'give-message' => 'sent-test-email',
				) ), 'give-send-preview-email' ),
			__( 'Send test email', 'give' )
		);

		$row_actions['email_preview'] = $preview_link;
		$row_actions['send_preview_email'] = $send_preview_email_link;
	}

	return $row_actions;
}
add_filter( 'give_email_notification_row_actions', 'give_email_notification_row_actions_callback', 10, 2 );