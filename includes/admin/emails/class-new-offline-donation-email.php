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

if ( ! class_exists( 'Give_New_Offline_Donation_Email' ) ) :

	/**
	 * Give_New_Offline_Donation_Email
	 *
	 * @abstract
	 * @since       1.9
	 */
	class Give_New_Offline_Donation_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.9
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'new-offline-donation';
			$this->label       = __( 'New Offline Donation', 'give' );
			$this->description = __( 'Donation Notification will be sent to admin when new offline donation received.', 'give' );

			$this->has_recipient_field       = true;
			$this->notification_status       = 'enabled';
			$this->preview_email_tags_values = array(
				'payment_method' => esc_html__( 'Offline', 'give' ),
			);

			parent::__construct();

			add_action( 'give_offline_donation_created', array( $this, 'setup_email_notification' ) );
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.9
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			return __( 'New Pending Donation', 'give' );
		}


		/**
		 * Get default email message.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param array $args Email arguments.{
		 *      @type  int $payment_id Payment ID.
		 * }
		 *
		 * @return string
		 */
		public function get_default_email_message( $args = array() ) {
			$payment_id = isset( $args['payment_id'] ) ? absint( $args['payment_id'] ) : 0;

			$message = __( 'Dear Admin,', 'give' ) . "\n\n";
			$message .= __( 'An offline donation has been made on your website:', 'give' ) . ' ' . get_bloginfo( 'name' ) . ' ';
			$message .= __( 'Hooray! The donation is in a pending status and is awaiting payment. Donation instructions have been emailed to the donor. Once you receive payment, be sure to mark the donation as complete using the link below.', 'give' ) . "\n\n";


			$message .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
			$message .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n\n";

			$message .= sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $payment_id ),
				__( 'Click Here to View and/or Update Donation Details', 'give' )
			) . "\n\n";


			/**
			 * Filter the donation receipt email message
			 *
			 * @since 1.9
			 *
			 * @param string $message
			 */
			return apply_filters( 'give_default_new_offline_donation_email', $message, $payment_id );
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
			$payment = new Give_Payment( $payment_id );

			// Set recipient email.
			$this->recipient_email = $payment->user_info['email'];

			// Send email.
			$this->send_email_notification( array( 'payment_id' => $payment_id ) );
		}
	}

endif; // End class_exists check

return new Give_New_Offline_Donation_Email();