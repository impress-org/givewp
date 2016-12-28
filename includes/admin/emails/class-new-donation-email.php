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

if ( ! class_exists( 'Give_New_Donation_Email' ) ) :

	/**
	 * Give_New_Donation_Email
	 *
	 * @abstract
	 * @since       1.8
	 */
	class Give_New_Donation_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.8
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'new-donation';
			$this->label       = __( 'New Donation', 'give' );
			$this->description = __( 'Donation Notification will be sent to recipient(s) when new donation received except offline donation.', 'give' );

			$this->has_recipient_field = true;
			$this->notification_status = 'enabled';

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
			return esc_attr__( 'New Donation - #{payment_id}', 'give' );
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
			$message = esc_html__( 'Hello', 'give' ) . "\n\n";
			$message .= esc_html__( 'A donation has been made.', 'give' ) . "\n\n";
			$message .= esc_html__( 'Donation:', 'give' ) . "\n\n";
			$message .= esc_html__( 'Donor:', 'give' ) . ' {fullname}' . "\n";
			$message .= esc_html__( 'Amount:', 'give' ) . ' {payment_total}' . "\n";
			$message .= esc_html__( 'Payment Method:', 'give' ) . ' {payment_method}' . "\n\n";
			$message .= esc_html__( 'Thank you', 'give' );


			/**
			 * Filter the new donation email message
			 *
			 * @since 1.8
			 * @param string $message
			 */
			return apply_filters( 'give_default_new_donation_email', $message );
		}
	}

endif; // End class_exists check

return new Give_New_Donation_Email();