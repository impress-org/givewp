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
                    'data' => [
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
                    'data' => [
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
                    'data' => [
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
                    'data' => [
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
                    'data' => [
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
                    'type' => 'pie',
                    'data' => [
                        'labels' => [
                            'Save the Whales',
                            'Another Campaign',
                            'One More Campaign',
                        ],
                        'datasets' => [
                            [
                                'label' => 'Donations',
                                'data' => [12, 14, 8],
                            ]
                        ]
                    ]
                ]
            ]),
            'payment_statuses' => new Card([
                'title' => 'Payment Statuses',
                'type' => 'chart',
                'width' => 4,
                'props' => [
                    'type' => 'bar',
                    'data' => [
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
                    'data' => [
                        'labels' => [
                            'January',
                            'February',
                            'March',
                            'April',
                        ],
                        'datasets' => [
                            [
                                'label' => 'Donations',
                                'data' => [12, 14, 8, 9],
                                    
                            ]
                        ]
                    ]
                ]
            ]),
        ];
    }
}
