<?php
/**
 * Email Functions
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, GiveWP
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
	/**
	 * Fire the action
	 */
	do_action( 'give_donation-receipt_email_notification', $payment_id );

	// If admin notifications are on, send the admin notice.
	if ( $admin_notice && give_is_setting_enabled( Give_Email_Notification::get_instance('new-donation' )->get_notification_status() ) ) {
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
		do_action( 'give_new-donation_email_notification', $payment_id, give_get_payment_meta( $payment_id ) );
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

	$default_email_body = __( 'Hi there,', 'give' ) . "\n\n";
	$default_email_body .= __( 'This email is to inform you that a new donation has been made on your website:', 'give' ) . ' {site_url}' . ".\n\n";
	$default_email_body .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {name}' . "\n";
	$default_email_body .= '<strong>' . __( 'Donation:', 'give' ) . '</strong> {donation}' . "\n";
	$default_email_body .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n";
	$default_email_body .= '<strong>' . __( 'Payment Method:', 'give' ) . '</strong> {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you,', 'give' ) . "\n\n";
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

	$default_email_body = __( 'Dear', 'give' ) . " {name},\n\n";
	$default_email_body .= __( 'Thank you for your donation. Your generosity is appreciated! Here are the details of your donation:', 'give' ) . "\n\n";
	$default_email_body .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
	$default_email_body .= '<strong>' . __( 'Donation:', 'give' ) . '</strong> {donation}' . "\n";
	$default_email_body .= '<strong>' . __( 'Donation Date:', 'give' ) . '</strong> {date}' . "\n";
	$default_email_body .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n";
	$default_email_body .= '<strong>' . __( 'Payment Method:', 'give' ) . '</strong> {payment_method}' . "\n";
	$default_email_body .= '<strong>' . __( 'Payment ID:', 'give' ) . '</strong> {payment_id}' . "\n\n";
	$default_email_body .= '{receipt_link}' . "\n\n";
	$default_email_body .= "\n\n";
	$default_email_body .= __( 'Sincerely,', 'give' ) . "\n";
	$default_email_body .= '{sitename}' . "\n";

	return apply_filters( 'give_default_donation_receipt_email', $default_email_body );
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.0
 *
 * @param array             $user_info List of User Information.
 * @param Give_Payment|bool $payment   Payment Object.
 *
 * @return array $email_names
 */
function give_get_email_names( $user_info, $payment = false ) {
	$email_names = array();

	if ( is_a( $payment, 'Give_Payment' ) ) {

		if ( $payment->user_id > 0 ) {

			$user_data               = get_userdata( $payment->user_id );
			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $user_data->user_login;

		} elseif ( ! empty( $payment->first_name ) ) {

			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;

		} else {

			$email_names['name']     = $payment->email;
			$email_names['username'] = $payment->email;

		}
	} else {

		// Support for old serialized data.
		if ( is_serialized( $user_info ) ) {

			// Security check.
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}
		}

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
	} // End if().

	// Set title prefix to name, if non empty.
	if ( ! empty( $user_info['title'] ) && ! empty( $user_info['last_name'] ) ) {
		$email_names['name'] = give_get_donor_name_with_title_prefixes( $user_info['title'], $user_info['last_name'] );
	}

	// Set title prefix to fullname, if non empty.
	if ( ! empty( $user_info['title'] ) && ! empty( $email_names['fullname'] ) ) {
		$email_names['fullname'] = give_get_donor_name_with_title_prefixes( $user_info['title'], $email_names['fullname'] );
	}

	return $email_names;
}

/**
 * Send email to admin when user tries to login and restricted due to user - donor disconnection.
 *
 * @param int $user_id  User ID.
 * @param int $donor_id Donor ID.
 *
 * @since 1.8.14
 */
function give_admin_email_user_donor_disconnection( $user_id, $donor_id ) {

	$user_id  = absint( $user_id );
	$donor_id = absint( $donor_id );

	// Bail Out, if user id doesn't exists.
	if ( empty( $user_id ) ) {
		return;
	}

	// Bail Out, if donor id doesn't exists.
	if ( empty( $donor_id ) ) {
		return;
	}

	$from_name = give_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

	$from_email = give_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	/* translators: %s: payment id */
	$subject = __( 'Attention: User tries to login whose Donor profile is disconnected!', 'give' );

	/**
	 * Filters the Donor-User Disconnection notification subject.
	 *
	 * @since 1.8.14
	 */
	$subject = apply_filters( 'give_admin_donor_user_disconnection_notification_subject', wp_strip_all_tags( $subject ) );

	$headers = "From: " . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
	$headers .= "Reply-To: " . $from_email . "\r\n";
	$headers .= "Content-Type: text/html; charset=utf-8\r\n";

	/**
	 * Filters the Donor-User Disconnection notification email headers.
	 *
	 * @since 1.8.14
	 */
	$headers = apply_filters( 'give_admin_donor_user_disconnection_notification_headers', $headers );

	$message = __( 'Hi Admin,', 'give' ) . "\n\n";
	$message .= __( 'This email is to inform you that a user has tried logging in. But, User was unable to login due to User-Donor profile disconnection.', 'give' ) . "\n\n";
	$message .= __( 'Do you want to reconnect User and Donor profile again?', 'give' ) . "\n\n";
	$message .= sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( admin_url() . 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor_id . '&user_id=' . $user_id . '&give-messages[]=reconnect-user' ),
		__( 'Reconnect User', 'give' ) . "\n\n"
	);
	$message .= __( 'Thank you,', 'give' ) . "\n\n";
	$message .= '{sitename}' . "\n";

	$emails = Give()->emails;
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'headers', $headers );
	$emails->__set( 'heading', __( 'User - Donor Profile Disconnection', 'give' ) );

	$emails->send( give_get_admin_notice_emails(), $subject, give_do_email_tags( $message ) );

}
