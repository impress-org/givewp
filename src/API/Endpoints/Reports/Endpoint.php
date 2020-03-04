<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

use \Give_Cache;

abstract class Endpoint {

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
					'callback'            => array( $this, 'get_report' ),
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

	public function validate_date( $param, $request, $key ) {
		// Check that date is valid, and formatted YYYY-MM-DD
		$exploded = explode( '-', $param );
		$valid    = checkdate( $exploded[1], $exploded[2], $exploded[0] );

		// If checking end date, check that it is after start date
		if ( $key === 'end' ) {
			$start = date_create( $request['start'] );
			$end   = date_create( $request['end'] );
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
	 */
	public function get_report( $request ) {
		return new \WP_REST_Response(
			array(
				'data' => array(
					'labels' => [ 'a', 'b', 'c' ],
					'data'   => [ '1', '4', '3' ],
				),
			)
		);
	}

	/**
	 * Get our sample schema for a report
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_report_schema( $request ) {

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
	 */
	public function get_cached_report( $request ) {

		$start = date_create( $request['start'] );
		$end   = date_create();

		// Do not get cached report for period less than a week
		$diff = date_diff( $start, $end );
		if ( $diff->days < 2 ) {
			return null;
		}

		$query_args = [
			'start' => $request['start'],
			'end'   => $request['end'],
		];

		$cache_key = Give_Cache::get_key( "api_get_report_{$this->endpoint}", $query_args );

		$cached = Give_Cache::get_db_query( $cache_key );

		if ( $cached ) {
			return $cached;
		} else {
			return null;
		}
	}

	/**
	 * Cache report
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function cache_report( $request, $report ) {

		$query_args = [
			'start' => $request['start'],
			'end'   => $request['end'],
		];

		$cache_key = Give_Cache::get_key( "api_get_report_{$this->endpoint}", $query_args );

		$result = Give_Cache::set_db_query( $cache_key, $report );

		return $result;

	}

		/**
		 * Cache report
		 *
		 * @param WP_REST_Request $request Current request.
		 */
	public function cache_payments( $startStr, $endStr, $orderBy, $number, $payments ) {

		$query_args = [
			'start'   => $startStr,
			'end'     => $endStr,
			'orderby' => $orderBy,
			'number'  => $number,
		];

		$cache_key = Give_Cache::get_key( 'api_report_payments', $query_args );

		$result = Give_Cache::set_db_query( $cache_key, $payments );

		return $result;

	}

	/**
	 * Get cached report
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_cached_payments( $startStr, $endStr, $orderBy, $number ) {

		$query_args = [
			'start'   => $startStr,
			'end'     => $endStr,
			'orderby' => $orderBy,
			'number'  => $number,
		];

		$cache_key = Give_Cache::get_key( 'api_report_payments', $query_args );

		$cached = Give_Cache::get_db_query( $cache_key );

		if ( $cached ) {
			return $cached;
		} else {
			return null;
		}
	}

	public function get_payments( $startStr, $endStr, $orderBy = 'date', $number = -1 ) {

		// Check if a cached payments exists
		$cached_payments = $this->get_cached_payments( $startStr, $endStr, $orderBy, $number );
		if ( $cached_payments !== null ) {
			// Bail and return the cached payments
			return $cached_payments;
		}

		$args = [
			'number'     => $number,
			'paged'      => 1,
			'orderby'    => $orderBy,
			'order'      => 'DESC',
			'start_date' => $startStr,
			'end_date'   => $endStr,
		];

		$payments = new \Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		// Cache the report data
		$result = $this->cache_payments( $startStr, $endStr, $orderBy, $number, $payments );

		return $payments;

	}

	public function get_give_status() {

		$donations = get_posts(
			[
				'post_type'   => array( 'give_payment' ),
				'post_status' => 'publish',
				'numberposts' => 1,
			]
		);

		if ( count( $donations ) > 0 ) {
			return 'donations_found';
		} else {
			return 'no_donations_found';
		}
	}
}
