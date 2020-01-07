<?php
/**
 * Payments report
 *
 * @package Give
 */

namespace Give\API\Controllers;

class Reports extends Controller {

    protected $reports = [];
 
    // Here initialize our resource name.
    public function __construct() {

        $this->resource_name = 'reports';

        $this->reports = [
            'payment_statuses' => new \Give\Reports\Report\Payment_Statuses()
        ];

    }

    // Register our routes.
    public function register_routes() {
        register_rest_route( '/give-api/v2/reports/?P<report>[a-zA-Z0-9-]+)/', array(
            // Here we register the readable endpoint for collections.
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_report' ),
                'permission_callback' => array( $this, 'permissions_check' ),
                // 'args' => array(
                //     'report' => array(
                //         'type' => 'string',
                //         'enum' => array_keys($this->reports),
                //         'validate_callback' => function($param, $request, $key) {
                //             return !empty( $param );
                //         }
                //     ),
                //     'period' => array(
                //         'validate_callback' => function($param, $request, $key) {
                //             return !empty( $param );
                //         },
                //         'sanitize_callback' => '',
                //     )
                // )
            ),
            // Register our schema callback.
            'schema' => array( $this, 'get_report_schema' ),
        ) );
    }

    /**
     * Check permissions
    *
    * @param WP_REST_Request $request Current request.
    */
    public function permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the reports resource.' ), array( 'status' => $this->authorization_status_code() ) );
        }
        return true;
    }

    /**
     * Get report callback
    *
    * @param WP_REST_Request $request Current request.
    */
    public function get_report( $request ) {
        return 'testingg';
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
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            // The title property marks the identity of the resource.
            'title'                => 'report',
            'type'                 => 'object',
            // In JSON Schema you can specify object properties in the properties attribute.
            'properties'           => array(
                'name' => array(
                    'description'  => esc_html__( 'Unique identifier for the report.', 'give' ),
                    'type'         => 'string',
                    'context'      => array( 'view', 'edit', 'embed' ),
                    'readonly'     => true,
                ),
                'data' => array(
                    'description'  => esc_html__( 'The data for the report.', 'give' ),
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