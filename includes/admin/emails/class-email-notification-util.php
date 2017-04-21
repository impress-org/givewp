<?php
/**
 * Email Notification Util
 *
 * This class contains helper functions for  email notification.
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


class Give_Email_Notification_Util {
	/**
	 * Instance.
	 *
	 * @since  1.0
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.0
	 * @access private
	 * Give_Email_Notification_Util constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.0
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
	 * Check if notification has preview field or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function has_preview( Give_Email_Notification $email ) {
		return $email->config['has_preview'];
	}

	/**
	 * Check if notification has recipient field or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function has_recipient_field( Give_Email_Notification $email ) {
		return $email->config['has_recipient_field'];
	}

	/**
	 * Check if admin can edit notification status or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_notification_status_editable( Give_Email_Notification $email ) {
		return $email->config['notification_status_editable'];
	}

	/**
	 * Check email preview header active or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_email_preview_has_header( Give_Email_Notification $email ) {
		return $email->config['has_preview_header'];
	}

	/**
	 * Check email preview header active or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return bool
	 */
	public static function is_email_preview( Give_Email_Notification $email ) {
		return $email->config['has_preview'];
	}


	/**
	 * Check email active or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 *
	 * @return string
	 */
	public static function is_email_notification_active( Give_Email_Notification $email ) {
		return give_is_setting_enabled( $email->get_notification_status() );
	}

	/**
	 * Check if admin preview email or not
	 *
	 * @since  2.0
	 * @access public
	 * @return bool   $is_preview
	 */
	public static function can_preview_email() {
		$is_preview = false;

		if (
			current_user_can( 'manage_give_settings' )
			&& ! empty( $_GET['give_action'] )
			&& 'preview_email' === $_GET['give_action']
		) {
			$is_preview = true;
		}

		return $is_preview;
	}

	/**
	 * Check if admin preview email or not
	 *
	 * @since  2.0
	 * @access public
	 * @return bool   $is_preview
	 */
	public static function can_send_preview_email() {
		$is_preview = false;

		if (
			current_user_can( 'manage_give_settings' )
			&& ! empty( $_GET['give_action'] )
			&& 'send_preview_email' === $_GET['give_action']
		) {
			$is_preview = true;
		}

		return $is_preview;
	}
}
