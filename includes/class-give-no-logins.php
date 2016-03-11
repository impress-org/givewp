<?php
/**
 * Class for allowing donors access to their history w/o logging in; Based on the work from Matt Gibbs
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.7
 */

defined( 'ABSPATH' ) or exit;

class Give_No_Logins {

	public $token_exists = false;
	public $token_email = false;
	public $token = false;
	public $error = '';

	private static $instance;


	function __construct() {

		// setup variables
		define( 'EDDNL_VERSION', '0.3' );
		define( 'EDDNL_DIR', dirname( __FILE__ ) );
		define( 'EDDNL_URL', plugins_url( basename( EDDNL_DIR ) ) );
		define( 'EDDNL_BASENAME', plugin_basename( __FILE__ ) );

		// get the gears turning
		add_action( 'init', array( $this, 'init' ), 8 );
	}


	/**
	 * Register defaults and filters
	 */
	function init() {
		if ( is_user_logged_in() ) {
			return;
		}

		// Setup the DB table
//		include( EDDNL_DIR . '/includes/class-upgrade.php' );

		//INSTALL ROUGH My_SQL:
		//ALTER TABLE `wp_give_customers` ADD `nl_token` VARCHAR(255) NOT NULL AFTER `date_created`, ADD `nl_verify_key` VARCHAR(255) NOT NULL AFTER `nl_token`, ADD `nl_date_added` DATETIME NOT NULL AFTER `nl_verify_key`;


		// Timeouts
		$this->verify_throttle  = apply_filters( 'give_nl_verify_throttle', 300 );
		$this->token_expiration = apply_filters( 'give_nl_token_expiration', 7200 );

		// Setup login
		$this->load_textdomain();
		$this->check_for_token();

		if ( $this->token_exists ) {
			add_filter( 'give_can_view_receipt', '__return_true' );
			add_filter( 'give_user_pending_verification', '__return_false' );
			add_filter( 'give_get_success_page_uri', array( $this, 'give_success_page_uri' ) );
			add_filter( 'give_get_users_purchases_args', array( $this, 'users_purchases_args' ) );
			add_filter( 'give_payment_user_id', array( $this, 'give_payment_user_id' ) );
		} else {
			add_action( 'get_template_part_history', array( $this, 'login' ), 10, 2 );
		}
	}


	/**
	 * Search for a customer ID by purchase email
	 *
	 * @param $email
	 *
	 * @return int
	 */
	function get_customer_id( $email ) {
		global $wpdb;

		$customer_id = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}give_customers WHERE email = %s", $email )
		);

		return $customer_id;
	}


	/**
	 * Prevent email spamming
	 */
	function can_send_email( $customer_id ) {
		global $wpdb;

		// Prevent multiple emails within X minutes
		$throttle = date( 'Y-m-d H:i:s', time() - $this->verify_throttle );

		// Does a user row exist?
		$exists = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}give_nl_tokens WHERE customer_id = %d", $customer_id )
		);

		if ( 0 < $exists ) {
			$row_id = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}give_nl_tokens WHERE customer_id = %d AND (added < %s OR verify_key = '') LIMIT 1", $customer_id, $throttle )
			);

			if ( $row_id < 1 ) {
				EDDNL()->error = __( 'Please wait a few minutes before requesting a new token', 'give_nl' );

				return false;
			}
		}

		return true;
	}


	/**
	 * Send the user's token
	 */
	function send_email( $customer_id, $email ) {
		$verify_key = wp_generate_password( 20, false );

		// Generate a new verify key
		$this->set_verify_key( $customer_id, $email, $verify_key );

		// Get the purchase history URL
		$page_id  = give_get_option( 'purchase_history_page' );
		$page_url = get_permalink( $page_id );

		// Send the email
		$subject = __( 'Your access token', 'give_nl' );
		$message = "$page_url?give_nl=$verify_key";
		wp_mail( $email, $subject, $message );
	}


	/**
	 * Has the user authenticated?
	 */
	function check_for_token() {

		$token = isset( $_GET['give_nl'] ) ? $_GET['give_nl'] : '';

		// Check for cookie
		if ( empty( $token ) ) {
			$token = isset( $_COOKIE['give_nl'] ) ? $_COOKIE['give_nl'] : '';
		}

		if ( ! empty( $token ) ) {
			if ( ! $this->is_valid_token( $token ) ) {
				if ( ! $this->is_valid_verify_key( $token ) ) {
					return;
				}
			}

			$this->token_exists = true;

			// Set cookie
			setcookie( 'give_nl', $token );

			// Simulate a user login
			$user = get_user_by( 'login', 'give_nl' );

			if ( $user ) {
				$user_id = $user->ID;
			} else {
				$user_id = wp_create_user( 'give_nl', wp_generate_password( 32 ), 'give_nl@facetwp.com' );
				update_user_meta( $user_id, 'show_admin_bar_front', false );
				update_user_meta( $user_id, 'wp_capabilities', '' );
				update_user_meta( $user_id, 'wp_user_level', 0 );
			}

			wp_set_current_user( $user_id );
		}
	}


	/**
	 * Add the verify key to DB
	 */
	function set_verify_key( $customer_id, $email, $verify_key ) {
		global $wpdb;

		$now = date( 'Y-m-d H:i:s' );

		// Insert or update?
		$row_id = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}give_nl_tokens WHERE customer_id = %d LIMIT 1", $customer_id )
		);

		// Update
		if ( ! empty( $row_id ) ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE {$wpdb->prefix}give_nl_tokens SET verify_key = %s, added = %s WHERE id = %d LIMIT 1", $verify_key, $now, $row_id )
			);
		} // Insert
		else {
			$wpdb->query(
				$wpdb->prepare( "INSERT INTO {$wpdb->prefix}give_nl_tokens (customer_id, email, verify_key, added) VALUES (%d, %s, %s, %s)", $customer_id, $email, $verify_key, $now )
			);
		}
	}


	/**
	 * Is this a valid token?
	 */
	function is_valid_token( $token ) {
		global $wpdb;

		// Make sure token isn't expired
		$expires = date( 'Y-m-d H:i:s', time() - $this->token_expiration );

		$email = $wpdb->get_var(
			$wpdb->prepare( "SELECT email FROM {$wpdb->prefix}give_nl_tokens WHERE token = %s AND added >= %s LIMIT 1", $token, $expires )
		);

		if ( ! empty( $email ) ) {
			$this->token_email = $email;
			$this->token       = $token;

			return true;
		}

		EDDNL()->error = __( 'That token has expired', 'give_nl' );

		return false;
	}


	/**
	 * Is this a valid verify key?
	 */
	function is_valid_verify_key( $token ) {
		global $wpdb;

		// See if the verify_key exists
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT id, email FROM {$wpdb->prefix}give_nl_tokens WHERE verify_key = %s LIMIT 1", $token )
		);

		$now = date( 'Y-m-d H:i:s' );

		// Set token
		if ( ! empty( $row ) ) {
			$wpdb->query(
				$wpdb->prepare( "UPDATE {$wpdb->prefix}give_nl_tokens SET verify_key = '', token = %s, added = %s WHERE id = %d LIMIT 1", $token, $now, $row->id )
			);

			$this->token_email = $row->email;
			$this->token       = $token;

			return true;
		}

		return false;
	}


	/**
	 * Show the email login form
	 */
	function login( $slug = 'history', $name = 'purchases' ) {
		give_get_template_part( 'session-refresh-form' );
	}


	/**
	 * Append the token to Give purchase links
	 *
	 * @param $uri
	 *
	 * @return string
	 */
	function give_success_page_uri( $uri ) {
		if ( $this->token_exists ) {
			return add_query_arg( array( 'give_nl' => $this->token ), $uri );
		}
	}


	/**
	 *
	 * Trick Give into thinking we're logged in
	 *
	 * @param $user_id
	 *
	 * @return int
	 */
	function give_payment_user_id( $user_id ) {
		return get_current_user_id();
	}


	/**
	 * Force Give to find transactions by purchase email, not user ID
	 */
	function users_purchases_args( $args ) {
		$args['user'] = $this->token_email;

		return $args;
	}


}
