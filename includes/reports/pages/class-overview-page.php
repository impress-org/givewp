<?php
/**
 * Overview Page
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

require_once GIVE_PLUGIN_DIR . 'includes/reports/cards/class-card.php';
require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-page.php';

/**
 * Functionality and actions specific to the overview page
 */
class Overview_Page extends Page {

    public function __construct() {
        $this->title = 'Overview';
        $this->path = '/';
        $this->cards = [
            'donations_for_period' => new Card([
                'title' => 'Donations For Period',
                'type' => 'chart',
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
            'campaign_performance' => new Card([
                'title' => 'Campaign Performance',
                'type' => 'chart',
                'width' => 6,
                'props' => ''
            ]),
            'payment_statuses' => new Card([
                'title' => 'Payment Statuses',
                'type' => 'chart',
                'width' => 6,
                'props' => ''
            ]),
        ];
    }
}
