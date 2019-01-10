<?php
/**
 * Filter for Email Notification
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

/**
 * Add extra row actions to email notification table.
 *
 * @since 2.0
 *
 * @param array                   $row_actions
 * @param Give_Email_Notification $email
 *
 * @return array
 */
function give_email_notification_row_actions_callback( $row_actions, $email ) {
	if( Give_Email_Notification_Util::is_email_preview( $email ) ) {
		$preview_link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			wp_nonce_url(
				add_query_arg(
					array( 'give_action' => 'preview_email', 'email_type' => $email->config['id'] ),
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
					'email_type' => $email->config['id'],
					'give-messages[]' => 'sent-test-email',
				) ), 'give-send-preview-email' ),
			__( 'Send test email', 'give' )
		);

		$row_actions['email_preview'] = $preview_link;
		$row_actions['send_preview_email'] = $send_preview_email_link;
	}

	return $row_actions;
}
add_filter( 'give_email_notification_row_actions', 'give_email_notification_row_actions_callback', 10, 2 );

/**
 * This help to decode all email template tags.
 *
 * @since 2.0
 *
 * @param string      $message
 * @param Give_Emails $email_obj
 *
 * @return string
 */
function give_decode_email_tags( $message, $email_obj ) {
	if ( ! empty( $email_obj->tag_args ) ) {
		$message = give_do_email_tags( $message, $email_obj->tag_args );
	}

	return $message;
}

add_filter( 'give_email_message', 'give_decode_email_tags', 10, 2 );
