<?php
/**
 * Offline Donation Instruction Email
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

if ( ! class_exists( 'Give_Offline_Donation_Instruction_Email' ) ) :

	/**
	 * Give_Offline_Donation_Instruction_Email
	 *
	 * @abstract
	 * @since       1.8
	 */
	class Give_Offline_Donation_Instruction_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.8
		 */
		public function __construct( $objects = array() ) {
			parent::__construct();

			$this->id          = 'offline-donation-instruction';
			$this->label       = __( 'Offline Donation Instruction', 'give' );
			$this->description = __( 'Offline Donation Instruction will be sent to recipient(s) when offline donation received.', 'give' );

			$this->notification_status = 'enabled';
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			return esc_attr__( '{form_title} - Offline Donation Instructions', 'give' );
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
			 * Filter the email message
			 *
			 * @since 1.8
			 *
			 * @param string $message
			 */
			return apply_filters( 'give_default_offline_donation_instruction_email', $message );
		}
	}

endif; // End class_exists check

return new Give_Offline_Donation_Instruction_Email();