<?php
/**
 * Abstract Report class
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

/**
 * Common functionality for reports. Override this class.
 */
abstract class Report {

	/**
	 * Variables used to register block type
	 *
	 * @var string
	 */
    protected $period = [];

	/**
	 * Initialize.
	 */
	public function __construct() {
        //Do nothing
    }

	public function handle_api_callback ($data) {
        $response = new WP_REST_Response([
            'key' => 'value',
            'report' => 'data'
        ]);
        return $response;
    }

}
