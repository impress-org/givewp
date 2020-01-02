<?php
/**
 * Reports API class
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Manages report api
 */
class Reports_API {

	/**
	 * Initialize Reports REST API
	 */
	public function init() {
		add_action( 'rest_api_init', [$this, 'register_api_routes'] );
	}

	public function __construct($args) {
        $this->reports = $args['reports'];
	}

	//Register api routes for reports
	public function register_api_routes() {

		register_rest_route( 'give-api/v2', '/reports/report=(?P<report>[a-zA-Z0-9-]+)/', array(
			'methods' => 'GET',
			'callback' => [$this, 'handle_callback'],
		));

	}

	//Return response for report API request
	public function handle_callback (\WP_REST_Request $request) {
		$report = $this->reports[$request['report']];
		return $report->handle_api_callback($request['data']);
	}

}