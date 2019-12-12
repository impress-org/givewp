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
                                'data' => [12, 14, 8, 9, 11],
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
                'title' => 'Campaign Performance',
                'type' => 'chart',
                'width' => 6,
                'props' => [
                    'type' => 'doughnut',
                    'data' => [
                        'labels' => ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
                        'datasets' => [
                            [
                                'label' => '# of Votes',
                                'data' => [12, 19, 3, 5, 2, 3],
                                'backgroundColor' => [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(255, 159, 64, 0.2)'
                                ],
                                'borderColor' => [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                'borderWidth' => 1
                            ]
                        ]
                    ]
                ]
            ]),
            'payment_statuses' => new Card([
                'title' => 'Payment Statuses',
                'type' => 'chart',
                'width' => 6,
                'props' => [
                    'type' => 'bar',
                    'data' => [
                        'labels' => ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
                        'datasets' => [
                            [
                                'label' => '# of Votes',
                                'data' => [12, 19, 3, 5, 2, 3],
                                'backgroundColor' => [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)',
                                    'rgba(255, 159, 64, 0.2)'
                                ],
                                'borderColor' => [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
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
