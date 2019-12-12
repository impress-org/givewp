<?php
/**
 * Donors Page
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

require_once GIVE_PLUGIN_DIR . 'includes/reports/cards/class-card.php';
require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-page.php';

/**
 * Functionality and actions specific to the donors page
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
                    'type' => 'line',
                    'data' => [
                        'label' => 'Donations',
                        'labels' => [
                            'January',
                            'February',
                            'March',
                            'April',
                            'May',
                        ],
                        'datasets' => [
                            [
                                'data' => [4, 2, 6, 2, 5],
                                'backgroundColor' => [
                                    'rgba(255, 99, 132, 0.2)',
                                ],
                                'borderColor' => [
                                    'rgba(255, 99, 132, 1)',
                                ],
                                'borderWidth' => 1
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'yAxes' => [
                                [
                                    'ticks' => [
                                        'beginAtZero' => true,
                                        'stepSize' => 5
                                    ]
                                ]
                            ]
                        ]
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
