<?php
/**
 * Overview Page
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

require_once GIVE_PLUGIN_DIR . 'includes/reports/charts/class-chart.php';
require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-page.php';

/**
 * Functionality and actions specific to the content API
 */
class Donors_Page extends Page {

    public function __construct() {
        $this->title = 'Donors';
        $this->path = '/donors';
        $this->charts = [
            'donations_for_period' => new Chart([
                'title' => 'Donations For Period',
                'type' => 'line',
                'width' => 12,
                'props' => [
                    [
                        'label' => 'Total Raised',
                        'data' => ''
                    ],
                    [
                        'label' => 'Total Donors',
                        'data' => ''
                    ],
                    [
                        'label' => 'Average Donation',
                        'data' => ''
                    ],
                    [
                        'label' => 'Total Refunded',
                        'data' => ''
                    ]
                ]
            ]),
            'campaign_performance' => new Chart([
                'title' => 'Campaign Performance',
                'type' => 'doughnut',
                'width' => 6,
                'props' => ''
            ]),
            'payment_statuses' => new Chart ([
                'title' => 'Payment Statuses',
                'type' => 'bar',
                'width' => 6,
                'props' => ''
            ]),
        ];
    }
}
