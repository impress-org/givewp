<?php
/**
 * Session
 *
 * @package     Give
 * @subpackage  Classes/Give_Session
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Session
 */
class Give_Session {
	/**
	 * Instance.
	 *
	 * @since  2.2.0
	 * @access private
	 * @var Give_Session
	 */
	static private $instance;

	/**
	 * Holds our session data
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    array
	 */
	private $session = array();

	/**
	 * Holds our session data
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    string
	 */
	private $session_data_changed = false;

	/**
	 * Whether to use PHP $_SESSION or WP_Session
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    bool
	 */
	private $use_php_sessions = false;

	/**
	 * Cookie Name
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    string
	 */
	private $cookie_name = '';

	/**
	 * Donor Unique ID
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    string
	 */
	private $donor_id = '';

	/**
	 * Session expiring time
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @var    string
	 */
	private $session_expiring;

	/**
	 * Session expiration time
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @var    string
	 */
	private $session_expiration;

	/**
	 * Flag to check if donor has cookie or not
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @var    bool
	 */
	private $has_cookie = false;

	/**
	 * Expiration Time
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    int
	 */
	private $exp_option = false;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.2.0
	 * @access public
	 * @return Give_Session
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->__setup();
		}

		return self::$instance;
	}

	/**
	 * Setup
	 *
	 * @since  2.2.0
	 * @access public
	 */
	private function __setup() {  // @codingStandardsIgnoreLine
		// deprecated
		$this->use_php_sessions = $this->use_php_sessions(); // @todo: check this option.

		$this->exp_option       = give_get_option( 'session_lifetime' );
		$this->exp_option = ! empty( $this->exp_option )
			? $this->exp_option
			: 30 * 60 * 24; // Default expiration time is 12 hours

		$this->cookie_name = $this->get_cookie_name();
		$cookie            = $this->get_session_cookie();

		if ( ! empty( $cookie ) ) {
			$this->donor_id           = $cookie[0];
			$this->session_expiration = $cookie[1];
			$this->session_expiring   = $cookie[2];
			$this->has_cookie         = true;

			// Update session if its close to expiring.
			if ( time() > $this->session_expiring ) {
				$this->set_expiration_time();
				Give()->session_db->update_session_timestamp( $this->donor_id, $this->session_expiration );
			}

			$this->load_donor_session();
		} else {
			$this->generate_donor_id();
		}

		add_action( 'give_pre_process_donation', array( $this, '__setup_donor_session' ) );

		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy_session' ) );

		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'nonce_user_logged_out' ) );
		}

		// Remove old sessions.
		Give_Cron::add_daily_event( array( $this, '__cleanup_sessions' ) );
	}

	/**
	 * Load donor session
	 *
	 * @since  2.2.0
	 * @access public
	 */
	private function load_donor_session() {
		$this->session = $this->get_session_data();
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) Give()->session_db->get_session( $this->donor_id, array() ) : array();
	}


	/**
	 * Get session by session id
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_session_cookie() {
		$session      = array();
		$cookie_value = isset( $_COOKIE[ $this->cookie_name ] ) ? give_clean( $_COOKIE[ $this->cookie_name ] ) : false; // @codingStandardsIgnoreLine.

		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return $session;
		}

		list( $donor_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $cookie_value );

		if ( empty( $donor_id ) ) {
			return $session;
		}

		// Validate hash.
		$to_hash = $donor_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return $session;
		}

		return array( $donor_id, $session_expiration, $session_expiring, $cookie_hash );
	}


	/**
	 * Check if session exist for specific session id
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return bool
	 */
	private function has_session() {
		return $this->has_cookie;
	}

	/**
	 * Get Session ID
	 *
	 * Retrieve session ID.
	 *
	 * @since      1.0
	 * @deprecated 2.2.0
	 * @access     public
	 *
	 * @return string Session ID.
	 */
	public function get_id() {
		return $this->get_cookie_name();
	}

	/**
	 * Get Session name
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @return string Cookie name.
	 */
	private function get_cookie_name() {

		/**
		 * Filter the cookie name
		 *
		 * @since  2.2.0
		 */
		return apply_filters( 'give_cookie', 'wp_give_session_' . COOKIEHASH );
	}

	/**
	 * Get Session
	 *
	 * Retrieve session variable for a given session key.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $key     Session key.
	 * @param mixed  $default default value.
	 *
	 * @return string|array      Session variable.
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );

		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : $default;
	}

	/**
	 * Set Session
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $key   Session key.
	 * @param  mixed  $value Session variable.
	 *
	 * @return string        Session variable.
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->session[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->session_data_changed            = true;
		}

		return $this->session[ $key ];
	}

	/**
	 * Set Session Cookies
	 *
	 * Cookies are used to increase the session lifetime using the give setting. This is helpful for when a user closes
	 * their browser after making a donation and comes back to the site.
	 *
	 * @since  1.4
	 * @access public
	 *
	 * @param bool $set Flag to check if set cookie or not.
	 */
	public function set_session_cookies( $set ) {
		if ( $set ) {
			$this->set_expiration_time();

			$to_hash          = $this->donor_id . '|' . $this->session_expiration;
			$cookie_hash      = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value     = $this->donor_id . '||' . $this->session_expiration . '||' . $this->session_expiring . '||' . $cookie_hash;
			$this->has_cookie = true;

			give_setcookie( $this->cookie_name, $cookie_value, $this->session_expiration, apply_filters( 'give_session_use_secure_cookie', false ) );
		}
	}

	/**
	 * Set Cookie Variant Time
	 *
	 * Force the cookie expiration variant time to custom expiration option, less and hour. defaults to 23 hours
	 * (set_expiration_variant_time used in WP_Session).
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return int
	 */
	public function set_expiration_variant_time() {

		return ( ! empty( $this->exp_option ) ? ( intval( $this->exp_option ) - 3600 ) : 30 * 60 * 23 );
	}

	/**
	 * Set Cookie Expiration
	 *
	 * Force the cookie expiration time if set, default to 24 hours.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return int
	 */
	public function set_expiration_time() {
		$this->session_expiring   = time() + intval( apply_filters( 'give_session_expiring', ( $this->exp_option - 3600 ) ) ); // Default 11 Hours.
		$this->session_expiration = time() + intval( apply_filters( 'give_session_expiration', $this->exp_option ) ); // Default 12 Hours.

		return $this->session_expiration;
	}

	/**
	 * Starts a new session if one has not started yet.
	 *
	 * Checks to see if the server supports PHP sessions or if the GIVE_USE_PHP_SESSIONS constant is defined.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool $ret True if we are using PHP sessions, false otherwise.
	 */
	public function use_php_sessions() {

		$ret = false;

		// If the database variable is already set, no need to run auto detection.
		$give_use_php_sessions = (bool) get_option( 'give_use_php_sessions' );

		if ( ! $give_use_php_sessions ) {

			// Attempt to detect if the server supports PHP sessions.
			if ( function_exists( 'session_start' ) && ! ini_get( 'safe_mode' ) ) {

				$this->set( 'give_use_php_sessions', 1 );

				if ( $this->get( 'give_use_php_sessions' ) ) {

					$ret = true;

					// Set the database option.
					update_option( 'give_use_php_sessions', true );

				}
			}
		} else {

			$ret = $give_use_php_sessions;
		}

		// Enable or disable PHP Sessions based on the GIVE_USE_PHP_SESSIONS constant.
		if ( defined( 'GIVE_USE_PHP_SESSIONS' ) && GIVE_USE_PHP_SESSIONS ) {
			$ret = true;
		} elseif ( defined( 'GIVE_USE_PHP_SESSIONS' ) && ! GIVE_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'give_use_php_sessions', $ret );
	}

	/**
	 * Should Start Session
	 *
	 * Determines if we should start sessions.
	 *
	 * @since  1.4
	 * @access public
	 *
	 * @return bool
	 */
	public function should_start_session() {

		$start_session = true;

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {  // @codingStandardsIgnoreLine

			$blacklist = apply_filters(
				'give_session_start_uri_blacklist', array(
					'feed',
					'feed',
					'feed/rss',
					'feed/rss2',
					'feed/rdf',
					'feed/atom',
					'comments/feed/',
				)
			);
			$uri       = ltrim( $_SERVER['REQUEST_URI'], '/' ); // // @codingStandardsIgnoreLine
			$uri       = untrailingslashit( $uri );
			if ( in_array( $uri, $blacklist, true ) ) {
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
	 * Get Session Expiration
	 *
	 * Looks at the session cookies and returns the expiration date for this session if applicable
	 *
	 * @todo   Review this
	 *
	 * @access public
	 *
	 * @return string Formatted expiration date string.
	 */
	public function get_session_expiration() {

		$expiration = false;

		if ( session_id() && isset( $_COOKIE[ session_name() . '_expiration' ] ) ) {
			$expiration = date( 'D, d M Y h:i:s', intval( $_COOKIE[ session_name() . '_expiration' ] ) );
		}

		return $expiration;

	}

	/**
	 * Start donor session when donation processing starts
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function __setup_donor_session() { // @codingStandardsIgnoreLine
		// Processing donation instead of just validating donation data
		if ( ! isset( $_POST['give_ajax'] ) ) { //// @codingStandardsIgnoreLine
			$this->maybe_start_session();
		}
	}

	/**
	 * Maybe Start Session
	 *
	 * Starts a new session if one hasn't started yet.
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function maybe_start_session() {
		if (
			! headers_sent()
			&& empty( $this->session )
			&& ! $this->has_cookie
		) {
			$this->set_session_cookies( true );
		}
	}

	/**
	 * Generate a unique donor ID.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function generate_donor_id() {
		require_once ABSPATH . 'wp-includes/class-phpass.php';

		$hasher         = new PasswordHash( 8, false );
		$this->donor_id = md5( $hasher->get_random_bytes( 32 ) );
	}

	/**
	 * Save donor session data
	 *
	 * @todo   use session db
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function save_data() {
		// Dirty if something changed - prevents saving nothing new.
		if ( $this->session_data_changed && $this->has_session() ) {
			global $wpdb;

			Give()->session_db->__replace(
				Give()->session_db->table_name,
				array(
					'session_key'    => $this->donor_id,
					'session_value'  => maybe_serialize( $this->session ),
					'session_expiry' => $this->session_expiration,
				),
				array(
					'%s',
					'%s',
					'%d',
				)
			);

			$this->session_data_changed = false;
		}
	}

	/**
	 * Destroy all session data.
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function destroy_session() {
		give_setcookie( $this->cookie_name, '', time() - YEAR_IN_SECONDS, apply_filters( 'give_session_use_secure_cookie', false ) );

		Give()->session_db->delete_session( $this->donor_id );

		$this->session              = array();
		$this->session_data_changed = false;
		$this->donor_id             = $this->generate_donor_id();
	}

	/**
	 * When a user is logged out, ensure they have a unique nonce by using the donor/session ID.
	 *
	 * @todo: review this
	 *
	 * @param int $uid User ID.
	 *
	 * @return string
	 */
	public function nonce_user_logged_out( $uid ) {
		return $this->has_session() && $this->donor_id ? $this->donor_id : $uid;
	}


	/**
	 * Cleanup session data from the database and clear caches.
	 * Note: for internal logic only.
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function __cleanup_sessions() { // @codingStandardsIgnoreLine
		Give()->session_db->delete_expired_sessions();
	}
}
