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
		public $payment;

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

			add_action( "give_{$this->id}_email_notification", array( $this, 'send_donation_receipt' ) );
			add_action( 'give_email_links', array( $this, 'resend_donation_receipt' ) );
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
		 * Get email subject.
		 *
		 * @since 1.9
		 * @access public
		 * @return string
		 */
		public function get_email_subject() {
			$subject = wp_strip_all_tags( give_get_option( "{$this->id}_email_subject", $this->get_default_email_subject() ) );

			/**
			 * Filters the donation email receipt subject.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters( 'give_donation_subject', $subject, $this->payment->ID );

			return $subject;
		}


		/**
		 * Get email message.
		 *
		 * @since  1.9
		 * @access public
		 * @return string
		 */
		public function get_email_message() {
			$email_body = give_get_option( "{$this->id}_email_message", $this->get_default_email_message() );
			$email_body = apply_filters( 'give_donation_receipt_' . Give()->emails->get_template(), $email_body, $this->payment->ID, $this->payment->payment_meta );

			return apply_filters( 'give_donation_receipt', $email_body, $this->payment->ID, $this->payment->payment_meta );
		}

		/**
		 * Get the recipient attachments.
		 *
		 * @since  1.9
		 * @access public
		 * @return array
		 */
		public function get_attachments() {
			/**
			 * Filter the attachments.
			 *
			 * @since 1.9
			 */
			return apply_filters( 'give_receipt_attachments', array(), $this->payment->ID, $this->payment->payment_meta );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  1.9
		 * @access public
		 */
		public function setup_email_notification() {
			// Set recipient email.
			$this->recipient_email = $this->payment->email;

			/**
			 * Filters the from name.
			 *
			 * @param int $payment_id Payment id.
			 * @param mixed $payment_data Payment meta data.
			 *
			 * @since 1.0
			 */
			$from_name = apply_filters( 'give_donation_from_name', Give()->emails->get_from_name(), $this->payment->ID, $this->payment->payment_meta );

			/**
			 * Filters the from email.
			 *
			 * @param int $payment_id Payment id.
			 * @param mixed $payment_data Payment meta data.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters( 'give_donation_from_address', Give()->emails->get_from_address(), $this->payment->ID, $this->payment->payment_meta );

			Give()->emails->__set( 'from_name', $from_name );
			Give()->emails->__set( 'from_email', $from_email );
			Give()->emails->__set( 'heading', esc_html__( 'Donation Receipt', 'give' ) );
			
			/**
			 * Filters the donation receipt's email headers.
			 *
			 * @param int $payment_id Payment id.
			 * @param mixed $payment_data Payment meta data.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters( 'give_receipt_headers', Give()->emails->get_headers(), $this->payment->ID, $this->payment->payment_meta );

			Give()->emails->__set( 'headers', $headers );

			// Send email.
			$this->send_email_notification( array( 'payment_id' => $this->payment->ID ) );
		}


		/**
		 * Send donation receipt
		 * @since  1.9
		 * @access public
		 *
		 * @param $payment_id
		 */
		public function send_donation_receipt( $payment_id ) {
			$this->payment = new Give_Payment( $payment_id );
			$this->setup_email_notification();
		}

		/**
		 * Resend payment receipt by row action.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param array $data
		 */
		public function resend_donation_receipt( $data ) {
			$purchase_id = absint( $data['purchase_id'] );

			if ( empty( $purchase_id ) ) {
				return;
			}

			// Get donation payment information.
			$this->payment = new Give_Payment( $purchase_id );

			if ( ! current_user_can( 'edit_give_payments', $this->payment->ID ) ) {
				wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
			}

			$this->setup_email_notification();

			wp_redirect( add_query_arg( array(
				'give-message' => 'email_sent',
				'give-action'  => false,
				'purchase_id'  => false,
			) ) );
			exit;
		}
	}

endif; // End class_exists check

return new Give_Donation_Receipt_Email();