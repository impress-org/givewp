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
		private $donor_id = 0;
		private $donor_email = '';

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.0
		 */
		public function init() {
			$this->id          = 'email-access';
			$this->label       = __( 'Email access', 'give' );
			$this->description = __( 'Email Access Notification will be sent to recipient(s) when want to access their donation history using only email.', 'give' );

			// $this->has_recipient_field = true;
			$this->recipient_group_name = __( 'Donor', 'give' );
			$this->notification_status  = give_get_option( 'email_access' );
			$this->form_metabox_setting = true;
			$this->email_tag_context    = 'donor';

			$this->load();

			add_action( "give_{$this->id}_email_notification", array( $this, 'setup_email_notification' ) );
		}


		/**
		 * Get email subject.
		 *
		 * @since  2.0
		 * @access public
		 * @return string
		 */
		public function get_email_subject() {
			$subject = wp_strip_all_tags( give_get_option( "{$this->id}_email_subject", $this->get_default_email_subject() ) );

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
			$subject = apply_filters( "give_{$this->id}_get_email_subject", $subject, $this );

			return $subject;
		}


		/**
		 * Get email attachment.
		 *
		 * @since  2.0
		 * @access public
		 * @return string
		 */
		public function get_email_message() {
			$message = give_get_option( "{$this->id}_email_message", $this->get_default_email_message() );

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
			$message = apply_filters( "give_{$this->id}_get_default_email_message", $message, $this );

			return $message;
		}


		/**
		 * Get email attachment.
		 *
		 * @since  2.0
		 * @access public
		 * @return array
		 */
		public function get_email_attachments() {
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
			$attachments = apply_filters( "give_{$this->id}_get_email_attachments", $attachments, $this );

			return $attachments;
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
			 * Filter the defaul email subject.
			 *
			 * @since 2.0
			 */
			return apply_filters( "give_{$this->id}_get_default_email_subject", sprintf( __( 'Your Access Link to %s', 'give' ), get_bloginfo( 'name' ) ), $this );
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
			return apply_filters( "give_{$this->id}_get_default_email_message", $message, $this );
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
			$this->donor_id    = $donor_id;
			$this->donor_email = $email;
			$verify_key        = wp_generate_password( 20, false );

			// Set verify key.
			Give()->email_access->set_verify_key( $this->donor_id, $this->donor_email, $verify_key );

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification(
				array(
					'verify_key' => $verify_key,
					'access_url' => sprintf(
						'<a href="%1$s">%2$s</a>',
						add_query_arg(
							array( 'give_nl' => $verify_key ),
							get_permalink( give_get_option( 'history_page' ) )
						),
						__( 'Access Donation Details &raquo;', 'give' )
					)
				)
			);
		}
	}

endif; // End class_exists check

return Give_Email_Access_Email::get_instance();