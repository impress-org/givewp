<?php
/**
 * New Donor Register Email
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

if ( ! class_exists( 'Give_New_Donor_Register_Email' ) ) :

	/**
	 * Give_New_Donor_Register_Email
	 *
	 * @abstract
	 * @since       1.8
	 */
	class Give_New_Donor_Register_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.8
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'new-donor-register';
			$this->label       = __( 'New Donor Register', 'give' );
			$this->description = __( 'New Donor Register Notification will be sent to recipient(s) when new donor registered.', 'give' );

			$this->has_recipient_field = true;
			$this->notification_status = 'enabled';

			parent::__construct();

			// Setup action hook.
			add_action(
				"give_{$this->action}_email_notification",
				array( $this, 'send_email_notification' ),
				10,
				2
			);
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		function get_default_email_subject() {
			return sprintf(
			/* translators: %s: site name */
				esc_attr__( 'New user registration on your site %s:', 'give' ),
				get_bloginfo( 'name' )
			);
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
		function get_default_email_message( $args = array() ) {
			$message = esc_attr__( 'New user registration on your site {sitename}:', 'give' ) . "\r\n\r\n";
			$message .= esc_attr__( 'Username: {user_login}', 'give' ) . "\r\n\r\n";
			$message .= esc_attr__( 'E-mail: {user_email}', 'give' ) . "\r\n";


			return $message;
		}


		/**
		 * Send new donor register notifications.
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param int   $user_id   User ID.
		 * @param array $user_data User Information.
		 *
		 * @return string
		 */
		public function send_email_notification( $user_id, $user_data ) {
			$subject = $this->get_email_subject();
			$message = $this->get_email_message();

			// Send email.
			Give()->emails->send( $this->get_recipient(), $subject, $message );
		}
	}

endif; // End class_exists check

return new Give_New_Donor_Register_Email();