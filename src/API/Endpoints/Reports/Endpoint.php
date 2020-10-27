<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

use DateInterval;
use DateTime;
use Give\API\RestRoute;
use \Give_Cache;
use Give_Payment;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

abstract class Endpoint implements RestRoute {
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

	/**
	 * @var boolean
	 */
	protected $testMode;

	/**
	 * @var string
	 */
	protected $currency;

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			'/reports/' . $this->endpoint,
			[
				// Here we register the readable endpoint
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => [ $this, 'permissionsCheck' ],
					'args'                => [
						'start'    => [
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => [ $this, 'validateDate' ],
							'sanitize_callback' => [ $this, 'sanitizeDate' ],
						],
						'end'      => [
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => [ $this, 'validateDate' ],
							'sanitize_callback' => [ $this, 'sanitizeDate' ],
						],
						'currency' => [
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => [ $this, 'validateCurrency' ],
						],
						'testMode' => [
							'type'              => 'boolean',
							'required'          => true,
							'sanitize_callback' => [ $this, 'sanitizeTestMode' ],
						],
					],
				],
				// Register our schema callback.
				'schema' => [ $this, 'getReportSchema' ],
			]
		);
	}

	/**
	 * Handle rest request.
	 *
	 * @since 2.6.1
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function handleRequest( $request ) {
		// Check if a cached version exists
		$cached_report = $this->getCachedReport( $request );
		if ( $cached_report !== null ) {
			// Bail and return the cached version
			return new WP_REST_Response( $cached_report );
		}

		$this->setupProperties( $request );

		$responseData = [
			'status' => $this->getGiveStatus(),
			'data'   => $this->getReport( $request ),
		];

		$this->cacheReport( $request, $responseData );

		return new WP_REST_Response( $responseData );
	}

	/**
	 * Setup properties
	 *
	 * @since 2.6.1
	 *
	 * @param WP_REST_Request $request
	 */
	private function setupProperties( $request ) {
		$this->request   = $request;
		$this->startDate = date_create( $request->get_param( 'start' ) );
		$this->endDate   = date_create( $request->get_param( 'end' ) );
		$this->currency  = $request->get_param( 'currency' );
		$this->testMode  = $request->get_param( 'testMode' );
		$this->dateDiff  = date_diff( $this->startDate, $this->endDate );
	}

	public function validateDate( $param, $request, $key ) {
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

	/**
	 * @since 2.9.0 Restrict appended time to only the end date.
	 * @since 2.6.1
	 */
	public function sanitizeDate( $param, $request, $key ) {
		// Return Date object from parameter
		$exploded = explode( '-', $param );

		$sanitizedDate = "{$exploded[0]}-{$exploded[1]}-{$exploded[2]}";

		if ( 'end' === $key ) {
			/**
			 * For the end date manually specify an end time.
			 */
			$sanitizedDate .= ' 24:00:00';
		}

		return $sanitizedDate;
	}

	/**
	 * Validate currency string
	 * Check if currency code provided to REST APi is valid
	 *
	 * @param string          $param Currency parameter provided in REST API request
	 * @param WP_REST_Request $request REST API Request object
	 * @param string          $key REST API Request key being validated (in this case currency)
	 *
	 * @return bool
	 */
	public function validateCurrency( $param, $request, $key ) {
		return in_array( $param, array_keys( give_get_currencies_list() ) );
	}

	/**
	 * Sanitize test mode parameter
	 * Uses filter_var to cast string to variable
	 *
	 * @param string          $param Validated test mode parameter provided in REST API request
	 * @param WP_REST_Request $request REST API Request object
	 * @param string          $key REST API Request key being validated (in this case test mode)
	 */
	public function sanitizeTestMode( $param, $request, $key ) {
		return filter_var( $param, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Check permissions
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @return bool|WP_Error
	 */
	public function permissionsCheck( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error(
				'rest_forbidden',
				esc_html__( 'You cannot view the reports resource.', 'give' ),
				[ 'status' => $this->authorizationStatusCode() ]
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
	public function getReport( $request ) {
		return [
			'data' => [
				'labels' => [ 'a', 'b', 'c' ],
				'data'   => [ '1', '4', '3' ],
			],
		];
	}

	/**
	 * Get our sample schema for a report
	 */
	public function getReportSchema() {

		if ( $this->schema ) {
			// Since WordPress 5.3, the schema can be cached in the $schema property.
			return $this->schema;
		}

		$this->schema = [
			// This tells the spec of JSON Schema we are using which is draft 4.
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			// The title property marks the identity of the resource.
			'title'      => 'report',
			'type'       => 'object',
			// In JSON Schema you can specify object properties in the properties attribute.
			'properties' => [
				'data' => [
					'description' => esc_html__( 'The data for the report.', 'give' ),
					'type'        => 'object',
				],
			],
		];

		return $this->schema;
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorizationStatusCode() {

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
	public function getCachedReport( $request ) {
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
	public function cacheReport( $request, $report ) {
		$cache_key = Give_Cache::get_key( "api_get_report_{$this->endpoint}", $request->get_params() );

		return Give_Cache::set_db_query( $cache_key, $report );

	}

	/**
	 * Cache report
	 *
	 * @param array          $args Query arguments.
	 * @param Give_Payment[] $payments Payments.
	 *
	 * @return bool
	 */
	private function cachePayments( $args, $payments ) {
		$cache_key = Give_Cache::get_key( 'api_report_payments', $args );

		return Give_Cache::set_db_query( $cache_key, $payments );

	}

	/**
	 * Get cached report
	 *
	 * @param array $args Query arguments.
	 *
	 * @return mixed
	 */
	private function getCachedPayments( $args ) {

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
	 * @param string $startStr
	 * @param string $endStr
	 * @param string $orderBy
	 * @param int    $number
	 *
	 * @return mixed
	 */
	public function getPayments( $startStr, $endStr, $orderBy = 'date', $number = - 1 ) {

		$gatewayObjects        = give_get_payment_gateways();
		$paymentModeKeyCompare = '!=';

		if ( $this->testMode === false ) {
			unset( $gatewayObjects['manual'] );
			$paymentModeKeyCompare = '=';
		}

		$gateway = array_keys( $gatewayObjects );

		$args = [
			'post_status' => [
				'publish',
				'give_subscription',
			],
			'number'      => $number,
			'paged'       => 1,
			'orderby'     => $orderBy,
			'order'       => 'DESC',
			'start_date'  => strtotime( $startStr ),
			'end_date'    => strtotime( $endStr ),
			'gateway'     => $gateway,
			'meta_query'  => [
				[
					'key'     => '_give_payment_currency',
					'value'   => $this->currency,
					'compare' => '=',
				],
				[
					'key'     => '_give_payment_mode',
					'value'   => 'live',
					'compare' => $paymentModeKeyCompare,
				],
			],
		];

		// Check if a cached payments exists
		$cached_payments = $this->getCachedPayments( $args );

		if ( $cached_payments !== null ) {
			// Bail and return the cached payments
			return $cached_payments;
		}

		$payments = new \Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		// Cache the report data
		$this->cachePayments( $args, $payments );

		return $payments;

	}

	public function getGiveStatus() {

		$donations = get_posts(
			[
				'post_type'   => [ 'give_payment' ],
				'post_status' => 'publish',
				'numberposts' => 1,
			]
		);

		if ( count( $donations ) > 0 ) {
			return 'donations_found';
		}

		return 'no_donations_found';
	}
}
