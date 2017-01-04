<?php
/**
 * Donor Register Email
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

if ( ! class_exists( 'Give_Donor_Register_Email' ) ) :

	/**
	 * Give_Donor_Register_Email
	 *
	 * @abstract
	 * @since       1.9
	 */
	class Give_Donor_Register_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.9
		 */
		public function __construct( $objects = array() ) {
			$this->id          = 'donor-register';
			$this->label       = __( 'Donor Register', 'give' );
			$this->description = __( 'Donor Register Notification will be sent to donor when new donor registered.', 'give' );

			$this->notification_status  = 'enabled';
			$this->recipient_group_name = __( 'Donor', 'give' );
			$this->email_tag_context    = 'donor';

			parent::__construct();

			// Setup action hook.
			add_action(
				"give_{$this->action}_email_notification",
				array( $this, 'setup_email_notification' ),
				10,
				2
			);
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.9
		 * @access public
		 * @return string
		 */
		function get_default_email_subject() {
			return sprintf(
			/* translators: %s: site name */
				esc_attr__( '[%s] Your username and password', 'give' ),
				get_bloginfo( 'name' )
			);
		}

		/**
		 * Get default email message.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param array $args Email Arguments.
		 *
		 * @return string
		 */
		function get_default_email_message( $args = array() ) {
			$message = esc_attr__( 'Username: {username}', 'give' ) . "\r\n";
			$message .= sprintf(
				esc_attr__( 'Password: %s', 'give' ),
				esc_attr__( '[Password entered during donation]', 'give' )
			) . "\r\n";

			$message .= '<a href="' . wp_login_url() . '"> ' . esc_attr__( 'Click Here to Login &raquo;', 'give' ) . '</a>' . "\r\n";


			return $message;
		}


		/**
		 * Setup and send new donor register notifications.
		 *
		 * @since  1.9
		 * @access public
		 *
		 * @param int   $user_id   User ID.
		 * @param array $user_data User Information.
		 *
		 * @return string
		 */
		public function setup_email_notification( $user_id, $user_data ) {
			$this->recipient_email = $user_data['user_email'];
			$this->send_email_notification( array( 'user_id' => $user_id ) );
		}
	}

endif; // End class_exists check

return new Give_Donor_Register_Email();