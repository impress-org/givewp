<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

use DateInterval;
use DateTime;
use \Give_Cache;
use Give_Payment;
use WP_REST_Request;
use WP_REST_Response;

abstract class Endpoint {

	/**
	 * @since 2.6.1
	 * @var WP_REST_Request
	 */
	protected $request;

	/**
	 * @var DateTime
	 */
	protected $startDate;

	/**
	 * @var DateTime
	 */
	protected $endDate;

	/**
	 * @var DateInterval
	 */
	protected $dateDiff;

	/**
	 * @var string
	 */
	protected $endpoint;

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_route' ) );
	}

	// Register our routes.
	public function register_route() {
		register_rest_route(
			'give-api/v2',
			'/reports/' . $this->endpoint,
			array(
				// Here we register the readable endpoint
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_request' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'start' => array(
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => array( $this, 'validate_date' ),
							'sanitize_callback' => array( $this, 'sanitize_date' ),
						),
						'end'   => array(
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => array( $this, 'validate_date' ),
							'sanitize_callback' => array( $this, 'sanitize_date' ),
						),
					),
				),
				// Register our schema callback.
				'schema' => array( $this, 'get_report_schema' ),
			)
		);
	}

	/**
	 * Handle rest request.
	 *
	 * @since 2.6.1
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function handle_request( $request ) {
		// Check if a cached version exists
		$cached_report = $this->get_cached_report( $request );
		if ( $cached_report !== null ) {
			// Bail and return the cached version
			return new WP_REST_Response( $cached_report );
		}

		$this->setupProperties( $request );

		$responseData = array(
			'status' => $this->get_give_status(),
			'data'   => $this->get_report( $request ),
		);

		$this->cache_report( $request, $responseData );

		return new WP_REST_Response( $responseData );
	}

	/**
	 * Setup properties
	 *
	 * @since 2.6.1
	 * @param WP_REST_Request $request
	 */
	private function setupProperties( $request ) {
		$this->request   = $request;
		$this->startDate = date_create( $request->get_param( 'start' ) );
		$this->endDate   = date_create( $request->get_param( 'end' ) );
		$this->dateDiff  = date_diff( $this->startDate, $this->endDate );
	}

	public function validate_date( $param, $request, $key ) {
		// Check that date is valid, and formatted YYYY-MM-DD
		$exploded = explode( '-', $param );
		$valid    = checkdate( $exploded[1], $exploded[2], $exploded[0] );

		// If checking end date, check that it is after start date
		if ( $key === 'end' ) {
			$start = date_create( $request->get_param( 'start' ) );
			$end   = date_create( $request->get_param( 'end' ) );
			$valid = $start <= $end ? $valid : false;
		}

		return $valid;
	}

	public function sanitize_date( $param, $request, $key ) {
		// Return Date object from parameter
		$exploded = explode( '-', $param );
		$date     = "{$exploded[0]}-{$exploded[1]}-{$exploded[2]} 24:00:00";
		return $date;
	}

	/**
	 * Check permissions
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function permissions_check( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				esc_html__( 'You cannot view the reports resource.', 'give' ),
				array(
					'status' => $this->authorization_status_code(),
				)
			);
		}
		return true;
	}

	/**
	 * Get report callback
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return array
	 */
	public function get_report( $request ) {
		return array(
			'data' => array(
				'labels' => array( 'a', 'b', 'c' ),
				'data'   => array( '1', '4', '3' ),
			),
		);
	}

	/**
	 * Get our sample schema for a report
	 */
	public function get_report_schema() {

		if ( $this->schema ) {
			// Since WordPress 5.3, the schema can be cached in the $schema property.
			return $this->schema;
		}

		$this->schema = array(
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'report',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => array(
				'data' => array(
					'description' => esc_html__( 'The data for the report.', 'give' ),
					'type'        => 'object',
				),
			),
		);

		return $this->schema;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {

		$status = 401;
		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;

	}

	/**
	 * Get cached report
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return mixed
	 */
	public function get_cached_report( $request ) {
		$cache_key = Give_Cache::get_key( "api_get_report_{$this->endpoint}", $request->get_params() );

		$cached = Give_Cache::get_db_query( $cache_key );

		if ( $cached ) {
			return $cached;
		}

		return null;

	}

	/**
	 * Cache report
	 *
	 * @param WP_REST_Request $request Current request.
	 * @param array           $report
	 *
	 * @return bool
	 */
	public function cache_report( $request, $report ) {
		$cache_key = Give_Cache::get_key( "api_get_report_{$this->endpoint}", $request->get_params() );

		return Give_Cache::set_db_query( $cache_key, $report );

	}

	/**
	 * Cache report
	 *
	 * @param  array          $args Query arguments.
	 * @param  Give_Payment[] $payments Payments.
	 *
	 * @return bool
	 */
	private function cache_payments( $args, $payments ) {
		$cache_key = Give_Cache::get_key( 'api_report_payments', $args );

		return Give_Cache::set_db_query( $cache_key, $payments );

	}

	/**
	 * Get cached report
	 *
	 * @param  array $args Query arguments.
	 * @return mixed
	 */
	private function get_cached_payments( $args ) {

		$cache_key = Give_Cache::get_key( 'api_report_payments', $args );

		$cached = Give_Cache::get_db_query( $cache_key );

		if ( $cached ) {
			return $cached;
		}

		return null;

	}


	/**
	 * Get payment.
	 *
	 * @param  string $startStr
	 * @param   string $endStr
	 * @param string $orderBy
	 * @param int    $number
	 *
	 * @return mixed
	 */
	public function get_payments( $startStr, $endStr, $orderBy = 'date', $number = -1 ) {
		$args = array(
			'number'     => $number,
			'paged'      => 1,
			'orderby'    => $orderBy,
			'order'      => 'DESC',
			'start_date' => $startStr,
			'end_date'   => $endStr,
		);

		// Check if a cached payments exists
		$cached_payments = $this->get_cached_payments( $args );

		if ( $cached_payments !== null ) {
			// Bail and return the cached payments
			return $cached_payments;
		}

		$payments = new \Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		// Cache the report data
		$this->cache_payments( $args, $payments );

		return $payments;

	}

	public function get_give_status() {

		$donations = get_posts(
			array(
				'post_type'   => array( 'give_payment' ),
				'post_status' => 'publish',
				'numberposts' => 1,
			)
		);

		if ( count( $donations ) > 0 ) {
			return 'donations_found';
		} else {
			return 'no_donations_found';
		}
	}
}
