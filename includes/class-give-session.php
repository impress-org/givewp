<?php
/**
 * Give Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of Give sessions
 *
 * @package     Give
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Session Class
 *
 * @since 1.0
 */
class Give_Session {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 * @since  1.0
	 */
	private $session;


	/**
	 * Whether to use PHP $_SESSION or WP_Session
	 *
	 * @var bool
	 * @access private
	 * @since  1.0,1
	 */
	private $use_php_sessions = false;

	/**
	 * Session index prefix
	 *
	 * @var string
	 * @access private
	 * @since  1.0
	 */
	private $prefix = '';

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->use_php_sessions = $this->use_php_sessions();

		if ( $this->use_php_sessions ) {

			if ( is_multisite() ) {

				$this->prefix = '_' . get_current_blog_id();

			}

			// Use PHP SESSION (must be enabled via the GIVE_USE_PHP_SESSIONS constant)
			add_action( 'init', array( $this, 'maybe_start_session' ), - 2 );

		} else {

			// Use WP_Session (default)

			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'give_wp_session' );
			}

			if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once GIVE_PLUGIN_DIR . 'includes/libraries/class-recursive-arrayaccess.php';
			}

			if ( ! class_exists( 'WP_Session' ) ) {
				require_once GIVE_PLUGIN_DIR . 'includes/libraries/class-wp-session.php';
				require_once GIVE_PLUGIN_DIR . 'includes/libraries/wp-session.php';
			}

			add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
			add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		}

		if ( empty( $this->session ) && ! $this->use_php_sessions ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		} else {
			add_action( 'init', array( $this, 'init' ), -1 );
		}

	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function init() {

		if ( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION[ 'give' . $this->prefix ] ) && is_array( $_SESSION[ 'give' . $this->prefix ] ) ? $_SESSION[ 'give' . $this->prefix ] : array();
		} else {
			$this->session = WP_Session::get_instance();
		}

		$purchase = $this->get( 'give_purchase' );

		if ( ! empty( $purchase ) ) {
			$this->set_cart_cookie();
		} else {
			$this->set_cart_cookie( false );
		}

		return $this->session;
	}


	/**
	 * Retrieve session ID
	 *
	 * @access public
	 * @since  1.0
	 * @return string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $key Session key
	 *
	 * @return string Session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Set a session variable
	 *
	 * @since 1.0
	 *
	 * @param $key   Session key
	 * @param $value Session variable
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		if ( $this->use_php_sessions ) {
			$_SESSION[ 'give' . $this->prefix ] = $this->session;
		}

		return $this->session[ $key ];
	}

	/**
	 * Set a cookie to identify whether the cart is empty or not
	 *
	 * This is for hosts and caching plugins to identify if caching should be disabled
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param string $set Whether to set or destroy
	 *
	 * @return void
	 */
	public function set_cart_cookie( $set = true ) {
		if ( ! headers_sent() ) {
			if ( $set ) {
				@setcookie( 'give_items_in_cart', '1', time() + 30 * 60, COOKIEPATH, COOKIE_DOMAIN, false );
			} else {
				@setcookie( 'give_items_in_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			}
		}
	}

	/**
	 * Force the cookie expiration variant time to 23 hours
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param int $exp Default expiration (1 hour)
	 *
	 * @return int
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( 30 * 60 * 23 );
	}

	/**
	 * Force the cookie expiration time to 24 hours
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param int $exp Default expiration (1 hour)
	 *
	 * @return int
	 */
	public function set_expiration_time( $exp ) {
		return ( 30 * 60 * 24 );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * @return null
	 * Checks to see if the server supports PHP sessions
	 * or if the GIVE_USE_PHP_SESSIONS constant is defined
	 *
	 * @access public
	 * @since  1.0
	 * @author Daniel J Griffiths
	 * @return bool $ret True if we are using PHP sessions, false otherwise
	 */
	public function use_php_sessions() {

		$ret = false;

		// If the database variable is already set, no need to run autodetection
		$give_use_php_sessions = (bool) get_option( 'give_use_php_sessions' );

		if ( ! $give_use_php_sessions ) {

			// Attempt to detect if the server supports PHP sessions
			if ( function_exists( 'session_start' ) && ! ini_get( 'safe_mode' ) ) {

				$this->set( 'give_use_php_sessions', 1 );

				if ( $this->get( 'give_use_php_sessions' ) ) {

					$ret = true;

					// Set the database option
					update_option( 'give_use_php_sessions', true );

				}

			}

		} else {
			$ret = $give_use_php_sessions;
		}

		// Enable or disable PHP Sessions based on the GIVE_USE_PHP_SESSIONS constant
		if ( defined( 'GIVE_USE_PHP_SESSIONS' ) && GIVE_USE_PHP_SESSIONS ) {
			$ret = true;
		} else if ( defined( 'GIVE_USE_PHP_SESSIONS' ) && ! GIVE_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'give_use_php_sessions', $ret );
	}

	/**
	 * Starts a new session if one hasn't started yet.
	 */
	public function maybe_start_session() {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

}

