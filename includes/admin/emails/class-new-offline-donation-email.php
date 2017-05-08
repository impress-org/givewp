<?php
/**
 * New Offline Donation Email
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
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
	 * @since       2.0
	 */
	class Give_New_Offline_Donation_Email extends Give_Email_Notification {
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
				'id'                           => 'new-offline-donation',
				'label'                        => __( 'New Offline Donation', 'give' ),
				'description'                  => __( 'Donation Notification will be sent to admin when new offline donation received.', 'give' ),
				'has_recipient_Field'          => true,
				'notification_status'          => 'enabled',
				'notification_status_editable' => false,
				'preview_email_tags_values'    => array(
					'payment_method' => esc_html__( 'Offline', 'give' ),
				),
				'default_email_subject'        => $this->get_default_email_subject(),
				'default_email_message'        => $this->get_default_email_message(),
			) );

			add_action( 'give_insert_payment', array( $this, 'setup_email_notification' ) );
			add_action( 'give_save_settings_give_settings', array( $this, 'set_notification_status' ), 10, 2 );
			add_filter( 'give_email_notification_setting_fields', array( $this, 'add_custom_notification_status_setting' ), 10, 3 );
		}

		/**
		 * Get default email subject.
		 *
		 * @since  2.0
		 * @access public
		 * @return string
		 */
		public function get_default_email_subject() {
			/**
			 * Filter the default subject.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$subject = apply_filters(
				'give_offline_admin_donation_notification_subject',
				__( 'New Pending Donation', 'give' )
			);

			/**
			 * Filter the default subject
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_default_email_subject",
				$subject,
				$this
			);
		}


		/**
		 * Get default email message.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @return string
		 */
		public function get_default_email_message() {
			$message = __( 'Dear Admin,', 'give' ) . "\n\n";
			$message .= __( 'An offline donation has been made on your website:', 'give' ) . ' ' . get_bloginfo( 'name' ) . ' ';
			$message .= __( 'Hooray! The donation is in a pending status and is awaiting payment. Donation instructions have been emailed to the donor. Once you receive payment, be sure to mark the donation as complete using the link below.', 'give' ) . "\n\n";

			$message .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
			$message .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n\n";

			$message .= sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $this->payment->ID ),
				__( 'Click Here to View and/or Update Donation Details', 'give' )
			) . "\n\n";

			/**
			 * Filter the donation receipt email message
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 *
			 * @param string $message
			 */
			$message = apply_filters(
				'give_default_new_offline_donation_email',
				$message,
				$this->payment->ID
			);

			/**
			 * Filter the default message
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_default_email_message",
				$message,
				$this
			);
		}


		/**
		 * Get message
		 *
		 * @since 2.0
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = null ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				"{$this->config['id']}_email_message",
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the email message.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$message = apply_filters(
				'give_offline_admin_donation_notification',
				$message,
				$this->payment->ID
			);

			/**
			 * Filter the email message
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_message",
				$message,
				$this,
				$form_id
			);
		}


		/**
		 * Get attachments.
		 *
		 * @since 2.0
		 *
		 * @param int $form_id
		 *
		 * @return array
		 */
		public function get_email_attachments( $form_id = null ) {
			/**
			 * Filter the attachments.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$attachment = apply_filters(
				'give_offline_admin_donation_notification_attachments',
				array(),
				$this->payment->ID
			);

			/**
			 * Filter the attachments.
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_attachments",
				$attachment,
				$this
			);
		}


		/**
		 * Set email data.
		 *
		 * @since 2.0
		 */
		public function setup_email_data() {
			// Set recipient email.
			$this->recipient_email = $this->payment->email;

			// Set header.
			Give()->emails->__set(
				'headers',
				apply_filters(
					'give_offline_admin_donation_notification_headers',
					Give()->emails->get_headers(),
					$this->payment->ID
				)
			);
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

			// Exit if not donation was not with offline donation.
			if ( 'offline' !== $this->payment->gateway ) {
				return;
			}

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification( array(
				'payment_id' => $this->payment->ID,
			) );
		}

		/**
		 * Set notification status
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param $update_options
		 * @param $option_name
		 */
		public function set_notification_status( $update_options, $option_name ) {
			// Get updated settings.
			$update_options = give_get_settings();

			$notification_status = isset( $update_options['gateways']['offline'] ) ? 'enabled' : 'disabled';

			if (
				empty( $update_options[ "{$this->config['id']}_notification" ] )
				|| $notification_status !== $update_options[ "{$this->config['id']}_notification" ]
			) {
				$update_options[ "{$this->config['id']}_notification" ] = $notification_status;
				update_option( $option_name, $update_options );
			}
		}


		/**
		 * Add custom notification status setting.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param array                   $setting_fields
		 * @param Give_Email_Notification $email
		 * @param int                     $form_id
		 *
		 * @return mixed
		 */
		public function add_custom_notification_status_setting( $setting_fields, $email, $form_id ) {
			if ( ! $form_id || $this->config['id'] !== $email->config['id'] ) {
				return $setting_fields;
			}

			// Set notification status field.
			$notification_setitng_field = Give_Email_Setting_Field::get_notification_status_field( $this, $form_id );

			// Admin does not allow to manually disable this notification.
			unset( $notification_setitng_field['options']['disabled'] );

			$setting_fields = array_merge(
				array( $notification_setitng_field ),
				$setting_fields
			);

			return $setting_fields;
		}
	}

endif; // End class_exists check

return Give_New_Offline_Donation_Email::get_instance();
