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
 * @since       1.8
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
	 * @since       1.8
	 */
	class Give_Donation_Receipt_Email extends Give_Email_Notification {

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
		 * @since   1.8
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'donation-receipt';
			$this->label       = __( 'Donation Receipt', 'give' );
			$this->description = __( 'Donation Receipt Notification will be sent to donor when new donation received.', 'give' );

			$this->notification_status  = 'enabled';
			$this->recipient_group_name = __( 'Donor', 'give' );

			parent::__construct();
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			return esc_attr__( 'Donation Receipt', 'give' );
		}


		/**
		 * Get default email message.
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param array $args Email Arguments.
		 *
		 * @return string
		 */
		public function get_default_email_message( $args = array() ) {
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
			 * @since 1.8
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
		 * Email Preview Template Tags.
		 *
		 * Provides sample content for the preview email functionality within settings > email.
		 *
		 * @since 1.9
		 *
		 * @param string $message Email message with template tags
		 *
		 * @return string $message Fully formatted message
		 */
		function preview_email_template_tags( $message ) {

			$price = give_currency_filter( give_format_amount( 10.50 ) );

			$gateway = 'PayPal';

			$receipt_id = strtolower( md5( uniqid() ) );

			$payment_id = rand( 1, 100 );

			$receipt_link_url = esc_url( add_query_arg( array(
				'payment_key' => $receipt_id,
				'give_action' => 'view_receipt',
			), home_url() ) );
			$receipt_link     = sprintf(
				'<a href="%1$s">%2$s</a>',
				$receipt_link_url,
				esc_html__( 'View the receipt in your browser &raquo;', 'give' )
			);

			$user = wp_get_current_user();

			$message = str_replace( '{name}', $user->display_name, $message );
			$message = str_replace( '{fullname}', $user->display_name, $message );
			$message = str_replace( '{username}', $user->user_login, $message );
			$message = str_replace( '{date}', date( give_date_format(), current_time( 'timestamp' ) ), $message );
			$message = str_replace( '{amount}', $price, $message );
			$message = str_replace( '{price}', $price, $message );
			$message = str_replace( '{donation}', esc_html__( 'Sample Donation Form Title', 'give' ), $message );
			$message = str_replace( '{form_title}', esc_html__( 'Sample Donation Form Title - Sample Donation Level', 'give' ), $message );
			$message = str_replace( '{receipt_id}', $receipt_id, $message );
			$message = str_replace( '{payment_method}', $gateway, $message );
			$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
			$message = str_replace( '{payment_id}', $payment_id, $message );
			$message = str_replace( '{receipt_link}', $receipt_link, $message );
			$message = str_replace( '{receipt_link_url}', $receipt_link_url, $message );
			$message = str_replace( '{pdf_receipt}', '<a href="#">Download Receipt</a>', $message );

			return wpautop( apply_filters( 'give_email_preview_template_tags', $message ) );
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
		 * Send preview email.
		 *
		 * @since  1.8
		 * @access public
		 */
		public function send_preview_email() {
			$subject     = $this->get_email_subject();
			$subject     = give_do_email_tags( $subject, 0 );
			$attachments = $this->get_attachments();
			$message     = $this->preview_email_template_tags( $this->get_email_message() );

			$this->email->__set( 'heading', $this->get_email_subject() );

			$this->send_email( $this->get_preview_email_recipient(), $subject, $message, $attachments );
		}
	}

endif; // End class_exists check

return new Give_Donation_Receipt_Email();