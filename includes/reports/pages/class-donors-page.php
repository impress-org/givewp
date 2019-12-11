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
 * Functionality and actions specific to the content API
 */
class Donors_Page extends Page {

    public function __construct() {
        $this->title = 'Donors';
        $this->path = '/donors';
        $this->cards = [
            'donations_for_period' => new Card([
                'title' => 'Donors For Period',
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
                'title' => 'Donors by Country',
                'type' => 'list',
                'width' => 4,
                'props' => ''
            ]),
            'payment_statuses' => new Card([
                'title' => 'Top Donors',
                'type' => 'list',
                'width' => 8,
                'props' => ''
            ]),
        ];
    }
}
