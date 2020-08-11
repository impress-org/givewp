<?php
/**
 * Session
 *
 * @since       1.0
 * @subpackage  Classes/Give_Session
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @package     Give
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
	private static $instance;

	/**
	 * Holds our session data
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    array
	 */
	private $session = [];

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
	private $session_expiring = false;

	/**
	 * Session expiration time
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @var    string
	 */
	private $session_expiration = false;

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
	 * Expiration Time
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @var    string
	 */
	private $nonce_cookie_name = '';

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
		$this->exp_option = give_get_option( 'session_lifetime' );
		$this->exp_option = ! empty( $this->exp_option )
			? $this->exp_option
			: 30 * 60 * 24; // Default expiration time is 12 hours

		$this->set_cookie_name();
		$cookie = $this->get_session_cookie();

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

			// Load session.
			$this->session = $this->get_session_data();

		} else {
			$this->generate_donor_id();
		}

		add_action( 'give_process_donation_after_validation', [ $this, 'maybe_start_session' ] );
		add_action( 'wp_login', [ $this, 'startSessionWhenLoginAsWPUser' ], 10, 2 );

		add_action( 'shutdown', [ $this, 'save_data' ], 20 );
		add_action( 'wp_logout', [ $this, 'destroy_session' ] );

		if ( ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', [ $this, '__nonce_user_logged_out' ] );
		}

		// Remove old sessions.
		Give_Cron::add_daily_event( [ $this, '__cleanup_sessions' ] );
	}

	/**
	 * Get session data
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) Give()->session_db->get_session( $this->donor_id, [] ) : [];
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
		$session      = [];
		$cookie_value = isset( $_COOKIE[ $this->cookie_name ] ) ? give_clean( $_COOKIE[ $this->cookie_name ] ) : $this->__handle_ajax_cookie(); // @codingStandardsIgnoreLine.

		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return $session;
		}

		list( $donor_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $cookie_value );

		if ( empty( $donor_id ) ) {
			return $session;
		}

		require_once ABSPATH . WPINC . '/pluggable.php';

		// Validate hash.
		$to_hash = $donor_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return $session;
		}

		/**
		 * Filter the session cookie data
		 *
		 * @since 2.2.6
		 */
		$cookie_data = apply_filters(
			'give_get_session_cookie',
			[ $donor_id, $session_expiration, $session_expiring, $cookie_hash ]
		);

		return $cookie_data;
	}


	/**
	 * Load session cookie by ajax
	 *
	 * @since 2.2.6
	 * @access private
	 *
	 * @return array|bool|string
	 */
	private function __handle_ajax_cookie() {
		$cookie = false;

		// @see https://github.com/impress-org/give/issues/3705
		if (
			empty( $cookie )
			&& wp_doing_ajax()
			&& isset( $_GET['action'] )
			&& 'get_receipt' === $_GET['action']
		) {
			$cookie = isset( $_GET[ $this->cookie_name ] ) ? give_clean( $_GET[ $this->cookie_name ] ) : false;
		}

		return $cookie;
	}


	/**
	 * Check if session exist for specific session id
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return bool
	 */
	public function has_session() {
		return $this->has_cookie;
	}

	/**
	 * Set cookie name
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @return void
	 */
	private function set_cookie_name() {
		/**
		 * Filter the cookie name
		 *
		 * @since 2.2.0
		 *
		 * @param string $cookie_name Cookie name.
		 * @param string $cookie_type Cookie type session or nonce.
		 */
		$this->cookie_name = apply_filters(
			'give_session_cookie',
			'wp-give_session_' . COOKIEHASH, // Cookie name.
			'session' // Cookie type.
		);

		$this->nonce_cookie_name = apply_filters(
			'give_session_cookie',
			'wp-give_session_reset_nonce_' . COOKIEHASH, // Cookie name.
			'nonce' // Cookie type.
		);
	}

	/**
	 * Get Session
	 *
	 * Retrieve session variable for a given session key.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $key Session key.
	 * @param mixed  $default default value.
	 *
	 * @return string|array      Session variable.
	 */
	public function get( $key, $default = false ) {
		$key = sanitize_key( $key );

		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : $default;
	}

	/**
	 * Set Session
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $key Session key.
	 * @param mixed  $value Session variable.
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
			give_setcookie( $this->nonce_cookie_name, '1', $this->session_expiration, apply_filters( 'give_session_use_secure_cookie', false ) );
		}
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
	 * Get Session Expiration
	 *
	 * Looks at the session cookies and returns the expiration date for this session if applicable
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @return string|bool Formatted expiration date string.
	 */
	public function get_session_expiration() {
		return $this->has_session() ? $this->session_expiration : false;
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
	 * Setup donor session when authorized by WP user credentials
	 *
	 * @since 2.7.0
	 *
	 * @param WP_User $wpUser
	 * @param string  $wpUserLogin
	 */
	public function startSessionWhenLoginAsWPUser( $wpUserLogin, $wpUser ) {
		$donor = Give()->donors->get_donor_by( 'user_id', $wpUser->ID );

		// Setup session only if donor exist for specific WP user.
		if ( $donor ) {
			$this->maybe_start_session();
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
	 * @since  2.2.0
	 * @access public
	 */
	public function save_data() {
		// Dirty if something changed - prevents saving nothing new.
		if ( $this->session_data_changed && $this->has_session() ) {
			global $wpdb;

			Give()->session_db->__replace(
				Give()->session_db->table_name,
				[
					'session_key'    => $this->donor_id,
					'session_value'  => maybe_serialize( $this->session ),
					'session_expiry' => $this->session_expiration,
				],
				[
					'%s',
					'%s',
					'%d',
				]
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
		give_setcookie( $this->nonce_cookie_name, '', time() - YEAR_IN_SECONDS, apply_filters( 'give_session_use_secure_cookie', false ) );

		Give()->session_db->delete_session( $this->donor_id );

		$this->session              = [];
		$this->session_data_changed = false;

		$this->generate_donor_id();
	}

	/**
	 * Delete nonce cookie if generating fresh form html.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_delete_nonce_cookie() {
		$value = false;

		if ( Give()->session->has_session() ) {
			$value = true;
		}

		return $value;
	}

	/**
	 * Get cookie names
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param string $type Nonce type.
	 *
	 * @return string Cookie name
	 */
	public function get_cookie_name( $type = '' ) {
		$name = '';

		switch ( $type ) {
			case 'nonce':
				$name = $this->nonce_cookie_name;
				break;

			case 'session':
				$name = $this->cookie_name;
				break;
		}

		return $name;
	}

	/**
	 * When a user is logged out, ensure they have a unique nonce by using the donor/session ID.
	 * Note: for internal logic only.
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param int $uid User ID.
	 *
	 * @return string
	 */
	public function __nonce_user_logged_out( $uid ) {
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


	/**
	 * Get Session ID
	 *
	 * Retrieve session ID.
	 *
	 * @since      1.0
	 * @return string Session ID.
	 * @deprecated 2.2.0
	 * @access     public
	 *
	 */
	public function get_id() {
		return $this->get_cookie_name( 'session' );
	}

	/**
	 * Set Cookie Variant Time
	 *
	 * Force the cookie expiration variant time to custom expiration option, less and hour. defaults to 23 hours
	 * (set_expiration_variant_time used in WP_Session).
	 *
	 * @since      1.0
	 * @return int
	 * @deprecated 2.2.0
	 * @access     public
	 *
	 */
	public function set_expiration_variant_time() {

		return ( ! empty( $this->exp_option ) ? ( intval( $this->exp_option ) - 3600 ) : 30 * 60 * 23 );
	}

	/**
	 * Starts a new session if one has not started yet.
	 *
	 * Checks to see if the server supports PHP sessions or if the GIVE_USE_PHP_SESSIONS constant is defined.
	 *
	 * @since      1.0
	 * @access     public
	 * @return bool $ret True if we are using PHP sessions, false otherwise.
	 * @deprecated 2.2.0
	 *
	 */
	public function use_php_sessions() {
		$ret = false;

		give_doing_it_wrong( __FUNCTION__, __( 'We are using database session logic instead of PHP session since GiveWP 2.2.0', 'give' ) );

		return (bool) apply_filters( 'give_use_php_sessions', $ret );
	}

	/**
	 * Should Start Session
	 *
	 * Determines if we should start sessions.
	 *
	 * @since      1.4
	 * @access     public
	 * @return bool
	 * @deprecated 2.2.0
	 *
	 */
	public function should_start_session() {

		$start_session = true;

		give_doing_it_wrong( __FUNCTION__, __( 'We are using database session logic instead of PHP session since GiveWP 2.2.0', 'give' ) );

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {  // @codingStandardsIgnoreLine

			$blacklist = apply_filters(
				'give_session_start_uri_blacklist',
				[
					'feed',
					'feed',
					'feed/rss',
					'feed/rss2',
					'feed/rdf',
					'feed/atom',
					'comments/feed/',
				]
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
}
