<?php
/**
 * Email access notification
 *
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

if ( ! class_exists( 'Give_Email_Access_Email' ) ) :

	/**
	 * Give_Email_Access_Email
	 *
	 * @abstract
	 * @since       2.0
	 */
	class Give_Email_Access_Email extends Give_Email_Notification {
		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.0
		 */
		public function init() {
			$this->load( array(
				'id'                           => 'email-access',
				'label'                        => __( 'Email access', 'give' ),
				'description'                  => __( 'Email Access Notification will be sent to recipient(s) when want to access their donation history using only email.', 'give' ),
				'notification_status'          => give_get_option( 'email_access', 'disabled' ),
				'form_metabox_setting'         => false,
				'notification_status_editable' => false,
				'email_tag_context'            => 'donor',
				'recipient_group_name'         => __( 'Donor', 'give' ),
				'default_email_subject'        => sprintf( __( 'Your Access Link to %s', 'give' ), get_bloginfo( 'name' ) ),
				'default_email_message'        => $this->get_default_email_message(),
			) );

			add_action( "give_{$this->config['id']}_email_notification", array( $this, 'setup_email_notification' ), 10, 2 );
			add_action( 'give_save_settings_give_settings', array( $this, 'set_notification_status' ), 10, 2 );
			add_filter( 'give_email_preview_header', array( $this, 'email_preview_header' ), 10, 2 );
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
			$subject = apply_filters( 'give_email_access_token_subject', $subject );

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
			$message = apply_filters( 'give_email_access_token_message', $message );

			/**
			 * Filter the email message
			 *
			 * @since 2.0
			 */
			$message = apply_filters( "give_{$this->config['id']}_get_default_email_message", $message, $this, $form_id );

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
			$attachments = apply_filters( 'give_admin_donation_notification_attachments', array() );

			/**
			 * Filters the donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 *
			 * @since 2.0
			 */
			$attachments = apply_filters( "give_{$this->config['id']}_get_email_attachments", $attachments, $this, $form_id );

			return $attachments;
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
			$message = __( 'You or someone in your organization requested an access link be sent to this email address. This is a temporary access link for you to view your donation information. Click on the link below to view:', 'give' ) . "\n\n";
			$message .= '{email_access_link}' . "\n\n";
			$message .= "\n\n";
			$message .= __( 'Sincerely,', 'give' ) . "\n";
			$message .= get_bloginfo( 'name' ) . "\n";

			/**
			 * Filter the new donation email message
			 *
			 * @since 2.0
			 *
			 * @param string $message
			 */
			return apply_filters( "give_{$this->config['id']}_get_default_email_message", $message, $this );
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
			$from_name = apply_filters( 'give_donation_from_name', Give()->emails->get_from_name() );

			/**
			 * Filters the from email.
			 * Note: This filter will deprecate soon.
			 *
			 * @since 1.0
			 */
			$from_email = apply_filters( 'give_donation_from_address', Give()->emails->get_from_address() );

			Give()->emails->__set( 'from_name', $from_name );
			Give()->emails->__set( 'from_email', $from_email );
			Give()->emails->__set( 'heading', apply_filters( 'give_email_access_token_heading', __( 'Your Access Link', 'give' ) ) );

			/**
			 * Filters the donation notification email headers.
			 *
			 * @since 1.0
			 */
			$headers = apply_filters( 'give_admin_donation_notification_headers', Give()->emails->get_headers() );

			Give()->emails->__set( 'headers', $headers );
		}

		/**
		 * Setup email notification.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int    $donor_id
		 * @param string $email
		 */
		public function setup_email_notification( $donor_id, $email ) {
			$donor = Give()->customers->get_by( 'id', $donor_id );
			$this->recipient_email = $email;

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification(
				array(
					'donor_id' => $donor_id,
					'user_id'  => $donor->user_id
				)
			);
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

			if (
				! empty( $update_options['email_access'] )
				&& ! empty( $update_options[ "{$this->config['id']}_notification" ] )
				&& $update_options['email_access'] !== $update_options[ "{$this->config['id']}_notification" ]
			) {
				$update_options[ "{$this->config['id']}_notification" ] = $update_options['email_access'];
				update_option( $option_name, $update_options );
			}
		}


		/**
		 * email preview header.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param string                  $email_preview_header
		 * @param Give_Email_Access_Email $email
		 * @return string
		 */
		public function email_preview_header( $email_preview_header, $email ) {
			if( $this->config['id'] === $email->config['id'] ) {
				$email_preview_header = '';
			}

			return $email_preview_header;
		}
	}

endif; // End class_exists check

return Give_Email_Access_Email::get_instance();
