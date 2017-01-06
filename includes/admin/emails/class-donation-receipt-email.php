<?php
/**
 * New Donation Email
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.9
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Donation_Receipt_Email' ) ) :

	/**
	 * Give_Donation_Receipt_Email
	 *
	 * @abstract
	 * @since       1.9
	 */
	class Give_Donation_Receipt_Email extends Give_Email_Notification {
		/* @var Give_Payment $payment*/
		private $payment;

		/**
		 * Payment id
		 *
		 * @since 1.9
		 * @var int
		 */
		private $payment_id = 0;

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.9
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'donation-receipt';
			$this->label       = __( 'Donation Receipt', 'give' );
			$this->description = __( 'Donation Receipt Notification will be sent to donor when new donation received.', 'give' );

			$this->notification_status  = 'enabled';
			$this->recipient_group_name = __( 'Donor', 'give' );

			// Initialize empty payment.
			$this->payment = new Give_Payment(0);

			parent::__construct();

			add_action( 'give_complete_donation', array( $this, 'setup_email_notification' ) );
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.9
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			return esc_attr__( 'Donation Receipt', 'give' );
		}


		/**
		 * Get default email message.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @return string
		 */
		public function get_default_email_message() {
			$message = esc_html__( 'Dear', 'give' ) . " {name},\n\n";
			$message .= esc_html__( 'Thank you for your donation. Your generosity is appreciated! Here are the details of your donation:', 'give' ) . "\n\n";
			$message .= '<strong>' . esc_html__( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
			$message .= '<strong>' . esc_html__( 'Donation:', 'give' ) . '</strong> {donation}' . "\n";
			$message .= '<strong>' . esc_html__( 'Donation Date:', 'give' ) . '</strong> {date}' . "\n";
			$message .= '<strong>' . esc_html__( 'Amount:', 'give' ) . '</strong> {amount}' . "\n";
			$message .= '<strong>' . esc_html__( 'Payment Method:', 'give' ) . '</strong> {payment_method}' . "\n";
			$message .= '<strong>' . esc_html__( 'Payment ID:', 'give' ) . '</strong> {payment_id}' . "\n";
			$message .= '<strong>' . esc_html__( 'Receipt ID:', 'give' ) . '</strong> {receipt_id}' . "\n\n";
			$message .= '{receipt_link}' . "\n\n";
			$message .= "\n\n";
			$message .= esc_html__( 'Sincerely,', 'give' ) . "\n";
			$message .= '{sitename}' . "\n";


			/**
			 * Filter the donation receipt email message
			 *
			 * @since 1.9
			 *
			 * @param string $message
			 */
			return apply_filters( 'give_default_donation_receipt_email', $message );
		}


		/**
		 * Get email message.
		 *
		 * @since  1.9
		 * @access public
		 * @return string
		 */
		public function get_email_message() {
			$payment = new Give_Payment( $this->payment_id );

			$email_body = wpautop( give_get_option( "{$this->id}_email_message", $this->get_default_email_message() ) );
			$email_body = apply_filters( 'give_donation_receipt_' . Give()->emails->get_template(), $email_body, $payment->ID, $payment->payment_meta );

			return apply_filters( 'give_donation_receipt', $email_body, $payment->ID, $payment->payment_meta );
		}

		/**
		 * Get the recipient attachments.
		 *
		 * @since  1.9
		 * @access public
		 * @return array
		 */
		public function get_attachments() {
			$payment = new Give_Payment( $this->payment_id );

			/**
			 * Filter the attachments.
			 *
			 * @since 1.9
			 */
			return apply_filters( 'give_receipt_attachments', array(), $payment->ID, $payment->payment_meta );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param int $payment_id
		 */
		public function setup_email_notification( $payment_id ) {
			// Make sure we don't send a receipt while editing a donation.
			if ( isset( $_POST['give-action'] ) && 'edit_payment' == $_POST['give-action'] ) {
				return;
			}

			$this->payment = new Give_Payment( $payment_id );

			// Set recipient email.
			$this->recipient_email = $this->payment->email;

			// Send email.
			$this->send_email_notification( array( 'payment_id' => $payment_id ) );
		}
	}

endif; // End class_exists check

return new Give_Donation_Receipt_Email();