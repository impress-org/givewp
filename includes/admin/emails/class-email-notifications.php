<?php
/**
 * Email Notification
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8
 */

/**
 * Class Give_Email_Notifications
 */
class Give_Email_Notifications {
	/**
	 * Instance.
	 *
	 * @since  1.8
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Array of email notifications.
	 *
	 * @since  1.8
	 * @access private
	 * @var array
	 */
	private $emails = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8
	 * @access private
	 * Give_Payumoney_API constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.8
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup dependencies
	 *
	 * @since 1.8
	 */
	public function init() {
		// Load email notifications.
		$this->add_emails_notifications();
	}

	/**
	 * Add email notifications
	 *
	 * @since  1.8
	 * @access private
	 */
	private function add_emails_notifications() {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/abstract-email-notification.php';

		$this->emails = array(
			include 'class-new-donation-email.php',
			include 'class-donation-receipt-notification.php',
		);

		/**
		 * Filter the email notifications.
		 *
		 * @since 1.8
		 */
		$this->emails = apply_filters( 'give_email_notifications', $this->emails );
	}


	/**
	 * Get list of email notifications.
	 *
	 * @since  1.8
	 * @access public
	 * @return array
	 */
	public function get_email_notifications() {
		return $this->emails;
	}


	public function get_columns(){
		/**
		 * Filter the table columns
		 *
		 * @since 1.8
		 */
		return apply_filters( 'give_email_notification_setting_columns', array(
			'status'     => '',
			'name'       => __( 'Email', 'give' ),
			'email_type' => __( 'Content Type', 'give' ),
			'recipient'  => __( 'Recipient(s)', 'give' ),
			'setting'    => ''
		) );
	}
}


/**
 * Initialize functionality.
 */
Give_Email_Notifications::get_instance()->init();