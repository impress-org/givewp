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

	// To do: refactor high-level API methods

	//Register api routes for reports, pages, and singles
	protected function register_api_routes() {

		register_rest_route( 'givewp/v3', '/reports/report=(?P<report>[a-zA-Z0-9-]+)/', array(
			'methods' => 'GET',
			'callback' => [__CLASS__, 'handle_report_callback'],
		));

		register_rest_route( 'givewp/v3', '/reports/page=(?P<page>[a-zA-Z0-9-]+)/', array(
			'methods' => 'GET',
			'callback' => [__CLASS__, 'handle_page_callback'],
		));

		register_rest_route( 'givewp/v3', '/reports/single=(?P<page>[a-zA-Z0-9-]+)/', array(
			'methods' => 'GET',
			'callback' => [__CLASS__, 'handle_single_callback'],
		));

	}

	//Return response for report API request
	protected function handle_report_callback (WP_REST_Request $request) {
		$report = self::$reports[$request['report']];
		return $report->handle_api_callback($request);
	}

	//Return response for page API request
	protected function handle_page_callback (WP_REST_Request $request) {
		$page = self::$pages[$request['page']];
		return $page->handle_api_callback($request);
	}

	//Return response for single API request
	protected function handle_single_callback (WP_REST_Request $request) {
		$page = self::$pages['single'];
		return $page->handle_api_callback($request);
	}

}