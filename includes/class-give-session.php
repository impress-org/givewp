<?php
/**
 * Give Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of Give sessions
 *
 * @package     Give
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2016, WordImpress
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
	 * @since  1.0
	 */
	private $use_php_sessions = false;

	/**
	 * Expiration Time
	 *
	 * @var int
	 * @access private
	 * @since  1.0
	 */
	private $exp_option = false;

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
	 * @description: Defines our session constants, includes the necessary libraries and retrieves the session instance
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->use_php_sessions = $this->use_php_sessions();
		$this->exp_option       = give_get_option( 'session_lifetime' );

		//PHP Sessions
		if ( $this->use_php_sessions ) {

			if ( is_multisite() ) {

				$this->prefix = '_' . get_current_blog_id();

			}

			add_action( 'init', array( $this, 'maybe_start_session' ), - 2 );

		} else {

			if ( ! $this->should_start_session() ) {
				return;
			}

			// Use WP_Session
			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'give_wp_session' );
			}

			if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once GIVE_PLUGIN_DIR . 'includes/libraries/sessions/class-recursive-arrayaccess.php';
			}

			if ( ! class_exists( 'WP_Session' ) ) {
				require_once GIVE_PLUGIN_DIR . 'includes/libraries/sessions/class-wp-session.php';
				require_once GIVE_PLUGIN_DIR . 'includes/libraries/sessions/wp-session.php';
			}

			add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
			add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		}

		//Init Session
		if ( empty( $this->session ) && ! $this->use_php_sessions ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), - 1 );
		} else {
			add_action( 'init', array( $this, 'init' ), - 1 );
		}

		//Set cookie on Donation Completion page
		add_action( 'give_pre_process_purchase', array( $this, 'set_session_cookies' ) );

	}

	/**
	 * Setup the Session instance
	 *
	 * @access public
	 * @since  1.0
	 * @return array $this->session
	 */
	public function init() {

		if ( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION[ 'give' . $this->prefix ] ) && is_array( $_SESSION[ 'give' . $this->prefix ] ) ? $_SESSION[ 'give' . $this->prefix ] : array();
		} else {
			$this->session = WP_Session::get_instance();
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
	 * @param $key $_SESSION key
	 * @param $value $_SESSION variable
	 *
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
	 * Set Session Cookies
	 *
	 * @description: Cookies are used to increase the session lifetime using the give setting; this is helpful for when a user closes their browser after making a donation and comes back to the site.
	 *
	 * @access public
	 * @hook
	 * @since 1.4
	 */
	public function set_session_cookies() {
		if( ! headers_sent() ) {
			$lifetime = current_time( 'timestamp' ) + $this->set_expiration_time();
			@setcookie( session_name(), session_id(), $lifetime, COOKIEPATH, COOKIE_DOMAIN, false  );
			@setcookie( session_name() . '_expiration', $lifetime, $lifetime,  COOKIEPATH, COOKIE_DOMAIN, false  );
		}
	}

	/**
	 * Set Cookie Variant Time
	 *
	 * @description Force the cookie expiration variant time to custom expiration option, less and hour; defaults to 23 hours (set_expiration_variant_time used in WP_Session)
	 *
	 * @access      public
	 * @since       1.0
	 *
	 * @return int
	 */
	public function set_expiration_variant_time() {

		return ( ! empty( $this->exp_option ) ? ( intval( $this->exp_option ) - 3600 ) : 30 * 60 * 23 );
	}

	/**
	 * Set the Cookie Expiration
	 *
	 * @description Force the cookie expiration time if set, default to 24 hours
	 *
	 * @access      public
	 * 
	 * @since       1.0
	 *
	 * @return int
	 */
	public function set_expiration_time() {

		return ( ! empty( $this->exp_option ) ? intval( $this->exp_option ) : 30 * 60 * 24 );
	}

	/**
	 * Starts a new session if one has not started yet.
	 *
	 * @return null
	 * Checks to see if the server supports PHP sessions or if the GIVE_USE_PHP_SESSIONS constant is defined
	 *
	 * @access public
	 * @since  1.0
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
	 * Determines if we should start sessions
	 *
	 * @since  1.4
	 * @return bool
	 */
	public function should_start_session() {

		$start_session = true;

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {

			$blacklist = apply_filters( 'give_session_start_uri_blacklist', array(
				'feed',
				'feed',
				'feed/rss',
				'feed/rss2',
				'feed/rdf',
				'feed/atom',
				'comments/feed/'
			) );
			$uri       = ltrim( $_SERVER['REQUEST_URI'], '/' );
			$uri       = untrailingslashit( $uri );
			if ( in_array( $uri, $blacklist ) ) {
				$start_session = false;
			}
			if ( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}
			if ( is_admin() ) {
				$start_session = false;
			}
		}

		return apply_filters( 'give_start_session', $start_session );
	}

	/**
	 * Maybe Start Session
	 *
	 * @description Starts a new session if one hasn't started yet.
	 * @see         http://php.net/manual/en/function.session-set-cookie-params.php
	 */
	public function maybe_start_session() {

		if ( ! $this->should_start_session() ) {
			return;
		}

		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

	}


	/**
	 * Get Session Expiration
	 *
	 * @description  Looks at the session cookies and returns the expiration date for this session if applicable
	 */
	public function get_session_expiration() {

		$expiration = false;

		if ( session_id() && isset( $_COOKIE[ session_name() . '_expiration' ] ) ) {

			$expiration = date( 'D, d M Y h:i:s', intval( $_COOKIE[ session_name() . '_expiration' ] ) );

		}

		return $expiration;

	}

}

