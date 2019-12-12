<?php
/**
 * Overview Page
 *
 * @package Give
 */

namespace Give;

defined( 'ABSPATH' ) || exit;

require_once GIVE_PLUGIN_DIR . 'includes/reports/pages/class-page.php';

/**
 * Functionality and actions specific to the overview page
 */
class Overview_Page extends Page {

    public function __construct() {

        require_once GIVE_PLUGIN_DIR . 'includes/reports/cards/class-card.php';

        $this->title = 'Overview';
        $this->path = '/';
        $this->cards = [
            'donations_for_period' => new Card([
                'title' => 'Donations For Period',
                'type' => 'chart',
                'width' => 12,
                'props' => [
                    'type' => 'line',
                    'aspectRatio' => 0.25,
                    'data' => [
                        'label' => 'Donations',
                        'labels' => [
                            'January',
                            'February',
                            'March',
                            'April',
                            'May',
                            'June', 
                            'July',
                            'August',
                            'September',
                            'October',
                            'November',
                            'December'
                        ],
                        'datasets' => [
                            [
                                'label' => 'Donations',
                                'data' => [12, 14, 8, 9, 11, 12, 11, 13, 4, 10, 11, 9],
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
            'total_raised' => new Card([
                'title' => 'Total Raised',
                'type' => 'chart',
                'width' => 3,
                'props' => [
                    'type' => 'line',
                    'aspectRatio' => 0.6,
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
                                'label' => 'Donors',
                                'data' => [4, 2, 6, 2, 5],
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
            'total_donors' => new Card([
                'title' => 'Total Donors',
                'type' => 'chart',
                'width' => 3,
                'props' => [
                    'type' => 'line',
                    'aspectRatio' => 0.6,
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
                                'label' => 'Donors',
                                'data' => [4, 2, 6, 2, 5],
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
            'average_donation' => new Card([
                'title' => 'Average Donation',
                'type' => 'chart',
                'width' => 3,
                'props' => [
                    'type' => 'line',
                    'aspectRatio' => 0.6,
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
                                'label' => 'Donors',
                                'data' => [4, 2, 6, 2, 5],
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
            'total_refunds' => new Card([
                'title' => 'Total Refunds',
                'type' => 'chart',
                'width' => 3,
                'props' => [
                    'type' => 'line',
                    'aspectRatio' => 0.6,
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
                                'label' => 'Donors',
                                'data' => [4, 2, 6, 2, 5],
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
                'width' => 4,
                'props' => [
                    'type' => 'doughnut',
                    'aspectRatio' => 1,
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
                'width' => 4,
                'props' => [
                    'type' => 'bar',
                    'aspectRatio' => 1,
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
            'payment_gateways' => new Card([
                'title' => 'Payment Gateways',
                'type' => 'chart',
                'width' => 4,
                'props' => [
                    'type' => 'doughnut',
                    'aspectRatio' => 1,
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
        ];
    }
}
