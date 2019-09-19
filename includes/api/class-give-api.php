<?php
/**
 * Give API
 *
 * A front-facing JSON/XML API that makes it possible to query donation data.
 *
 * @package     Give
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_API Class
 *
 * Renders API returns as a JSON/XML array
 *
 * @since 1.1
 */
class Give_API {

	/**
	 * Latest API Version
	 */
	const VERSION = 1;

	/**
	 * Pretty Print?
	 *
	 * @var bool
	 * @access private
	 * @since  1.1
	 */
	private $pretty_print = false;

	/**
	 * Log API requests?
	 *
	 * @var bool
	 * @access public
	 * @since  1.1
	 */
	public $log_requests = true;

	/**
	 * Is this a valid request?
	 *
	 * @var bool
	 * @access private
	 * @since  1.1
	 */
	private $is_valid_request = false;

	/**
	 * User ID Performing the API Request
	 *
	 * @var int
	 * @access public
	 * @since  1.1
	 */
	public $user_id = 0;

	/**
	 * Instance of Give Stats class
	 *
	 * @var object
	 * @access private
	 * @since  1.1
	 */
	private $stats;

	/**
	 * Response data to return
	 *
	 * @var array
	 * @access private
	 * @since  1.1
	 */
	private $data = array();

	/**
	 * Whether or not to override api key validation.
	 *
	 * @var bool
	 * @access public
	 * @since  1.1
	 */
	public $override = true;

	/**
	 * Version of the API queried
	 *
	 * @var string
	 * @access public
	 * @since  1.1
	 */
	private $queried_version;

	/**
	 * All versions of the API
	 *
	 * @var array
	 * @access protected
	 * @since  1.1
	 */
	protected $versions = array();

	/**
	 * Queried endpoint
	 *
	 * @var string
	 * @access private
	 * @since  1.1
	 */
	private $endpoint;

	/**
	 * Endpoints routes
	 *
	 * @var object
	 * @access private
	 * @since  1.1
	 */
	private $routes;

	/**
	 * Setup the Give API
	 *
	 * @since  1.1
	 * @access public
	 */
	public function __construct() {

		$this->versions = array(
			'v1' => 'GIVE_API_V1',
		);

		foreach ( $this->get_versions() as $version => $class ) {
			require_once GIVE_PLUGIN_DIR . 'includes/api/class-give-api-' . $version . '.php';
		}

		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( 'wp', array( $this, 'process_query' ), - 1 );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_action( 'show_user_profile', array( $this, 'user_key_field' ) );
		add_action( 'edit_user_profile', array( $this, 'user_key_field' ) );
		add_action( 'personal_options_update', array( $this, 'generate_api_key' ) );
		add_action( 'edit_user_profile_update', array( $this, 'generate_api_key' ) );
		add_action( 'give_process_api_key', array( $this, 'process_api_key' ) );

		// Setup a backwards compatibility check for user API Keys
		add_filter( 'get_user_metadata', array( $this, 'api_key_backwards_compat' ), 10, 4 );

		// Determine if JSON_PRETTY_PRINT is available
		$this->pretty_print = defined( 'JSON_PRETTY_PRINT' ) ? JSON_PRETTY_PRINT : null;

		// Allow API request logging to be turned off
		$this->log_requests = apply_filters( 'give_api_log_requests', $this->log_requests );

		// Setup Give_Payment_Stats instance
		$this->stats = new Give_Payment_Stats();

	}

	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 2.0
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		$deprecated_function_arr = array(
			'get_customers',
		);

		if ( in_array( $name, $deprecated_function_arr, true ) ) {
			switch ( $name ) {
				case 'get_customers':
					$args = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $this->get_donors( $args );
			}
		}
	}

	/**
	 * Registers a new rewrite endpoint for accessing the API
	 *
	 * @access public
	 *
	 * @since  1.1
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( 'give-api', EP_ALL );
	}

	/**
	 * Registers query vars for API access
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param array $vars Query vars
	 *
	 * @return string[] $vars New query vars
	 */
	public function query_vars( $vars ) {

		$vars[] = 'token';
		$vars[] = 'key';
		$vars[] = 'query';
		$vars[] = 'type';
		$vars[] = 'form';
		$vars[] = 'number';
		$vars[] = 'date';
		$vars[] = 'startdate';
		$vars[] = 'enddate';
		$vars[] = 'donor';
		$vars[] = 'format';
		$vars[] = 'id';
		$vars[] = 'purchasekey';
		$vars[] = 'email';

		return $vars;
	}

	/**
	 * Retrieve the API versions
	 *
	 * @access public
	 * @since  1.1
	 * @return array
	 */
	public function get_versions() {
		return $this->versions;
	}

	/**
	 * Retrieve the API version that was queried
	 *
	 * @access public
	 * @since  1.1
	 * @return string
	 */
	public function get_queried_version() {
		return $this->queried_version;
	}

	/**
	 * Retrieves the default version of the API to use
	 *
	 * @access public
	 * @since  1.1
	 * @return string
	 */
	public function get_default_version() {

		$version = get_option( 'give_default_api_version' );

		if ( defined( 'GIVE_API_VERSION' ) ) {
			$version = GIVE_API_VERSION;
		} elseif ( ! $version ) {
			$version = 'v1';
		}

		return $version;
	}

	/**
	 * Sets the version of the API that was queried.
	 *
	 * Falls back to the default version if no version is specified
	 *
	 * @access private
	 * @since  1.1
	 */
	private function set_queried_version() {

		global $wp_query;

		$version = $wp_query->query_vars['give-api'];

		if ( strpos( $version, '/' ) ) {

			$version = explode( '/', $version );
			$version = strtolower( $version[0] );

			$wp_query->query_vars['give-api'] = str_replace( $version . '/', '', $wp_query->query_vars['give-api'] );

			if ( array_key_exists( $version, $this->versions ) ) {

				$this->queried_version = $version;

			} else {

				$this->is_valid_request = false;
				$this->invalid_version();
			}
		} else {

			$this->queried_version = $this->get_default_version();

		}

	}

	/**
	 * Validate the API request
	 *
	 * Checks for the user's public key and token against the secret key.
	 *
	 * @access private
	 * @global object $wp_query WordPress Query
	 * @uses   Give_API::get_user()
	 * @uses   Give_API::invalid_key()
	 * @uses   Give_API::invalid_auth()
	 * @since  1.1
	 * @return bool
	 */
	private function validate_request() {
		global $wp_query;

		$this->override = false;

		// Make sure we have both user and api key
		if ( ! empty( $wp_query->query_vars['give-api'] ) && ( $wp_query->query_vars['give-api'] !== 'forms' || ! empty( $wp_query->query_vars['token'] ) ) ) {

			if ( empty( $wp_query->query_vars['token'] ) || empty( $wp_query->query_vars['key'] ) ) {
				$this->missing_auth();

				return false;
			}

			// Retrieve the user by public API key and ensure they exist
			if ( ! preg_match( '/^[a-f0-9]{32}$/i',$wp_query->query_vars['key'] ) || ! ( $user = $this->get_user( $wp_query->query_vars['key'] ) ) ) {

				$this->invalid_key();

				return false;

			} else {
				$token  = urldecode( $wp_query->query_vars['token'] );
				$secret = $this->get_user_secret_key( $user );
				$public = urldecode( $wp_query->query_vars['key'] );

				// Verify that if user has secret key or not
				if( ! $secret ) {
					$this->invalid_auth();
				}

				if ( hash_equals( md5( $secret . $public ), $token ) ) {
					$this->is_valid_request = true;
				} else {
					$this->invalid_auth();

					return false;
				}

			}
		} elseif ( ! empty( $wp_query->query_vars['give-api'] ) && $wp_query->query_vars['give-api'] === 'forms' ) {
			$this->is_valid_request = true;
			$wp_query->set( 'key', 'public' );
		}
	}

	/**
	 * Retrieve the user ID based on the public key provided
	 *
	 * @access public
	 * @since  1.1
	 * @global WPDB  $wpdb  Used to query the database using the WordPress
	 *                      Database API
	 *
	 * @param string $key   Public Key
	 *
	 * @return bool if user ID is found, false otherwise
	 */
	public function get_user( $key = '' ) {
		global $wpdb, $wp_query;

		if ( empty( $key ) ) {
			$key = urldecode( $wp_query->query_vars['key'] );
		}

		if ( empty( $key ) ) {
			return false;
		}

		$user = Give_Cache::get( md5( 'give_api_user_' . $key ), true );

		if ( false === $user ) {
			$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s AND meta_value=%s LIMIT 1", $key, 'give_user_public_key' ) );
			Give_Cache::set( md5( 'give_api_user_' . $key ), $user, DAY_IN_SECONDS, true );
		}

		if ( $user != null ) {
			$this->user_id = $user;

			return $user;
		}

		return false;
	}

	/**
	 * Get user public key.
	 *
	 * @param int $user_id
	 *
	 * @return mixed|null|string
	 */
	public function get_user_public_key( $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'give_api_user_public_key' . $user_id );
		$user_public_key = Give_Cache::get( $cache_key, true );

		if ( empty( $user_public_key ) ) {
			$user_public_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'give_user_public_key' AND user_id = %d", $user_id ) );
			Give_Cache::set( $cache_key, $user_public_key, HOUR_IN_SECONDS, true );
		}

		return $user_public_key;
	}

	/**
	 * Get user secret key.
	 *
	 * @param int $user_id
	 *
	 * @return mixed|null|string
	 */
	public function get_user_secret_key( $user_id = 0 ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return '';
		}

		$cache_key       = md5( 'give_api_user_secret_key' . $user_id );
		$user_secret_key = Give_Cache::get( $cache_key, true );

		if ( empty( $user_secret_key ) ) {
			$user_secret_key = $wpdb->get_var( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE meta_value = 'give_user_secret_key' AND user_id = %d", $user_id ) );
			Give_Cache::set( $cache_key, $user_secret_key, HOUR_IN_SECONDS, true );
		}

		return $user_secret_key;
	}

	/**
	 * Displays a missing authentication error if all the parameters are not met.
	 * provided
	 *
	 * @access private
	 * @uses   Give_API::output()
	 * @since  1.1
	 */
	private function missing_auth() {
		$error          = array();
		$error['error'] = __( 'You must specify both a token and API key.', 'give' );

		$this->data = $error;
		$this->output( 401 );
	}

	/**
	 * Displays an authentication failed error if the user failed to provide valid
	 * credentials
	 *
	 * @access private
	 * @since  1.1
	 * @uses   Give_API::output()
	 * @return void
	 */
	private function invalid_auth() {
		$error          = array();
		$error['error'] = __( 'Your request could not be authenticated.', 'give' );

		$this->data = $error;
		$this->output( 403 );
	}

	/**
	 * Displays an invalid API key error if the API key provided couldn't be
	 * validated
	 *
	 * @access private
	 * @since  1.1
	 * @uses   Give_API::output()
	 * @return void
	 */
	private function invalid_key() {
		$error          = array();
		$error['error'] = __( 'Invalid API key.', 'give' );

		$this->data = $error;
		$this->output( 403 );
	}

	/**
	 * Displays an invalid version error if the version number passed isn't valid
	 *
	 * @access private
	 * @since  1.1
	 * @uses   Give_API::output()
	 * @return void
	 */
	private function invalid_version() {
		$error          = array();
		$error['error'] = __( 'Invalid API version.', 'give' );

		$this->data = $error;
		$this->output( 404 );
	}

	/**
	 * Listens for the API and then processes the API requests
	 *
	 * @access public
	 * @global $wp_query
	 * @since  1.1
	 * @return void
	 */
	public function process_query() {

		global $wp_query;

		// Start logging how long the request takes for logging
		$before = microtime( true );

		// Check for give-api var. Get out if not present
		if ( empty( $wp_query->query_vars['give-api'] ) ) {
			return;
		}

		// Determine which version was queried
		$this->set_queried_version();

		// Determine the kind of query
		$this->set_query_mode();

		// Check for a valid user and set errors if necessary
		$this->validate_request();

		// Only proceed if no errors have been noted
		if ( ! $this->is_valid_request ) {
			return;
		}

		if ( ! defined( 'GIVE_DOING_API' ) ) {
			define( 'GIVE_DOING_API', true );
		}

		$data         = array();
		$this->routes = new $this->versions[$this->get_queried_version()];
		$this->routes->validate_request();

		switch ( $this->endpoint ) :

			case 'stats' :

				$data = $this->routes->get_stats( array(
					'type'      => isset( $wp_query->query_vars['type'] ) ? $wp_query->query_vars['type'] : null,
					'form'      => isset( $wp_query->query_vars['form'] ) ? $wp_query->query_vars['form'] : null,
					'date'      => isset( $wp_query->query_vars['date'] ) ? $wp_query->query_vars['date'] : null,
					'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
					'enddate'   => isset( $wp_query->query_vars['enddate'] ) ? $wp_query->query_vars['enddate'] : null,
				) );

				break;

			case 'forms' :

				$form = isset( $wp_query->query_vars['form'] ) ? $wp_query->query_vars['form'] : null;

				$data = $this->routes->get_forms( $form );

				break;

			case 'donors' :

				$donor = isset( $wp_query->query_vars['donor'] ) ? $wp_query->query_vars['donor'] : null;

				$data = $this->routes->get_donors( $donor );

				break;

			case 'donations' :

				/**
				 *  Call to get recent donations
				 *
				 * @params text date | today, yesterday or range
				 * @params date startdate | required when date = range and format to be YYYYMMDD (i.e. 20170524)
				 * @params date enddate | required when date = range and format to be YYYYMMDD (i.e. 20170524)
				 */
				$data = $this->routes->get_recent_donations( array(
					'id'        => isset( $wp_query->query_vars['id'] ) ? $wp_query->query_vars['id'] : null,
					'date'      => isset( $wp_query->query_vars['date'] ) ? $wp_query->query_vars['date'] : null,
					'startdate' => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
					'enddate'   => isset( $wp_query->query_vars['enddate'] ) ? $wp_query->query_vars['enddate'] : null,
				) );

				break;

		endswitch;

		// Allow extensions to setup their own return data
		$this->data = apply_filters( 'give_api_output_data', $data, $this->endpoint, $this );

		$after                       = microtime( true );
		$request_time                = ( $after - $before );
		$this->data['request_speed'] = $request_time;

		// Log this API request, if enabled. We log it here because we have access to errors.
		$this->log_request( $this->data );

		// Send out data to the output function
		$this->output();
	}

	/**
	 * Returns the API endpoint requested
	 *
	 * @access public
	 * @since  1.1
	 * @return string $query Query mode
	 */
	public function get_query_mode() {

		return $this->endpoint;
	}

	/**
	 * Determines the kind of query requested and also ensure it is a valid query
	 *
	 * @access public
	 * @since  1.1
	 * @global $wp_query
	 */
	public function set_query_mode() {

		global $wp_query;

		// Whitelist our query options
		$accepted = apply_filters( 'give_api_valid_query_modes', array(
			'stats',
			'forms',
			'donors',
			'donations',
		) );

		$query = isset( $wp_query->query_vars['give-api'] ) ? $wp_query->query_vars['give-api'] : null;
		$query = str_replace( $this->queried_version . '/', '', $query );

		$error = array();

		// Make sure our query is valid
		if ( ! in_array( $query, $accepted ) ) {
			$error['error'] = __( 'Invalid query.', 'give' );

			$this->data = $error;
			// 400 is Bad Request
			$this->output( 400 );
		}

		$this->endpoint = $query;
	}

	/**
	 * Get page number
	 *
	 * @access public
	 * @since  1.1
	 * @global $wp_query
	 * @return int $wp_query->query_vars['page'] if page number returned (default: 1)
	 */
	public function get_paged() {
		global $wp_query;

		return isset( $wp_query->query_vars['page'] ) ? $wp_query->query_vars['page'] : 1;
	}


	/**
	 * Number of results to display per page
	 *
	 * @access public
	 * @since  1.1
	 * @global $wp_query
	 * @return int $per_page Results to display per page (default: 10)
	 */
	public function per_page() {
		global $wp_query;

		$per_page = isset( $wp_query->query_vars['number'] ) ? $wp_query->query_vars['number'] : 10;

		if ( $per_page < 0 && $this->get_query_mode() == 'donors' ) {
			$per_page = 99999999;
		} // End if().

		return apply_filters( 'give_api_results_per_page', $per_page );
	}

	/**
	 * Sets up the dates used to retrieve earnings/donations
	 *
	 * @access public
	 * @since  1.2
	 *
	 * @param array $args Arguments to override defaults
	 *
	 * @return array $dates
	 */
	public function get_dates( $args = array() ) {
		$dates = array();

		$defaults = array(
			'type'      => '',
			'form'      => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$current_time = current_time( 'timestamp' );

		if ( 'range' === $args['date'] ) {
			$startdate          = strtotime( $args['startdate'] );
			$enddate            = strtotime( $args['enddate'] );
			$dates['day_start'] = date( 'd', $startdate );
			$dates['day_end']   = date( 'd', $enddate );
			$dates['m_start']   = date( 'n', $startdate );
			$dates['m_end']     = date( 'n', $enddate );
			$dates['year']      = date( 'Y', $startdate );
			$dates['year_end']  = date( 'Y', $enddate );
		} else {
			// Modify dates based on predefined ranges
			switch ( $args['date'] ) :

				case 'this_month' :
					$dates['day']     = null;
					$dates['m_start'] = date( 'n', $current_time );
					$dates['m_end']   = date( 'n', $current_time );
					$dates['year']    = date( 'Y', $current_time );
					break;

				case 'last_month' :
					$dates['day']     = null;
					$dates['m_start'] = date( 'n', $current_time ) == 1 ? 12 : date( 'n', $current_time ) - 1;
					$dates['m_end']   = $dates['m_start'];
					$dates['year']    = date( 'n', $current_time ) == 1 ? date( 'Y', $current_time ) - 1 : date( 'Y', $current_time );
					break;

				case 'today' :
					$dates['day']     = date( 'd', $current_time );
					$dates['m_start'] = date( 'n', $current_time );
					$dates['m_end']   = date( 'n', $current_time );
					$dates['year']    = date( 'Y', $current_time );
					break;

				case 'yesterday' :

					$year  = date( 'Y', $current_time );
					$month = date( 'n', $current_time );
					$day   = date( 'd', $current_time );

					if ( $month == 1 && $day == 1 ) {

						$year  -= 1;
						$month = 12;
						$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					} elseif ( $month > 1 && $day == 1 ) {

						$month -= 1;
						$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					} else {

						$day -= 1;

					}

					$dates['day']     = $day;
					$dates['m_start'] = $month;
					$dates['m_end']   = $month;
					$dates['year']    = $year;

					break;

				case 'this_quarter' :
					$month_now = date( 'n', $current_time );

					$dates['day'] = null;

					if ( $month_now <= 3 ) {

						$dates['m_start'] = 1;
						$dates['m_end']   = 3;
						$dates['year']    = date( 'Y', $current_time );

					} elseif ( $month_now <= 6 ) {

						$dates['m_start'] = 4;
						$dates['m_end']   = 6;
						$dates['year']    = date( 'Y', $current_time );

					} elseif ( $month_now <= 9 ) {

						$dates['m_start'] = 7;
						$dates['m_end']   = 9;
						$dates['year']    = date( 'Y', $current_time );

					} else {

						$dates['m_start'] = 10;
						$dates['m_end']   = 12;
						$dates['year']    = date( 'Y', $current_time );

					}
					break;

				case 'last_quarter' :
					$month_now = date( 'n', $current_time );

					$dates['day'] = null;

					if ( $month_now <= 3 ) {

						$dates['m_start'] = 10;
						$dates['m_end']   = 12;
						$dates['year']    = date( 'Y', $current_time ) - 1; // Previous year

					} elseif ( $month_now <= 6 ) {

						$dates['m_start'] = 1;
						$dates['m_end']   = 3;
						$dates['year']    = date( 'Y', $current_time );

					} elseif ( $month_now <= 9 ) {

						$dates['m_start'] = 4;
						$dates['m_end']   = 6;
						$dates['year']    = date( 'Y', $current_time );

					} else {

						$dates['m_start'] = 7;
						$dates['m_end']   = 9;
						$dates['year']    = date( 'Y', $current_time );

					}
					break;

				case 'this_year' :
					$dates['day']     = null;
					$dates['m_start'] = null;
					$dates['m_end']   = null;
					$dates['year']    = date( 'Y', $current_time );
					break;

				case 'last_year' :
					$dates['day']     = null;
					$dates['m_start'] = null;
					$dates['m_end']   = null;
					$dates['year']    = date( 'Y', $current_time ) - 1;
					break;

			endswitch;
		}// End if().

		/**
		 * Returns the filters for the dates used to retrieve earnings.
		 *
		 * @since 1.2
		 *
		 * @param array $dates The dates used for retrieving earnings.
		 */
		return apply_filters( 'give_api_stat_dates', $dates );
	}

	/**
	 * Process Get Donors API Request.
	 *
	 * @access public
	 * @since  1.1
	 * @global WPDB $wpdb  Used to query the database using the WordPress Database API.
	 *
	 * @param int $donor Donor ID.
	 *
	 * @return array $donors Multidimensional array of the donors.
	 */
	public function get_donors( $donor = null ) {

		$donors = array();
		$error  = array();
		if ( ! user_can( $this->user_id, 'view_give_sensitive_data' ) && ! $this->override ) {
			return $donors;
		}

		$paged    = $this->get_paged();
		$per_page = $this->per_page();
		$offset   = $per_page * ( $paged - 1 );

		if ( is_numeric( $donor ) ) {
			$field = 'id';
		} else {
			$field = 'email';
		}

		$donor_query = Give()->donors->get_donors( array(
			'number' => $per_page,
			'offset' => $offset,
			$field   => $donor,
		) );
		$donor_count = 0;

		if ( $donor_query ) {

			foreach ( $donor_query as $donor_obj ) {

				$names      = explode( ' ', $donor_obj->name );
				$first_name = ! empty( $names[0] ) ? $names[0] : '';
				$last_name  = '';
				if ( ! empty( $names[1] ) ) {
					unset( $names[0] );
					$last_name = implode( ' ', $names );
				}

				$title_prefix = Give()->donor_meta->get_meta( $donor_obj->id, '_give_donor_title_prefix', true );

				// Set title prefix empty, if not available in db.
				if ( empty( $title_prefix ) ) {
					$title_prefix = '';
				}

				$donors['donors'][ $donor_count ]['info']['user_id']      = '';
				$donors['donors'][ $donor_count ]['info']['username']     = '';
				$donors['donors'][ $donor_count ]['info']['display_name'] = '';
				$donors['donors'][ $donor_count ]['info']['donor_id']     = $donor_obj->id;
				$donors['donors'][ $donor_count ]['info']['title_prefix'] = $title_prefix;
				$donors['donors'][ $donor_count ]['info']['first_name']   = $first_name;
				$donors['donors'][ $donor_count ]['info']['last_name']    = $last_name;
				$donors['donors'][ $donor_count ]['info']['email']        = $donor_obj->email;

				if ( ! empty( $donor_obj->user_id ) ) {

					$user_data = get_userdata( $donor_obj->user_id );

					// Donor with registered account.
					$donors['donors'][ $donor_count ]['info']['user_id']      = $donor_obj->user_id;
					$donors['donors'][ $donor_count ]['info']['username']     = $user_data->user_login;
					$donors['donors'][ $donor_count ]['info']['display_name'] = $user_data->display_name;

				}

				$donors['donors'][ $donor_count ]['stats']['total_donations'] = $donor_obj->purchase_count;
				$donors['donors'][ $donor_count ]['stats']['total_spent']     = $donor_obj->purchase_value;

				$donor = new Give_Donor( $donor_obj->id );

				// Get donor's addresses.
				$donors['donors'][ $donor_count ]['address'] = $donor->address;

				$donor_count ++;

			} // End foreach().
		} elseif ( $donor ) {

			$error['error'] = sprintf(
				/* translators: %s: donor */
				__( 'Donor %s not found.', 'give' ),
				$donor
			);

			return $error;

		} else {

			$error['error'] = __( 'No donors found.', 'give' );

			return $error;

		} // End if().

		return $donors;
	}

	/**
	 * Process Get Donation Forms API Request
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param int $form Give Form ID.
	 *
	 * @return array $donors Multidimensional array of the forms.
	 */
	public function get_forms( $form = null ) {

		$forms = array();
		$error = array();

		if ( $form == null ) {
			$forms['forms'] = array();

			$form_list = get_posts( array(
				'post_type'        => 'give_forms',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged(),
			) );

			if ( $form_list ) {
				$i = 0;
				foreach ( $form_list as $form_info ) {
					$forms['forms'][ $i ] = $this->get_form_data( $form_info );
					$i ++;
				}
			}
		} else {
			if ( get_post_type( $form ) == 'give_forms' ) {
				$form_info = get_post( $form );

				$forms['forms'][0] = $this->get_form_data( $form_info );

			} else {
				$error['error'] = sprintf( /* translators: %s: form */
					__( 'Form %s not found.', 'give' ), $form );

				return $error;
			}
		}

		return $forms;
	}

	/**
	 * Given a give_forms post object, generate the data for the API output
	 *
	 * @since  1.1
	 *
	 * @param  object $form_info The Give Form's Post Object.
	 *
	 * @return array                Array of post data to return back in the API.
	 */
	private function get_form_data( $form_info ) {

		$form = array();
		$currency = give_get_option('currency');

		$form['info']['id']            = $form_info->ID;
		$form['info']['slug']          = $form_info->post_name;
		$form['info']['title']         = $form_info->post_title;
		$form['info']['create_date']   = $form_info->post_date;
		$form['info']['modified_date'] = $form_info->post_modified;
		$form['info']['status']        = $form_info->post_status;
		$form['info']['link']          = html_entity_decode( $form_info->guid );
		$form['info']['content']       = give_get_meta( $form_info->ID, '_give_form_content', true );
		$form['info']['thumbnail']     = wp_get_attachment_url( get_post_thumbnail_id( $form_info->ID ) );

		if ( give_is_setting_enabled( give_get_option( 'categories', 'disabled' ) ) ) {
			$form['info']['category'] = get_the_terms( $form_info, 'give_forms_category' );
			$form['info']['tags']     = get_the_terms( $form_info, 'give_forms_tag' );
		}
		if ( give_is_setting_enabled( give_get_option( 'tags', 'disabled' ) ) ) {
			$form['info']['tags'] = get_the_terms( $form_info, 'give_forms_tag' );
		}

		// Check whether any goal is to be achieved for the donation form.
		$goal_option = give_get_meta( $form_info->ID, '_give_goal_option', true );
		$goal_amount = give_get_meta( $form_info->ID, '_give_set_goal', true );
		if ( give_is_setting_enabled( $goal_option ) && $goal_amount ) {
			$total_income                         = give_get_form_earnings_stats( $form_info->ID );
			$goal_percentage_completed            = ( $total_income < $goal_amount ) ? round( ( $total_income / $goal_amount ) * 100, 2 ) : 100;
			$form['goal']['amount']               = isset( $goal_amount ) ? give_format_decimal( array( 'amount' => $goal_amount, 'currency' => $currency  ) ) : '';
			$form['goal']['percentage_completed'] = isset( $goal_percentage_completed ) ? $goal_percentage_completed : '';
		}

		if ( user_can( $this->user_id, 'view_give_reports' ) || $this->override ) {
			$form['stats']['total']['donations']           = give_get_form_sales_stats( $form_info->ID );
			$form['stats']['total']['earnings']            = give_format_decimal( array( 'amount' => give_get_form_earnings_stats( $form_info->ID ), 'currency' => $currency  ) );
			$form['stats']['monthly_average']['donations'] = give_get_average_monthly_form_sales( $form_info->ID );
			$form['stats']['monthly_average']['earnings']  = give_format_decimal( array( 'amount' => give_get_average_monthly_form_earnings( $form_info->ID ), 'currency' => $currency  ) );
		}

		$counter = 0;
		if ( give_has_variable_prices( $form_info->ID ) ) {
			foreach ( give_get_variable_prices( $form_info->ID ) as $price ) {
				$counter ++;
				// multi-level item
				$level                                     = isset( $price['_give_text'] ) ? $price['_give_text'] : 'level-' . $counter;
				$form['pricing'][ sanitize_key( $level ) ] = give_format_decimal( array( 'amount' => $price['_give_amount'], 'currency' => $currency ) );

			}
		} else {
			$form['pricing']['amount'] = give_format_decimal( array( 'amount' => give_get_form_price( $form_info->ID ), 'currency' => $currency ) );
		}

		if ( user_can( $this->user_id, 'view_give_sensitive_data' ) || $this->override ) {

			/**
			 * Fires when generating API sensitive data.
			 *
			 * @since 1.1
			 */
			do_action( 'give_api_sensitive_data' );

		}

		return apply_filters( 'give_api_forms_form', $form );

	}

	/**
	 * Process Get Stats API Request
	 *
	 * @since 1.1
	 *
	 * @global WPDB $wpdb Used to query the database using the WordPress.
	 *
	 * @param array $args Arguments provided by API Request.
	 *
	 * @return array
	 */
	public function get_stats( $args = array() ) {
		$defaults = array(
			'type'      => null,
			'form'      => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$dates = $this->get_dates( $args );

		$currency = give_get_option('currency');
		$stats     = array();
		$earnings  = array(
			'earnings' => array(),
		);
		$donations = array(
			'donations' => array(),
		);
		$error     = array();

		if ( ! user_can( $this->user_id, 'view_give_reports' ) && ! $this->override ) {
			return $stats;
		}

		if ( $args['type'] == 'donations' ) {

			if ( $args['form'] == null ) {
				if ( $args['date'] == null ) {
					$donations = $this->get_default_sales_stats();
				} elseif ( $args['date'] === 'range' ) {
					// Return donations for a date range.
					// Ensure the end date is later than the start date.
					if ( $args['enddate'] < $args['startdate'] ) {
						$error['error'] = __( 'The end date must be later than the start date.', 'give' );
					}

					// Ensure both the start and end date are specified
					if ( empty( $args['startdate'] ) || empty( $args['enddate'] ) ) {
						$error['error'] = __( 'Invalid or no date range specified.', 'give' );
					}

					$total = 0;

					// Loop through the years
					$y = $dates['year'];
					while ( $y <= $dates['year_end'] ) :

						if ( $dates['year'] == $dates['year_end'] ) {
							$month_start = $dates['m_start'];
							$month_end   = $dates['m_end'];
						} elseif ( $y == $dates['year'] && $dates['year_end'] > $dates['year'] ) {
							$month_start = $dates['m_start'];
							$month_end   = 12;
						} elseif ( $y == $dates['year_end'] ) {
							$month_start = 1;
							$month_end   = $dates['m_end'];
						} else {
							$month_start = 1;
							$month_end   = 12;
						}

						$i = $month_start;
						while ( $i <= $month_end ) :

							if ( $i == $dates['m_start'] ) {
								$d = $dates['day_start'];
							} else {
								$d = 1;
							}

							if ( $i == $dates['m_end'] ) {
								$num_of_days = $dates['day_end'];
							} else {
								$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
							}

							while ( $d <= $num_of_days ) :
								$sale_count = give_get_sales_by_date( $d, $i, $y );
								$date_key   = date( 'Ymd', strtotime( $y . '/' . $i . '/' . $d ) );
								if ( ! isset( $donations['sales'][ $date_key ] ) ) {
									$donations['sales'][ $date_key ] = 0;
								}
								$donations['sales'][ $date_key ] += $sale_count;
								$total                           += $sale_count;
								$d ++;
							endwhile;
							$i ++;
						endwhile;

						$y ++;
					endwhile;

					$donations['totals'] = $total;
				} else {
					if ( $args['date'] == 'this_quarter' || $args['date'] == 'last_quarter' ) {
						$donations_count = 0;

						// Loop through the months
						$month = $dates['m_start'];

						while ( $month <= $dates['m_end'] ) :
							$donations_count += give_get_sales_by_date( null, $month, $dates['year'] );
							$month ++;
						endwhile;

						$donations['donations'][ $args['date'] ] = $donations_count;
					} else {
						$donations['donations'][ $args['date'] ] = give_get_sales_by_date( $dates['day'], $dates['m_start'], $dates['year'] );
					}
				}// End if().
			} elseif ( $args['form'] == 'all' ) {
				$forms = get_posts( array(
					'post_type' => 'give_forms',
					'nopaging'  => true,
				) );
				$i     = 0;
				foreach ( $forms as $form_info ) {
					$donations['donations'][ $i ] = array(
						$form_info->post_name => $this->stats->get_sales(
							$form_info->ID,
							is_numeric( $args['startdate'] )
								? strtotime( $args['startdate'] )
								: $args['startdate'],
							is_numeric( $args['enddate'] )
								? strtotime( $args['enddate'] )
								: $args['enddate']
						),
					);
					$i ++;
				}
			} else {
				if ( get_post_type( $args['form'] ) == 'give_forms' ) {
					$form_info                 = get_post( $args['form'] );
					$donations['donations'][0] = array(
						$form_info->post_name => $this->stats->get_sales(
							$args['form'],
							is_numeric( $args['startdate'] )
								? strtotime( $args['startdate'] )
								: $args['startdate'],
							is_numeric( $args['enddate'] )
								? strtotime( $args['enddate'] )
								: $args['enddate']
						),
					);
				} else {
					$error['error'] = sprintf( /* translators: %s: form */
						__( 'Form %s not found.', 'give' ), $args['form'] );
				}
			}// End if().

			if ( ! empty( $error ) ) {
				return $error;
			}

			return $donations;

		} elseif ( $args['type'] == 'earnings' ) {
			if ( $args['form'] == null ) {
				if ( $args['date'] == null ) {
					$earnings = $this->get_default_earnings_stats();
				} elseif ( $args['date'] === 'range' ) {
					// Return sales for a date range
					// Ensure the end date is later than the start date
					if ( $args['enddate'] < $args['startdate'] ) {
						$error['error'] = __( 'The end date must be later than the start date.', 'give' );
					}

					// Ensure both the start and end date are specified
					if ( empty( $args['startdate'] ) || empty( $args['enddate'] ) ) {
						$error['error'] = __( 'Invalid or no date range specified.', 'give' );
					}

					$total = (float) 0.00;

					// Loop through the years
					$y = $dates['year'];
					if ( ! isset( $earnings['earnings'] ) ) {
						$earnings['earnings'] = array();
					}
					while ( $y <= $dates['year_end'] ) :

						if ( $dates['year'] == $dates['year_end'] ) {
							$month_start = $dates['m_start'];
							$month_end   = $dates['m_end'];
						} elseif ( $y == $dates['year'] && $dates['year_end'] > $dates['year'] ) {
							$month_start = $dates['m_start'];
							$month_end   = 12;
						} elseif ( $y == $dates['year_end'] ) {
							$month_start = 1;
							$month_end   = $dates['m_end'];
						} else {
							$month_start = 1;
							$month_end   = 12;
						}

						$i = $month_start;
						while ( $i <= $month_end ) :

							if ( $i == $dates['m_start'] ) {
								$d = $dates['day_start'];
							} else {
								$d = 1;
							}

							if ( $i == $dates['m_end'] ) {
								$num_of_days = $dates['day_end'];
							} else {
								$num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
							}

							while ( $d <= $num_of_days ) :
								$earnings_stat = give_get_earnings_by_date( $d, $i, $y );
								$date_key      = date( 'Ymd', strtotime( $y . '/' . $i . '/' . $d ) );
								if ( ! isset( $earnings['earnings'][ $date_key ] ) ) {
									$earnings['earnings'][ $date_key ] = 0;
								}

								$earnings['earnings'][ $date_key ] += give_format_decimal( array( 'amount' => $earnings_stat, 'currency' => $currency ) );
								$total                             += $earnings_stat;
								$d ++;
							endwhile;

							$i ++;
						endwhile;

						$y ++;
					endwhile;

					$earnings['totals'] = give_format_decimal( array( 'amount' => $total, 'currency' => $currency ) );
				} else {
					if ( $args['date'] == 'this_quarter' || $args['date'] == 'last_quarter' ) {
						$earnings_count = (float) 0.00;

						// Loop through the months
						$month = $dates['m_start'];

						while ( $month <= $dates['m_end'] ) :
							$earnings_count += give_get_earnings_by_date( null, $month, $dates['year'] );
							$month ++;
						endwhile;

						$earnings['earnings'][ $args['date'] ] = give_format_decimal( array( 'amount' => $earnings_count, 'currency' => $currency ) );
					} else {
						$earnings['earnings'][ $args['date'] ] = give_format_decimal( array( 'amount' => give_get_earnings_by_date( $dates['day'], $dates['m_start'], $dates['year'] ), 'currency' => $currency ) );
					}
				}// End if().
			} elseif ( $args['form'] == 'all' ) {
				$forms = get_posts( array(
					'post_type' => 'give_forms',
					'nopaging'  => true,
				) );

				$i = 0;
				foreach ( $forms as $form_info ) {
					$earnings['earnings'][ $i ] = array(
						$form_info->post_name => give_format_decimal( array( 'amount' => give_get_form_earnings_stats( $form_info->ID ), 'currency' => $currency ) ),
					);
					$i ++;
				}
			} else {
				if ( get_post_type( $args['form'] ) == 'give_forms' ) {
					$form_info               = get_post( $args['form'] );
					$earnings['earnings'][0] = array(
						$form_info->post_name => give_format_decimal( array( 'amount' => $this->stats->get_earnings(
							$args['form'],
							is_numeric( $args['startdate'] )
								? strtotime( $args['startdate'] )
								: $args['startdate'],
							is_numeric( $args['enddate'] )
								? strtotime( $args['enddate'] )
								: $args['enddate']
						), 'currency' => $currency ) ),
					);
				} else {
					$error['error'] = sprintf( /* translators: %s: form */
						__( 'Form %s not found.', 'give' ), $args['form'] );
				}
			}// End if().

			if ( ! empty( $error ) ) {
				return $error;
			}

			return $earnings;
		} elseif ( $args['type'] == 'donors' ) {
			$donors                             = new Give_DB_Donors();
			$stats['donations']['total_donors'] = $donors->count();

			return $stats;

		} elseif ( empty( $args['type'] ) ) {
			$stats = array_merge( $stats, $this->get_default_sales_stats() );
			$stats = array_merge( $stats, $this->get_default_earnings_stats() );

			return array(
				'stats' => $stats,
			);
		}// End if().
	}

	/**
	 * Retrieves Recent Donations
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param $args array
	 *
	 * @return array
	 */
	public function get_recent_donations( $args = array() ) {
		global $wp_query;

		$defaults = array(
			'id'        => null,
			'date'      => null,
			'startdate' => null,
			'enddate'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$donations = array();

		if ( ! user_can( $this->user_id, 'view_give_reports' ) && ! $this->override ) {
			return $donations;
		}

		if ( isset( $wp_query->query_vars['id'] ) ) {
			$query   = array();
			$query[] = new Give_Payment( $wp_query->query_vars['id'] );
		} elseif ( isset( $wp_query->query_vars['purchasekey'] ) ) {
			$query   = array();
			$query[] = give_get_payment_by( 'key', $wp_query->query_vars['purchasekey'] );
		} elseif ( isset( $wp_query->query_vars['email'] ) ) {
			$args  = array(
				'fields'     => 'ids',
				'meta_key'   => '_give_payment_donor_email',
				'meta_value' => $wp_query->query_vars['email'],
				'number'     => $this->per_page(),
				'page'       => $this->get_paged(),
			);
			$query = give_get_payments( $args );
		} elseif ( isset( $wp_query->query_vars['date'] ) ) {

			$current_time = current_time( 'timestamp' );
			$dates        = $this->get_dates( $args );
			$start_date   = '';
			$end_date     = '';

			/**
			 *  Switch case for date query argument
			 *
			 * @since  1.8.8
			 *
			 * @params text date | today, yesterday or range
			 * @params date startdate | required when date = range and format to be YYYYMMDD (i.e. 20170524)
			 * @params date enddate | required when date = range and format to be YYYYMMDD (i.e. 20170524)
			 */
			switch ( $wp_query->query_vars['date'] ) {

				case 'today':

					// Set and Format Start and End Date to be date of today.
					$start_date = $end_date = date( 'Y/m/d', $current_time );

					break;

				case 'yesterday':

					// Set and Format Start and End Date to be date of yesterday.
					$start_date = $end_date = date( 'Y/m', $current_time ) . '/' . ( date( 'd', $current_time ) - 1 );

					break;

				case 'range':

					// Format Start Date and End Date for filtering payment based on date range.
					$start_date = $dates['year'] . '/' . $dates['m_start'] . '/' . $dates['day_start'];
					$end_date   = $dates['year_end'] . '/' . $dates['m_end'] . '/' . $dates['day_end'];

					break;

			}

			$args = array(
				'fields'     => 'ids',
				'start_date' => $start_date,
				'end_date'   => $end_date,
				'number'     => $this->per_page(),
				'page'       => $this->get_paged(),
			);

			$query = give_get_payments( $args );
		} else {
			$args  = array(
				'fields' => 'ids',
				'number' => $this->per_page(),
				'page'   => $this->get_paged(),
			);
			$query = give_get_payments( $args );
		}// End if().

		if ( $query ) {
			$i = 0;
			foreach ( $query as $payment ) {

				if ( is_numeric( $payment ) ) {
					$payment      = new Give_Payment( $payment );
				}

				$payment_meta = $payment->get_meta();
				$user_info    = $payment->user_info;

				$first_name = isset( $user_info['first_name'] ) ? $user_info['first_name'] : '';
				$last_name  = isset( $user_info['last_name'] ) ? $user_info['last_name'] : '';

				$donations['donations'][ $i ]['ID']             = $payment->ID;
				$donations['donations'][ $i ]['number']         = $payment->number;
				$donations['donations'][ $i ]['transaction_id'] = $payment->transaction_id;
				$donations['donations'][ $i ]['key']            = $payment->key;
				$donations['donations'][ $i ]['total']          = give_format_decimal( array( 'donation_id' => $payment->ID, 'dp' => true ) );
				$donations['donations'][ $i ]['status']         = give_get_payment_status( $payment, true );
				$donations['donations'][ $i ]['gateway']        = $payment->gateway;
				$donations['donations'][ $i ]['name']           = trim( "{$first_name} {$last_name}" );
				$donations['donations'][ $i ]['fname']          = $first_name;
				$donations['donations'][ $i ]['lname']          = $last_name;
				$donations['donations'][ $i ]['email']          = $payment->email;
				$donations['donations'][ $i ]['date']           = $payment->date;
				$donations['donations'][ $i ]['payment_meta']   = array();

				$form_id  = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : $payment_meta;
				$price    = isset( $payment_meta['form_id'] ) ? give_get_form_price( $payment_meta['form_id'] ) : false;
				$price_id = isset( $payment_meta['price_id'] ) ? $payment_meta['price_id'] : null;

				$donations['donations'][ $i ]['form']['id']    = $form_id;
				$donations['donations'][ $i ]['form']['name']  = get_the_title( $payment_meta['form_id'] );
				$donations['donations'][ $i ]['form']['price'] = give_format_decimal( array( 'amount' => $price, 'currency' => give_get_option('currency' ), 'dp' => true ) );

				if ( give_has_variable_prices( $form_id ) ) {
					if ( isset( $payment_meta['price_id'] ) ) {
						$price_name                                         = give_get_price_option_name( $form_id, $payment_meta['price_id'], $payment->ID );
						$donations['donations'][ $i ]['form']['price_name'] = $price_name;
						$donations['donations'][ $i ]['form']['price_id']   = $price_id;
						$donations['donations'][ $i ]['form']['price']      = give_format_decimal( array( 'amount' => give_get_price_option_amount( $form_id, $price_id ), 'currency' => give_get_option('currency' ), 'dp' => true ) );
					}
				}

				if( ! empty( $payment_meta ) ) {
					// Add custom meta to API
					foreach ( $payment_meta as $meta_key => $meta_value ) {

						$exceptions = array(
							'_give_payment_form_title',
							'form_title',
							'_give_payment_form_id',
							'form_id',
							'_give_payment_price_id',
							'price_id',
							'user_info',
							'_give_payment_purchase_key',
							'key',
							'email',
							'date',
							'currency',
							'_give_payment_total',
							'_give_payment_date'
						);

						// Don't clutter up results with dupes
						if ( ! is_string( $meta_value ) || in_array( $meta_key, $exceptions ) ) {
							continue;
						}

						// Meta key can contain price value like _give_fee_amount, so convert them to standard format.
						if( give_is_amount_sanitized( $meta_value ) ) {
							$meta_value = give_format_decimal( array( 'amount' => $meta_value, 'currency' => give_get_option('currency' ), 'dp' => true ) );
						}

						$donations['donations'][ $i ]['payment_meta'][ $meta_key ] = $meta_value;

					}
				}

				$i ++;
			}// End foreach().
		}// End if().

		return apply_filters( 'give_api_donations_endpoint', $donations );
	}

	/**
	 * Retrieve the output format.
	 *
	 * Determines whether results should be displayed in XML or JSON.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return mixed
	 */
	public function get_output_format() {
		global $wp_query;

		$format = isset( $wp_query->query_vars['format'] ) ? $wp_query->query_vars['format'] : 'json';

		return apply_filters( 'give_api_output_format', $format );
	}


	/**
	 * Log each API request, if enabled.
	 *
	 * @access private
	 * @since  1.1
	 *
	 * @global WP_Query     $wp_query
	 *
	 * @param array         $data
	 *
	 * @return void
	 */
	private function log_request( $data = array() ) {
		if ( ! $this->log_requests ) {
			return;
		}

		/**
		 * @var WP_Query $wp_query
		 */
		global $wp_query;

		$query = array(
			'give-api'    => $wp_query->query_vars['give-api'],
			'key'         => isset( $wp_query->query_vars['key'] ) ? $wp_query->query_vars['key'] : null,
			'token'       => isset( $wp_query->query_vars['token'] ) ? $wp_query->query_vars['token'] : null,
			'query'       => isset( $wp_query->query_vars['query'] ) ? $wp_query->query_vars['query'] : null,
			'type'        => isset( $wp_query->query_vars['type'] ) ? $wp_query->query_vars['type'] : null,
			'form'        => isset( $wp_query->query_vars['form'] ) ? $wp_query->query_vars['form'] : null,
			'donor'       => isset( $wp_query->query_vars['donor'] ) ? $wp_query->query_vars['donor'] : null,
			'date'        => isset( $wp_query->query_vars['date'] ) ? $wp_query->query_vars['date'] : null,
			'startdate'   => isset( $wp_query->query_vars['startdate'] ) ? $wp_query->query_vars['startdate'] : null,
			'enddate'     => isset( $wp_query->query_vars['enddate'] ) ? $wp_query->query_vars['enddate'] : null,
			'id'          => isset( $wp_query->query_vars['id'] ) ? $wp_query->query_vars['id'] : null,
			'purchasekey' => isset( $wp_query->query_vars['purchasekey'] ) ? $wp_query->query_vars['purchasekey'] : null,
			'email'       => isset( $wp_query->query_vars['email'] ) ? $wp_query->query_vars['email'] : null,
		);

		$log_data = array(
			'log_type'     => 'api_request',
			'post_excerpt' => http_build_query( $query ),
			'post_content' => ! empty( $data['error'] ) ? $data['error'] : '',
		);

		$log_meta = array(
			'api_query'  => http_build_query( $query ),
			'request_ip' => give_get_ip(),
			'user'       => $this->user_id,
			'key'        => isset( $wp_query->query_vars['key'] ) ? $wp_query->query_vars['key'] : null,
			'token'      => isset( $wp_query->query_vars['token'] ) ? $wp_query->query_vars['token'] : null,
			'time'       => $data['request_speed'],
			'version'    => $this->get_queried_version(),
		);

		Give()->logs->insert_log( $log_data, $log_meta );
	}


	/**
	 * Retrieve the output data.
	 *
	 * @access public
	 * @since  1.1
	 * @return array
	 */
	public function get_output() {
		return $this->data;
	}

	/**
	 * Output Query in either JSON/XML.
	 * The query data is outputted as JSON by default.
	 *
	 * @since 1.1
	 * @global WP_Query $wp_query
	 *
	 * @param int       $status_code
	 */
	public function output( $status_code = 200 ) {

		$format = $this->get_output_format();

		status_header( $status_code );

		/**
		 * Fires before outputting the API.
		 *
		 * @since 1.1
		 *
		 * @param array    $data   Response data to return.
		 * @param Give_API $this   The Give_API object.
		 * @param string   $format Output format, XML or JSON. Default is JSON.
		 */
		do_action( 'give_api_output_before', $this->data, $this, $format );

		switch ( $format ) :

			case 'xml' :

				require_once GIVE_PLUGIN_DIR . 'includes/libraries/array2xml.php';
				$xml = Array2XML::createXML( 'give', $this->data );
				echo $xml->saveXML();

				break;

			case 'json' :

				header( 'Content-Type: application/json' );
				if ( ! empty( $this->pretty_print ) ) {
					echo json_encode( $this->data, $this->pretty_print );
				} else {
					echo json_encode( $this->data );
				}

				break;

			default :

				/**
				 * Fires by the API while outputting other formats.
				 *
				 * @since 1.1
				 *
				 * @param array    $data Response data to return.
				 * @param Give_API $this The Give_API object.
				 */
				do_action( "give_api_output_{$format}", $this->data, $this );

				break;

		endswitch;

		/**
		 * Fires after outputting the API.
		 *
		 * @since 1.1
		 *
		 * @param array    $data   Response data to return.
		 * @param Give_API $this   The Give_API object.
		 * @param string   $format Output format, XML or JSON. Default is JSON.
		 */
		do_action( 'give_api_output_after', $this->data, $this, $format );

		give_die();
	}

	/**
	 * Modify User Profile
	 *
	 * Modifies the output of profile.php to add key generation/revocation.
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param object $user Current user info
	 *
	 * @return void
	 */
	function user_key_field( $user ) {

		if ( ( give_get_option( 'api_allow_user_keys', false ) || current_user_can( 'manage_give_settings' ) ) && current_user_can( 'edit_user', $user->ID ) ) {

			$user = get_userdata( $user->ID );
			?>
			<table class="form-table">
				<tbody>
				<tr>
					<th>
						<?php _e( 'GiveWP API Keys', 'give' ); ?>
					</th>
					<td>
						<?php
						$public_key = $this->get_user_public_key( $user->ID );
						$secret_key = $this->get_user_secret_key( $user->ID );
						?>
						<?php if ( empty( $user->give_user_public_key ) ) { ?>
							<input name="give_set_api_key" type="checkbox" id="give_set_api_key" />
							<span class="description"><?php _e( 'Generate API Key', 'give' ); ?></span>
						<?php } else { ?>
							<strong style="display:inline-block; width: 125px;"><?php _e( 'Public key:', 'give' ); ?>
								&nbsp;</strong>
							<input type="text" disabled="disabled" class="regular-text" id="publickey" value="<?php echo esc_attr( $public_key ); ?>" />
							<br />
							<strong style="display:inline-block; width: 125px;"><?php _e( 'Secret key:', 'give' ); ?>
								&nbsp;</strong>
							<input type="text" disabled="disabled" class="regular-text" id="privatekey" value="<?php echo esc_attr( $secret_key ); ?>" />
							<br />
							<strong style="display:inline-block; width: 125px;"><?php _e( 'Token:', 'give' ); ?>
								&nbsp;</strong>
							<input type="text" disabled="disabled" class="regular-text" id="token" value="<?php echo esc_attr( $this->get_token( $user->ID ) ); ?>" />
							<br />
							<input name="give_revoke_api_key" type="checkbox" id="give_revoke_api_key" />
							<span class="description"><label for="give_revoke_api_key"><?php _e( 'Revoke API Keys', 'give' ); ?></label></span>
						<?php } ?>
					</td>
				</tr>
				</tbody>
			</table>
		<?php }// End if().
	}

	/**
	 * Process an API key generation/revocation
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function process_api_key( $args ) {

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'give-api-nonce' ) ) {
			wp_die( __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ), __( 'Error', 'give' ), array(
				'response' => 403,
			) );
		}

		if ( empty( $args['user_id'] ) ) {
			wp_die( __( 'User ID Required.', 'give' ), __( 'Error', 'give' ), array(
				'response' => 401,
			) );
		}

		if ( is_numeric( $args['user_id'] ) ) {
			$user_id = isset( $args['user_id'] ) ? absint( $args['user_id'] ) : get_current_user_id();
		} else {
			$userdata = get_user_by( 'login', $args['user_id'] );
			$user_id  = $userdata->ID;
		}
		$process = isset( $args['give_api_process'] ) ? strtolower( $args['give_api_process'] ) : false;

		if ( $user_id == get_current_user_id() && ! give_get_option( 'allow_user_api_keys' ) && ! current_user_can( 'manage_give_settings' ) ) {
			wp_die( sprintf( /* translators: %s: process */
				__( 'You do not have permission to %s API keys for this user.', 'give' ), $process ), __( 'Error', 'give' ), array(
				'response' => 403,
			) );
		} elseif ( ! current_user_can( 'manage_give_settings' ) ) {
			wp_die( sprintf( /* translators: %s: process */
				__( 'You do not have permission to %s API keys for this user.', 'give' ), $process ), __( 'Error', 'give' ), array(
				'response' => 403,
			) );
		}

		switch ( $process ) {
			case 'generate':
				if ( $this->generate_api_key( $user_id ) ) {
					Give_Cache::delete( Give_Cache::get_key( 'give_total_api_keys' ) );
					wp_redirect( add_query_arg( 'give-messages[]', 'api-key-generated', 'edit.php?post_type=give_forms&page=give-tools&tab=api' ) );
					exit();
				} else {
					wp_redirect( add_query_arg( 'give-messages[]', 'api-key-failed', 'edit.php?post_type=give_forms&page=give-tools&tab=api' ) );
					exit();
				}
				break;
			case 'regenerate':
				$this->generate_api_key( $user_id, true );
				Give_Cache::delete( Give_Cache::get_key( 'give_total_api_keys' ) );
				wp_redirect( add_query_arg( 'give-messages[]', 'api-key-regenerated', 'edit.php?post_type=give_forms&page=give-tools&tab=api' ) );
				exit();
				break;
			case 'revoke':
				$this->revoke_api_key( $user_id );
				Give_Cache::delete( Give_Cache::get_key( 'give_total_api_keys' ) );
				wp_redirect( add_query_arg( 'give-messages[]', 'api-key-revoked', 'edit.php?post_type=give_forms&page=give-tools&tab=api' ) );
				exit();
				break;
			default;
				break;
		}
	}

	/**
	 * Generate new API keys for a user
	 *
	 * @param int     $user_id    User ID the key is being generated for.
	 * @param boolean $regenerate Regenerate the key for the user.
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @return boolean True if (re)generated successfully, false otherwise.
	 */
	public function generate_api_key( $user_id = 0, $regenerate = false ) {

		// Bail out, if user doesn't exists.
		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		// Bail Out, if user object doesn't exists.
		if ( ! $user ) {
			return false;
		}

		$new_public_key = '';
		$new_secret_key = '';

		if( ! empty( $_POST['from'] ) && 'profile' === $_POST['from'] ) {
			// For User Profile Page.
			if( ! empty( $_POST['give_set_api_key'] ) ) {
				// Generate API Key from User Profile page.
				$new_public_key = $this->generate_public_key( $user->user_email );
				$new_secret_key = $this->generate_private_key( $user->ID );
			} elseif ( ! empty( $_POST['give_revoke_api_key'] ) ) {
				// Revoke API Key from User Profile page.
				$this->revoke_api_key( $user->ID );
			} else {
				return false;
			}
		} else {
			// For Tools > API page.
			$public_key = $this->get_user_public_key( $user_id );

			if ( empty( $public_key ) && ! $regenerate ) {
				// Generating API for first time.
				$new_public_key = $this->generate_public_key( $user->user_email );
				$new_secret_key = $this->generate_private_key( $user->ID );
			} elseif ( $public_key && $regenerate ) {
				// API Key already exists and Regenerating API Key.
				$this->revoke_api_key( $user->ID );
				$new_public_key = $this->generate_public_key( $user->user_email );
				$new_secret_key = $this->generate_private_key( $user->ID );
			} elseif ( ! empty( $public_key ) && ! $regenerate ) {
				// Doing nothing, when API Key exists but still try to generate again instead of regenerating.
				return false;
			} else {
				// Revoke API Key.
				$this->revoke_api_key( $user->ID );
			}
		}

		update_user_meta( $user_id, $new_public_key, 'give_user_public_key' );
		update_user_meta( $user_id, $new_secret_key, 'give_user_secret_key' );

		return true;
	}

	/**
	 * Revoke a users API keys
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param int $user_id User ID of user to revoke key for
	 *
	 * @return bool
	 */
	public function revoke_api_key( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$public_key = $this->get_user_public_key( $user_id );
		$secret_key = $this->get_user_secret_key( $user_id );
		if ( ! empty( $public_key ) ) {
			Give_Cache::delete( Give_Cache::get_key( md5( 'give_api_user_' . $public_key ) ) );
			Give_Cache::delete( Give_Cache::get_key( md5( 'give_api_user_public_key' . $user_id ) ) );
			Give_Cache::delete( Give_Cache::get_key( md5( 'give_api_user_secret_key' . $user_id ) ) );
			delete_user_meta( $user_id, $public_key );
			delete_user_meta( $user_id, $secret_key );
		} else {
			return false;
		}

		return true;
	}

	public function get_version() {
		return self::VERSION;
	}

	/**
	 * Generate the public key for a user
	 *
	 * @access private
	 * @since  1.1
	 *
	 * @param string $user_email
	 *
	 * @return string
	 */
	private function generate_public_key( $user_email = '' ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$public   = hash( 'md5', $user_email . $auth_key . date( 'U' ) );

		return $public;
	}

	/**
	 * Generate the secret key for a user
	 *
	 * @access private
	 * @since  1.1
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	private function generate_private_key( $user_id = 0 ) {
		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $user_id . $auth_key . date( 'U' ) );

		return $secret;
	}

	/**
	 * Retrieve the user's token
	 *
	 * @access private
	 * @since  1.1
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	public function get_token( $user_id = 0 ) {
		return hash( 'md5', $this->get_user_secret_key( $user_id ) . $this->get_user_public_key( $user_id ) );
	}

	/**
	 * Generate the default donation stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @since  1.1
	 * @return array default sales statistics
	 */
	private function get_default_sales_stats() {

		// Default sales return
		$donations                               = array();
		$donations['donations']['today']         = $this->stats->get_sales( 0, 'today' );
		$donations['donations']['current_month'] = $this->stats->get_sales( 0, 'this_month' );
		$donations['donations']['last_month']    = $this->stats->get_sales( 0, 'last_month' );
		$donations['donations']['totals']        = give_get_total_donations();

		return $donations;
	}

	/**
	 * Generate the default earnings stats returned by the 'stats' endpoint
	 *
	 * @access private
	 * @return array default earnings statistics
	 * @since  1.1
	 */
	private function get_default_earnings_stats() {
		$currency = give_get_option( 'currency' );

		// Default earnings return
		$earnings                              = array();
		$earnings['earnings']['today']         = give_format_decimal( array(
			'amount'   => $this->stats->get_earnings( 0, 'today' ),
			'currency' => $currency,
		) );
		$earnings['earnings']['current_month'] = give_format_decimal( array(
			'amount'   => $this->stats->get_earnings( 0, 'this_month' ),
			'currency' => $currency,
		) );
		$earnings['earnings']['last_month']    = give_format_decimal( array(
			'amount'   => $this->stats->get_earnings( 0, 'last_month' ),
			'currency' => $currency,
		) );
		$earnings['earnings']['totals']        = give_format_decimal( array(
			'amount'   => give_get_total_earnings(),
			'currency' => $currency,
		) );

		return $earnings;
	}

	/**
	 * API Key Backwards Compatibility
	 *
	 * A Backwards Compatibility call for the change of meta_key/value for users API Keys.
	 *
	 * @since  1.3.6
	 *
	 * @param  string $check     Whether to check the cache or not
	 * @param  int    $object_id The User ID being passed
	 * @param  string $meta_key  The user meta key
	 * @param  bool   $single    If it should return a single value or array
	 *
	 * @return string            The API key/secret for the user supplied
	 */
	public function api_key_backwards_compat( $check, $object_id, $meta_key, $single ) {

		if ( $meta_key !== 'give_user_public_key' && $meta_key !== 'give_user_secret_key' ) {
			return $check;
		}

		$return = $check;

		switch ( $meta_key ) {
			case 'give_user_public_key':
				$return = Give()->api->get_user_public_key( $object_id );
				break;
			case 'give_user_secret_key':
				$return = Give()->api->get_user_secret_key( $object_id );
				break;
		}

		if ( ! $single ) {
			$return = array( $return );
		}

		return $return;

	}
}
