<?php
/**
 * New Donation Email
 *
 * Donation Notification will be sent to recipient(s) when new donation received except offline donation.
 *
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
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
	 * @since       2.0
	 */
	class Give_New_Donation_Email extends Give_Email_Notification {
		/* @var Give_Payment $payment */
		public $payment;

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.0
		 */
		public function init() {
			// Initialize empty payment.
			$this->payment = new Give_Payment( 0 );

			$this->load( array(
				'id'                    => 'new-donation',
				'label'                 => __( 'New Donation', 'give' ),
				'description'           => __( 'Sent to designated recipient(s) when a new donation is received or a pending donation is marked as complete.', 'give' ),
				'has_recipient_field'   => true,
				'notification_status'   => 'enabled',
				'form_metabox_setting'  => true,
				'default_email_subject' => esc_attr__( 'New Donation - #{payment_id}', 'give' ),
				'default_email_message' => ( false !== give_get_option( 'new-donation_email_message' ) ) ? give_get_option( 'new-donation_email_message' ) : give_get_default_donation_notification_email(),
				'default_email_header'  => __( 'New Donation!', 'give' ),
			) );

			add_action( "give_{$this->config['id']}_email_notification", array( $this, 'setup_email_notification' ) );
		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_subject( $form_id = null ) {
			$subject = wp_strip_all_tags(
				Give_Email_Notification_Util::get_value(
					$this,
					Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'email_subject',
					$form_id,
					$this->config['default_email_subject']
				)
			);

			/**
			 * Filters the donation notification subject.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters( 'give_admin_donation_notification_subject', $subject, $this->payment->ID );

			/**
			 * Filters the donation notification subject.
			 *
			 * @since 2.0
			 */
			$subject = apply_filters( "give_{$this->config['id']}_get_email_subject", $subject, $this, $form_id );

			return $subject;
		}


		/**
		 * Get email attachment.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = null ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'email_message',
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the email message
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$message = apply_filters(
				'give_donation_notification',
				$message,
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filter the email message
			 *
			 * @since 2.0
			 */
			$message = apply_filters(
				"give_{$this->config['id']}_get_default_email_message",
				$message,
				$this,
				$form_id
			);

			return $message;
		}


		/**
		 * Get email attachment.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 * @return array
		 */
		public function get_email_attachments( $form_id = null ) {
			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$attachments = apply_filters(
				'give_admin_donation_notification_attachments',
				array(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 *
			 * @since 2.0
			 */
			$attachments = apply_filters(
				"give_{$this->config['id']}_get_email_attachments",
				$attachments,
				$this,
				$form_id
			);

			return $attachments;
		}

		/**
		 * Set email data
		 *
		 * @since 2.0
		 */
		public function setup_email_data() {
			/**
			 * Filters the from name.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_name = apply_filters(
				'give_donation_from_name',
				Give()->emails->get_from_name(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			/**
			 * Filters the from email.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters(
				'give_donation_from_address',
				Give()->emails->get_from_address(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			Give()->emails->__set( 'from_name', $from_name );
			Give()->emails->__set( 'from_email', $from_email );

			/**
			 * Filters the donation notification email headers.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters(
				'give_admin_donation_notification_headers',
				Give()->emails->get_headers(),
				$this->payment->ID,
				$this->payment->payment_meta
			);

			Give()->emails->__set( 'headers', $headers );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $payment_id
		 */
		public function setup_email_notification( $payment_id ) {
			$this->payment = new Give_Payment( $payment_id );

			if( ! $this->payment->ID ) {
				wp_die( esc_html__( 'Cheatin&#8217; uh?', 'give' ), esc_html__( 'Error', 'give' ), array(
					'response' => 400,
				) );
			}

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification( array(
				'payment_id' => $payment_id,
			) );
		}
	}

endif; // End class_exists check

return Give_New_Donation_Email::get_instance();
