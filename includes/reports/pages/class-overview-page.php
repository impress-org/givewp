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
                    'type' => 'line',
                    'aspectRatio' => 0.4,
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
                                'label' => 'Donations',
                                'data' => [12, 14, 8, 9, 11],
                                'backgroundColor' => [
                                    'rgba(105, 184, 104, 0.21)',
                                ],
                                'borderColor' => [
                                    'rgba(105, 184, 104, 1)',
                                ],
                                'borderWidth' => 3
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
                'title' => 'Campaign Performance',
                'type' => 'chart',
                'width' => 6,
                'props' => [
                    'type' => 'doughnut',
                    'aspectRatio' => 0.6,
                    'data' => [
                        'labels' => ['Red', 'Blue', 'Yellow', 'Green', 'Purple'],
                        'datasets' => [
                            [
                                'label' => '# of Votes',
                                'data' => [12, 19, 3, 5, 2],
                                'backgroundColor' => [
                                    '#69B868',
                                    '#F49420',
                                    '#556E79',
                                    '#D75A4B',
                                    '#9EA3A8',
                                ],
                                'borderColor' => '#FFFFFF',
                                'borderWidth' => 3,
                                'borderAlign' => 'inner'
                            ]
                        ],
                    ]
                ]
            ]),
            'payment_statuses' => new Card([
                'title' => 'Payment Statuses',
                'type' => 'chart',
                'width' => 6,
                'props' => [
                    'type' => 'bar',
                    'aspectRatio' => 0.6,
                    'data' => [
                        'labels' => ['Stripe', 'Paypal', 'Yellow', 'Green', 'Purple'],
                        'datasets' => [
                            [
                                'label' => '# of Votes',
                                'data' => [12, 19, 3, 5, 2],
                                'backgroundColor' => [
                                    '#69B868',
                                    '#F49420',
                                    '#556E79',
                                    '#D75A4B',
                                    '#9EA3A8',
                                ],
                            ]
                        ]
                    ],
                    'options' => [
                        'scales' => [
                            'yAxes' => [
                                [
                                    'ticks' => [
                                        'beginAtZero' => true
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]),
        ];
    }
}
