<?php
/**
 * Give API V2
 *
 * @package     Give
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give_API_V2 Class
 *
 * The base version API class
 *
 * @since  2.1
 */
class Give_API_V2 {
	/**
	 * API base prefix
	 *
	 * @since  2.1
	 * @access private
	 *
	 * @var string
	 */
	private $rest_base = 'give-api/v2';

	/**
	 * Instance.
	 *
	 * @since  2.1
	 * @access private
	 *
	 * @var Give_API_V2
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.1
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.1
	 * @access public
	 * @return Give_API_V2
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->init();
		}

		return self::$instance;
	}


	/**
	 * Initialize API
	 *
	 * @since  2.1
	 * @access private
	 */
	private function init() {
		// Setup hooks.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_script' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'localize_script' ), 999 );
	}


	/**
	 * Register API routes
	 * Note: only for internal purpose.
	 * @todo   : prevent cross domain api request
	 *
	 * @since  2.1
	 * @access private
	 */
	public function register_routes() {
		register_rest_route( $this->rest_base, '/form/(?P<id>[\d]+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_forms_data' ),
		) );

		register_rest_route( $this->rest_base, '/form-grid', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_donation_grid' ),
		) );

		register_rest_route( $this->rest_base, '/donor-wall', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_donor_wall' ),
		) );
	}

	/**
	 * Add api localize data
	 *
	 * @since  2.1
	 * @access public
	 */
	public function localize_script() {
		$data = array(
			'root' => esc_url_raw( Give_API_V2::get_rest_api() ),
			'rest_base' => $this->rest_base
		);

		if ( is_admin() ) {
			wp_localize_script( 'give-admin-scripts', 'giveApiSettings', $data );
		} else {
			wp_localize_script( 'give', 'giveApiSettings', $data );
		}
	}

	/**
	 * Rest fetch form data callback
	 *
	 * @param WP_REST_Request $request
	 *
	 * @access public
	 * @return array|mixed|object
	 */
	public function get_forms_data( $request ) {
		$parameters = $request->get_params();

		// Bailout
		if ( ! isset( $parameters['id'] ) || empty( $parameters['id'] ) ) {
			return array( 'error' => 'no_parameter_given' );
		}

		return give_form_shortcode( $parameters );
	}

	/**
	 * Rest fetch form data callback
	 *
	 * @param WP_REST_Request $request
	 *
	 * @access public
	 * @return array|mixed|object
	 */
	public function get_donation_grid( $request ) {
		$parameters = $request->get_params();

		return give_form_grid_shortcode( $parameters );
	}

	/**
	 * Rest fetch form data callback
	 *
	 * @param WP_REST_Request $request
	 *
	 * @access public
	 * @return array|mixed|object
	 */
	public function get_donor_wall( $request ) {
		$parameters = $request->get_params();

		return Give_Donor_Wall::get_instance()->render_shortcode( $parameters );
	}

	/**
	 * Get api reset url
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @param int    $blog_id Optional. Blog ID. Default of null returns URL for current blog.
	 * @param string $path    Optional. REST route. Default '/'.
	 * @param string $scheme  Optional. Sanitization scheme. Default 'rest'.
	 *
	 * @return string Full URL to the endpoint.
	 */
	public static function get_rest_api( $blog_id = null, $path = '/', $scheme = 'rest' ) {
		return trailingslashit( get_rest_url( $blog_id, $path, $scheme ) . self::$instance->rest_base );
	}
}

Give_API_V2::get_instance();
