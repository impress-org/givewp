<?php
/**
 * Give API V2
 *
 * @package     Give
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2018, WordImpress
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
	 * @Access private
	 */
	private function init() {
		// Setup hooks.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}


	/**
	 * Register API routes
	 *
	 * @since  2.1
	 * @access private
	 */
	public function register_routes() {
		register_rest_route( $this->rest_base, '/form/(?P<id>\d+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_forms_data' ),
		) );
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

		if ( ! ( $html = give_form_shortcode( $parameters ) ) ) {
			// @todo: add notice here for form which do not has publish status.
			$html = '';
		}

		// Response data array
		$response = array(
			'html' => $html,
		);

		return $response;
	}
}

Give_API_V2::get_instance();
