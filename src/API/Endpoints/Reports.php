<?php

/**
 * Payments report
 *
 * @package Give
 */

namespace Give\API\Endpoints;

class Reports extends Endpoint {

    protected $reports = [];

    // Here initialize our resource name.
    public function __construct() {

        $this->resource_name = 'reports';

        $this->reports = array(
            'payment-statuses' => new \Give\Reports\Report\PaymentStatuses()
		);

    }

    public function init() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    // Register our routes.
    public function register_routes() {
        register_rest_route(
			'give-api/v2',
			'/reports/(?P<report>[a-zA-Z0-9-]+)',
			array(
				// Here we register the readable endpoint
				array(
					'methods' => 'GET',
					'callback' => array($this, 'get_report'),
					'permission_callback' => array($this, 'permissions_check'),
					'args' => array(
						'report' => array(
							'type' => 'string',
							'enum' => array_keys($this->reports),
							'required' => true,
						),
						'start' => array(
							'type' => 'string',
							'required' => true,
							'validate_callback' => array($this, 'validate_date'),
							'sanitize_callback' => array($this, 'sanitize_date')
						),
						'end' => array(
							'type' => 'string',
							'required' => true,
							'validate_callback' => array($this, 'validate_date'),
							'sanitize_callback' => array($this, 'sanitize_date')
						)
					)
				),
				// Register our schema callback.
				'schema' => array($this, 'get_report_schema'),
			)
		);
    }

    public function validate_date($param, $request, $key) {
        //Check that date is valid, and formatted YYYY-MM-DD
        $exploded = explode('-', $param);
        $valid = checkdate($exploded[1], $exploded[2], $exploded[0]);

        //If checking end date, check that it is after start date
        if ( $key === 'end' ) {
            $start = date($request['start']);
            $end = date($request['end']);
            $valid = $start <= $end ? $valid : false;
        }

        return $valid;
    }

    public function sanitize_date($param, $request, $key) {
        //Return Date object from parameter
        $date = date($param);
        return $date;
    }

    /**
     * Check permissions
     *
     * @param WP_REST_Request $request Current request.
     */
    public function permissions_check($request) {
        if (!current_user_can('read')) {
            return new \WP_Error(
				'rest_forbidden',
				esc_html__('You cannot view the reports resource.', 'give'),
				array(
					'status' => $this->authorization_status_code()
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
    public function get_report($request) {

        $report = $this->reports[$request['report']];

        return new \WP_REST_Response(
			array(
				'report' => $request['report'],
				'start' => $request['start'],
				'end' => $request['end'],
				'data' => array(
					'labels' => $report->get_labels(),
					'datasets' => $report->get_datasets(),
				)
			)
		);
    }

    /**
     * Get our sample schema for a report
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_report_schema($request) {

        if ($this->schema) {
            // Since WordPress 5.3, the schema can be cached in the $schema property.
            return $this->schema;
        }

        $this->schema = array(
            // This tells the spec of JSON Schema we are using which is draft 4.
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'report',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'report' => array(
                    'description'  => esc_html__('Unique identifier for the report.', 'give'),
                    'type'         => 'string',
                    'context'      => array('view', 'edit', 'embed'),
                    'readonly'     => true,
                ),
                'data' => array(
                    'description'  => esc_html__('The data for the report.', 'give'),
                    'type'         => 'object',
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
}
