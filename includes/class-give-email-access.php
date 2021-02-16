<?php
/**
 * Email Access
 *
 * @package     Give
 * @subpackage  Classes/Give_Email_Access
 * @copyright   Copyright (c) 2016, GiveWP
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
	 * @access public
	 *
	 * @var
	 */
	public $verify_throttle;

	/**
	 * Limit throttle
	 *
	 * @since  1.8.17
	 * @access public
	 *
	 * @var
	 */
	public $limit_throttle;

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

		// Get it started.
		add_action( 'wp', [ $this, 'setup' ] );
	}

	/**
	 * Setup hooks
	 *
	 * @since 2.4.0
	 */
	public function setup() {

		$is_email_access_on_page = apply_filters( 'give_is_email_access_on_page', give_is_success_page() || give_is_history_page() );

		if ( $is_email_access_on_page ) {
			// Get it started.
			add_action( 'wp', [ $this, 'init' ], 14 );
		}
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

		// Bail Out, if user is logged in.
		if ( is_user_logged_in() ) {
			return;
		}

		// Are db columns setup?
		$column_exists = Give()->donors->does_column_exist( 'token' );
		if ( ! $column_exists ) {
			$this->create_columns();
		}

		// Timeouts.
		$this->verify_throttle  = apply_filters( 'give_nl_verify_throttle', 300 );
		$this->limit_throttle   = apply_filters( 'give_nl_limit_throttle', 3 );
		$this->token_expiration = apply_filters( 'give_nl_token_expiration', 7200 );

		// Setup login.
		$this->check_for_token();

		if ( $this->token_exists ) {
			add_filter( 'give_user_pending_verification', '__return_false' );
			add_filter( 'give_get_users_donations_args', [ $this, 'users_donations_args' ] );
		}

	}

	/**
	 * Prevent email spamming.
	 *
	 * @param int $donor_id Donor ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function can_send_email( $donor_id ) {

		$donor = Give()->donors->get_donor_by( 'id', $donor_id );

		if ( is_object( $donor ) ) {

			$email_throttle_count = (int) give_get_meta( $donor_id, '_give_email_throttle_count', true );

			$cache_key = "give_cache_email_throttle_limit_exhausted_{$donor_id}";
			if (
				$email_throttle_count < $this->limit_throttle &&
				true !== Give_Cache::get( $cache_key )
			) {
				give_update_meta( $donor_id, '_give_email_throttle_count', $email_throttle_count + 1 );
			} else {
				give_update_meta( $donor_id, '_give_email_throttle_count', 0 );
				Give_Cache::set( $cache_key, true, $this->verify_throttle );
				return false;
			}
		}

		return true;
	}

	/**
	 * Send the user's token
	 *
	 * @param int    $donor_id Donor id.
	 * @param string $email    Donor email.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function send_email( $donor_id, $email ) {
		return apply_filters( 'give_email-access_email_notification', $donor_id, $email );
	}

	/**
	 * This function is used to fetch the token value from query string or cookies based on availability.
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @return string
	 */
	public function get_token() {

		$token = isset( $_GET['give_nl'] ) ? give_clean( $_GET['give_nl'] ) : '';

		// Check for cookie.
		if ( empty( $token ) ) {
			$token = isset( $_COOKIE['give_nl'] ) ? give_clean( $_COOKIE['give_nl'] ) : '';
		}

		return $token;
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

		$token = $this->get_token();

		// Must have a token.
		if ( ! empty( $token ) ) {

			if ( ! $this->is_valid_token( $token ) ) {
				if ( ! $this->is_valid_verify_key( $token ) ) {
					return false;
				}
			}

			// Set Receipt Access Session.
			Give()->session->maybe_start_session();
			Give()->session->set( 'receipt_access', true );
			$this->token_exists = true;

			// Set cookie.
			$lifetime = current_time( 'timestamp' ) + Give()->session->set_expiration_time();
			@setcookie( 'give_nl', $token, $lifetime, COOKIEPATH, COOKIE_DOMAIN, false );

			return true;
		}

		return false;
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
			$wpdb->prepare( "SELECT email FROM {$wpdb->donors} WHERE verify_key = %s AND verify_throttle >= %s LIMIT 1", $token, $expires )
		);

		if ( ! empty( $email ) ) {
			$this->token_email = $email;
			$this->token       = $token;
			return true;
		}

		// Set error only if email access form isn't being submitted.
		if (
			! isset( $_POST['give_email'] ) &&
			! isset( $_POST['_wpnonce'] )
		) {
			give_set_error( 'give_email_token_expired', apply_filters( 'give_email_token_expired_message', __( 'Your access token has expired. Please request a new one.', 'give' ) ) );
		}

		return false;

	}

	/**
	 * Add the verify key to DB
	 *
	 * @param int    $donor_id   Donor id.
	 * @param string $email      Donor email.
	 * @param string $verify_key The verification key.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function set_verify_key( $donor_id, $email, $verify_key ) {
		global $wpdb;

		$now = date( 'Y-m-d H:i:s' );

		// Insert or update?
		$row_id = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$wpdb->donors} WHERE id = %d LIMIT 1", $donor_id )
		);

		// Update.
		if ( ! empty( $row_id ) ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE {$wpdb->donors} SET verify_key = %s, verify_throttle = %s WHERE id = %d LIMIT 1", $verify_key, $now, $row_id )
			);
		} // Insert.
		else {
			$wpdb->query(
				$wpdb->prepare( "INSERT INTO {$wpdb->donors} ( verify_key, verify_throttle) VALUES (%s, %s)", $verify_key, $now )
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
			$wpdb->prepare( "SELECT id, email FROM {$wpdb->donors} WHERE verify_key = %s LIMIT 1", $token )
		);

		$now = date( 'Y-m-d H:i:s' );

		// Set token and remove verify key.
		if ( ! empty( $row ) ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE {$wpdb->donors} SET verify_key = '', token = %s, verify_throttle = %s WHERE id = %d LIMIT 1", $token, $now, $row->id )
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
	 * Create required columns
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

		// Create columns in donors table.
		$wpdb->query( "ALTER TABLE {$wpdb->donors} ADD `token` VARCHAR(255) CHARACTER SET utf8 NOT NULL, ADD `verify_key` VARCHAR(255) CHARACTER SET utf8 NOT NULL AFTER `token`, ADD `verify_throttle` DATETIME NOT NULL AFTER `verify_key`" );
	}
}
