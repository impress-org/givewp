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
 * @param int  $payment_id   Payment ID.
 * @param bool $admin_notice Whether to send the admin email notification or not (default: true).
 *
 * @return void
 */
function give_email_donation_receipt( $payment_id, $admin_notice = true ) {
	$payment = new Give_Payment( $payment_id );
	/**
	 * Fire the action
	 */
	do_action( 'give_donation-receipt_email_notification', $payment_id );

	//If admin notifications are on, send the admin notice.
	if ( $admin_notice && ! give_admin_notices_disabled( $payment_id ) ) {
		/**
		 * Fires in the donation email receipt.
		 *
		 * When admin email notices are not disabled, you can add new email notices.
		 *
		 * @since 1.0
		 *
		 * @param int   $payment_id   Payment id.
		 * @param mixed $payment_data Payment meta data.
		 */
		do_action( 'give_new-donation_email_notification', $payment_id, $payment->payment_meta );
	}
}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID (default: 0)
 *
 * @return void
 */
function give_admin_email_notice( $payment_id ) {
	/**
	 * Fires in the donation email receipt.
	 *
	 * When admin email notices are not disabled, you can add new email notices.
	 *
	 * @since 1.0
	 *
	 * @param int   $payment_id   Payment id.
	 * @param mixed $payment_data Payment meta data.
	 */
	do_action( 'give_new-donation_email_notification', $payment_id );
}

add_action( 'give_admin_donation_email', 'give_admin_email_notice' );


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

	return apply_filters( 'give_default_donation_notification_email', $default_email_body );
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

	return apply_filters( 'give_default_donation_receipt_email', $default_email_body );
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
