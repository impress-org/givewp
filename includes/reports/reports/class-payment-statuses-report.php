<?php
/**
 * Campaigns report
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

require_once GIVE_PLUGIN_DIR . 'includes/reports/reports/class-report.php';

/**
 * Functionality and actions specific to the campaigns report
 */
class Payment_Statuses_Report extends Report {
    public function handle_api_callback ($data) {
        $response = new \WP_REST_Response([
            'key' => 'value',
            'report' => 'payment-statuses',
            'data' => [
                'labels' => [
                    'Completed',
                    'Abandoned',
                    'Refunded'
                ],
                'datasets' => [
                    'label' => 'Payment Statuses',
                    'data' => [
                        326,
                        16,
                        44
                    ]
                ]
            ]
        ]);
        return $response;
    }
}
