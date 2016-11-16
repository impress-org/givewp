<?php
/**
 * Email Functions
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Donation Receipt.
 *
 * Email the donation confirmation to the donor via the customizable "Donation Receipt" settings.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true).
 *
 * @return void
 */
function give_email_donation_receipt( $payment_id, $admin_notice = true ) {

	$payment_data = give_get_payment_meta( $payment_id );

	$from_name = give_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

	/**
	 * Filters the from name.
	 *
	 * @param int $payment_id Payment id.
	 * @param mixed $payment_data Payment meta data.
	 *
	 * @since 1.0
	 */
	$from_name = apply_filters( 'give_donation_from_name', $from_name, $payment_id, $payment_data );

	$from_email = give_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	/**
	 * Filters the from email.
	 *
	 * @param int $payment_id Payment id.
	 * @param mixed $payment_data Payment meta data.
	 *
	 * @since 1.0
	 */
	$from_email = apply_filters( 'give_donation_from_address', $from_email, $payment_id, $payment_data );

	$to_email = give_get_payment_user_email( $payment_id );

	$subject = give_get_option( 'donation_subject', esc_html__( 'Donation Receipt', 'give' ) );

	/**
	 * Filters the donation email receipt subject.
	 *
	 * @since 1.0
	 */
	$subject = apply_filters( 'give_donation_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = give_do_email_tags( $subject, $payment_id );

	/**
	 * Filters the donation email receipt attachments. By default, there is no attachment but plugins can hook in to provide one more multiple for the donor. Examples would be a printable ticket or PDF receipt.
	 *
	 * @param int $payment_id Payment id.
	 * @param mixed $payment_data Payment meta data.
	 *
	 * @since 1.0
	 */
	$attachments = apply_filters( 'give_receipt_attachments', array(), $payment_id, $payment_data );
	$message     = give_do_email_tags( give_get_email_body_content( $payment_id, $payment_data ), $payment_id );

	$emails = Give()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', esc_html__( 'Donation Receipt', 'give' ) );

	/**
	 * Filters the donation receipt's email headers.
	 *
	 * @param int $payment_id Payment id.
	 * @param mixed $payment_data Payment meta data.
	 *
	 * @since 1.0
	 */
	$headers = apply_filters( 'give_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	//Send the donation receipt.
	$emails->send( $to_email, $subject, $message, $attachments );

	//If admin notifications are on, send the admin notice.
	if ( $admin_notice && ! give_admin_notices_disabled( $payment_id ) ) {
		/**
		 * Fires in the donation email receipt.
		 *
		 * When admin email notices are not disabled, you can add new email notices.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 * @param mixed $payment_data Payment meta data.
		 */
		do_action( 'give_admin_donation_email', $payment_id, $payment_data );
	}
}

/**
 * Email the donation confirmation to the admin accounts for testing.
 *
 * @since 1.0
 *
 * @return void
 */
function give_email_test_donation_receipt() {

	$from_name = give_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

	/**
	 * Filters the from name.
	 *
	 * @since 1.7
	 */
	$from_name = apply_filters( 'give_donation_from_name', $from_name, 0, array() );

	$from_email = give_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	/**
	 * Filters the from email.
	 *
	 * @since 1.7
	 */
	$from_email = apply_filters( 'give_donation_from_address', $from_email, 0, array() );

	$subject = give_get_option( 'donation_subject', esc_html__( 'Donation Receipt', 'give' ) );
	$subject = apply_filters( 'give_donation_subject', wp_strip_all_tags( $subject ), 0 );
	$subject = give_do_email_tags( $subject, 0 );

	$attachments = apply_filters( 'give_receipt_attachments', array(), 0, array() );

	$message = give_email_preview_template_tags( give_get_email_body_content( 0, array() ) );

	$emails = Give()->emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', esc_html__( 'Donation Receipt', 'give' ) );

	$headers = apply_filters( 'give_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	$emails->send( give_get_admin_notice_emails(), $subject, $message, $attachments );

}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 *
 * @return void
 */
function give_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

	$payment_id = absint( $payment_id );

	if ( empty( $payment_id ) ) {
		return;
	}

	if ( ! give_get_payment_by( 'id', $payment_id ) ) {
		return;
	}

	$from_name = give_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

	/**
	 * Filters the from name.
	 *
	 * @since 1.0
	 */
	$from_name = apply_filters( 'give_donation_from_name', $from_name, $payment_id, $payment_data );

	$from_email = give_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	/**
	 * Filters the from email.
	 *
	 * @since 1.0
	 */
	$from_email = apply_filters( 'give_donation_from_address', $from_email, $payment_id, $payment_data );

	/* translators: %s: payment id */
	$subject = give_get_option( 'donation_notification_subject', sprintf( esc_html__( 'New Donation - Payment #%s', 'give' ), $payment_id ) );

	/**
	 * Filters the donation notification subject.
	 *
	 * @since 1.0
	 */
	$subject = apply_filters( 'give_admin_donation_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = give_do_email_tags( $subject, $payment_id );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: " . $from_email . "\r\n";
	//$headers  .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";

	/**
	 * Filters the donation notification email headers.
	 *
	 * @since 1.0
	 */
	$headers = apply_filters( 'give_admin_donation_notification_headers', $headers, $payment_id, $payment_data );

	/**
	 * Filters the donation notification email attachments. By default, there is no attachment but plugins can hook in to provide one more multiple.
	 *
	 * @since 1.0
	 */
	$attachments = apply_filters( 'give_admin_donation_notification_attachments', array(), $payment_id, $payment_data );

	$message = give_get_donation_notification_body_content( $payment_id, $payment_data );

	$emails = Give()->emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'headers', $headers );
	$emails->__set( 'heading', esc_html__( 'New Donation!', 'give' ) );

	$emails->send( give_get_admin_notice_emails(), $subject, $message, $attachments );

}

add_action( 'give_admin_donation_email', 'give_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be changed in the Give Settings).
 *
 * @since 1.0
 * @return mixed
 */
function give_get_admin_notice_emails() {

	$email_option = give_get_option( 'admin_notice_emails' );

	$emails = ! empty( $email_option ) && strlen( trim( $email_option ) ) > 0 ? $email_option : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'give_admin_notice_emails', $emails );
}

/**
 * Checks whether admin donation notices are disabled
 *
 * @since 1.0
 *
 * @param int $payment_id
 *
 * @return mixed
 */
function give_admin_notices_disabled( $payment_id = 0 ) {

	$retval = give_get_option( 'disable_admin_notices' );

	return apply_filters( 'give_admin_notices_disabled', $retval, $payment_id );
}

/**
 * Get default donation notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since  1.0
 * @return string $message
 */
function give_get_default_donation_notification_email() {

	$default_email_body = esc_html__( 'Hi there,', 'give' ) . "\n\n";
	$default_email_body .= esc_html__( 'This email is to inform you that a new donation has been made on your website:', 'give' ) . ' <a href="' . get_bloginfo( 'url' ) . '" target="_blank">' . get_bloginfo( 'url' ) . '</a>' . ".\n\n";
	$default_email_body .= '<strong>' . esc_html__( 'Donor:', 'give' ) . '</strong> {name}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Donation:', 'give' ) . '</strong> {donation}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Amount:', 'give' ) . '</strong> {amount}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Payment Method:', 'give' ) . '</strong> {payment_method}' . "\n\n";
	$default_email_body .= esc_html__( 'Thank you,', 'give' ) . "\n\n";
	$default_email_body .= '{sitename}' . "\n";

	$custom_message = give_get_option( 'donation_notification' );
	$message        = ! empty( $custom_message ) ? $custom_message : $default_email_body;

	return apply_filters( 'give_default_donation_notification_email', $message );
}


/**
 * Get default donation receipt email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since  1.3.7
 * @return string $message
 */
function give_get_default_donation_receipt_email() {

	$default_email_body = esc_html__( 'Dear', 'give' ) . " {name},\n\n";
	$default_email_body .= esc_html__( 'Thank you for your donation. Your generosity is appreciated! Here are the details of your donation:', 'give' ) . "\n\n";
	$default_email_body .= '<strong>' . esc_html__( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Donation:', 'give' ) . '</strong> {donation}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Donation Date:', 'give' ) . '</strong> {date}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Amount:', 'give' ) . '</strong> {amount}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Payment Method:', 'give' ) . '</strong> {payment_method}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Payment ID:', 'give' ) . '</strong> {payment_id}' . "\n";
	$default_email_body .= '<strong>' . esc_html__( 'Receipt ID:', 'give' ) . '</strong> {receipt_id}' . "\n\n";
	$default_email_body .= '{receipt_link}' . "\n\n";
	$default_email_body .= "\n\n";
	$default_email_body .= esc_html__( 'Sincerely,', 'give' ) . "\n";
	$default_email_body .= '{sitename}' . "\n";

	$custom_message = give_get_option( 'donation_receipt' );

	$message = ! empty( $custom_message ) ? $custom_message : $default_email_body;

	return apply_filters( 'give_default_donation_receipt_email', $message );
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.0
 *
 * @param $user_info
 *
 * @return array $email_names
 */
function give_get_email_names( $user_info ) {
	$email_names = array();
	$user_info   = maybe_unserialize( $user_info );

	$email_names['fullname'] = '';
	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
		$user_data               = get_userdata( $user_info['id'] );
		$email_names['name']     = $user_info['first_name'];
		$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names['username'] = $user_data->user_login;
	} elseif ( isset( $user_info['first_name'] ) ) {
		$email_names['name']     = $user_info['first_name'];
		$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
		$email_names['username'] = $user_info['first_name'];
	} else {
		$email_names['name']     = $user_info['email'];
		$email_names['username'] = $user_info['email'];
	}

	return $email_names;
}
