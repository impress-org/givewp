<?php
/**
 * Email Access
 *
 * @package     Give
 * @subpackage  Classes/Give_Email_Access
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Email_Access class
 *
 * This class handles email access, allowing donors access to their donation w/o logging in;
 *
 * Based on the work from Matt Gibbs - https://github.com/FacetWP/edd-no-logins
 *
 * @since 1.0
 */
class Give_Email_Access {

	/**
	 * Token exists
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    bool
	 */
	public $token_exists = false;

	/**
	 * Token email
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    bool
	 */
	public $token_email = false;

	/**
	 * Token
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    bool
	 */
	public $token = false;

	/**
	 * Error
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $error = '';

	/**
	 * Verify throttle
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var
	 */
	private $verify_throttle;

	/**
	 * Verify expiration
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    string
	 */
	private $token_expiration;

	/**
	 * Class Constructor
	 *
	 * Set up the Give Email Access Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {

		// get it started
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init
	 *
	 * Register defaults and filters
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init() {

		/**
		 * Do NOT pass go if:
		 *
		 * a. User is logged in
		 * b. Email access setting is not enabled
		 * c. You're in the admin
		 */
		if (
			is_user_logged_in()
			|| ! give_is_setting_enabled( give_get_option( 'email_access' ) )
			|| is_admin()
		) {
			return;
		}

		// Are db columns setup?
		$is_setup = give_get_option( 'email_access_installed' );
		if ( empty( $is_setup ) ) {
			$this->create_columns();
		}

		// Timeouts.
		$this->verify_throttle  = apply_filters( 'give_nl_verify_throttle', 300 );
		$this->token_expiration = apply_filters( 'give_nl_token_expiration', 7200 );

		// Setup login.
		$this->check_for_token();

		if ( $this->token_exists ) {
			add_filter( 'give_can_view_receipt', '__return_true' );
			add_filter( 'give_user_pending_verification', '__return_false' );
			add_filter( 'give_get_users_donations_args', array( $this, 'users_donations_args' ) );
		}

	}

	/**
	 * Prevent email spamming.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $customer_id string Customer id.
	 *
	 * @return bool
	 */
	public function can_send_email( $customer_id ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Prevent multiple emails within X minutes
		$throttle = date( 'Y-m-d H:i:s', time() - $this->verify_throttle );

		// Does a user row exist?
		$exists = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}give_customers WHERE id = %d", $customer_id )
		);

		if ( 0 < $exists ) {
			$row_id = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}give_customers WHERE id = %d AND (verify_throttle < %s OR verify_key = '') LIMIT 1", $customer_id, $throttle )
			);

			if ( $row_id < 1 ) {
				give_set_error( 'give_email_access_attempts_exhausted', __( 'Please wait a few minutes before requesting a new email access link.', 'give' ) );

				return false;
			}
		}

		return true;
	}

	/**
	 * Send the user's token
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $customer_id string Customer id.
	 * @param  $email       string Customer email.
	 *
	 * @return void
	 */
	public function send_email( $customer_id, $email ) {

		$verify_key = wp_generate_password( 20, false );

		// Generate a new verify key
		$this->set_verify_key( $customer_id, $email, $verify_key );

		// Get the donation history page
		$page_id = give_get_option( 'history_page' );

		$access_url = add_query_arg( array(
			'give_nl' => $verify_key,
		), get_permalink( $page_id ) );

		// Nice subject and message.
		$subject = apply_filters( 'give_email_access_token_subject', sprintf( __( 'Your Access Link to %s', 'give' ), get_bloginfo( 'name' ) ) );

		$message = __( 'You or someone in your organization requested an access link be sent to this email address. This is a temporary access link for you to view your donation information. Click on the link below to view:', 'give' ) . "\n\n";
		$message .= '<a href="' . esc_url( $access_url ) . '" target="_blank">' . __( 'Access Donation Details &raquo;', 'give' ) . '</a>' . "\n\n";
		$message .= "\n\n";
		$message .= __( 'Sincerely,', 'give' ) . "\n";
		$message .= get_bloginfo( 'name' ) . "\n";

		$message = apply_filters( 'give_email_access_token_message', $message );

		// Send the email.
		Give()->emails->__set( 'heading', apply_filters( 'give_email_access_token_heading', __( 'Your Access Link', 'give' ) ) );
		Give()->emails->send( $email, $subject, $message );

	}

	/**
	 * Has the user authenticated?
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function check_for_token() {

		$token = isset( $_GET['give_nl'] ) ? $_GET['give_nl'] : '';

		// Check for cookie.
		if ( empty( $token ) ) {
			$token = isset( $_COOKIE['give_nl'] ) ? $_COOKIE['give_nl'] : '';
		}

		// Must have a token.
		if ( ! empty( $token ) ) {

			if ( ! $this->is_valid_token( $token ) ) {
				if ( ! $this->is_valid_verify_key( $token ) ) {
					return;
				}
			}

			$this->token_exists = true;
			// Set cookie.
			$lifetime = current_time( 'timestamp' ) + Give()->session->set_expiration_time();
			@setcookie( 'give_nl', $token, $lifetime, COOKIEPATH, COOKIE_DOMAIN, false );

			return true;
		}
	}

	/**
	 * Is this a valid token?
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $token string The token.
	 *
	 * @return bool
	 */
	public function is_valid_token( $token ) {

		global $wpdb;

		// Make sure token isn't expired.
		$expires = date( 'Y-m-d H:i:s', time() - $this->token_expiration );

		$email = $wpdb->get_var(
			$wpdb->prepare( "SELECT email FROM {$wpdb->prefix}give_customers WHERE token = %s AND verify_throttle >= %s LIMIT 1", $token, $expires )
		);

		if ( ! empty( $email ) ) {
			$this->token_email = $email;
			$this->token       = $token;
			return true;
		}

		// Set error only if email access form isn't being submitted
		if ( ! isset( $_POST['give_email'] ) && ! isset( $_POST['_wpnonce'] ) ) {
			give_set_error( 'give_email_token_expired', apply_filters( 'give_email_token_expired_message', __( 'Your access token has expired. Please request a new one below:', 'give' ) ) );
		}

		return false;

	}

	/**
	 * Add the verify key to DB
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $customer_id string Customer id.
	 * @param  $email       string Customer email.
	 * @param  $verify_key  string The verification key.
	 *
	 * @return void
	 */
	public function set_verify_key( $customer_id, $email, $verify_key ) {
		global $wpdb;

		$now = date( 'Y-m-d H:i:s' );

		// Insert or update?
		$row_id = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}give_customers WHERE id = %d LIMIT 1", $customer_id )
		);

		// Update.
		if ( ! empty( $row_id ) ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE {$wpdb->prefix}give_customers SET verify_key = %s, verify_throttle = %s WHERE id = %d LIMIT 1", $verify_key, $now, $row_id )
			);
		} // Insert.
		else {
			$wpdb->query(
				$wpdb->prepare( "INSERT INTO {$wpdb->prefix}give_customers ( verify_key, verify_throttle) VALUES (%s, %s)", $verify_key, $now )
			);
		}
	}

	/**
	 * Is this a valid verify key?
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $token string The token.
	 *
	 * @return bool
	 */
	public function is_valid_verify_key( $token ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// See if the verify_key exists.
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT id, email FROM {$wpdb->prefix}give_customers WHERE verify_key = %s LIMIT 1", $token )
		);

		$now = date( 'Y-m-d H:i:s' );

		// Set token and remove verify key.
		if ( ! empty( $row ) ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE {$wpdb->prefix}give_customers SET verify_key = '', token = %s, verify_throttle = %s WHERE id = %d LIMIT 1", $token, $now, $row->id )
			);

			$this->token_email = $row->email;
			$this->token       = $token;

			return true;
		}

		return false;
	}

	/**
	 * Users donations args
	 *
	 * Force Give to find donations by email, not user ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $args array User Donations arguments.
	 *
	 * @return mixed
	 */
	public function users_donations_args( $args ) {
		$args['user'] = $this->token_email;

		return $args;
	}

	/**
	 * Create בolumns
	 *
	 * Create the necessary columns for email access
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function create_columns() {

		global $wpdb;

		// Create columns in customers table
		$query = $wpdb->query( "ALTER TABLE {$wpdb->prefix}give_customers ADD `token` VARCHAR(255) CHARACTER SET utf8 NOT NULL, ADD `verify_key` VARCHAR(255) CHARACTER SET utf8 NOT NULL AFTER `token`, ADD `verify_throttle` DATETIME NOT NULL AFTER `verify_key`" );

		// Columns added properly
		if ( $query ) {
			give_update_option( 'email_access_installed', 1 );
		}

	}

}
