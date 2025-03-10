<?php

namespace Give\Donations\Routes;

use Give\Donations\Controllers\DonationRequestController;
use Give\Donations\ValueObjects\DonationRoute;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class RegisterDonationRoutes
{
    /**
     * @var DonationRequestController
     */
    protected $donationRequestController;

    /**
     * @unreleased
     */
    public function __construct(DonationRequestController $donationRequestController)
    {
        $this->donationRequestController = $donationRequestController;
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->registerGetDonation();
        $this->registerGetDonations();
    }

    /**
     * Get Donation route
     *
     * @unreleased
     */
    public function registerGetDonation()
    {
        register_rest_route(
            DonationRoute::NAMESPACE,
            DonationRoute::DONATION,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->donationRequestController->getDonation($request);
                    },
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    /*'sensitiveData' => [
                        'type' => 'string',
                        'default' => 'exclude',
                        'enum' => [
                            'exclude',
                            'include',
                        ],
                    ],*/
                    'includeSensitiveData' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'anonymousDonations' => [
                        'type' => 'string',
                        'default' => 'exclude',
                        'enum' => [
                            'exclude',
                            'include',
                            'redact',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get Donations route
     *
     * @unreleased
     */
    public function registerGetDonations()
    {
        register_rest_route(
            DonationRoute::NAMESPACE,
            DonationRoute::DONATIONS,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->donationRequestController->getDonations($request);
                    },
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1,
                        'maximum' => 100,
                    ],
                    'sort' => [
                        'type' => 'string',
                        'default' => 'id',
                        'enum' => [
                            'id',
                            'createdAt',
                            'updatedAt',
                            'status',
                            'amount',
                            'feeAmountRecovered',
                            'donorId',
                            'firstName',
                            'lastName',
                        ],
                    ],
                    'direction' => [
                        'type' => 'string',
                        'default' => 'DESC',
                        'enum' => ['ASC', 'DESC'],
                    ],
                    'mode' => [
                        'type' => 'string',
                        'default' => 'live',
                        'enum' => ['live', 'test'],
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    /*'sensitiveData' => [
                        'type' => 'string',
                        'default' => 'exclude',
                        'enum' => [
                            'exclude',
                            'include',
                        ],
                    ],*/
                    'includeSensitiveData' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'anonymousDonations' => [
                        'type' => 'string',
                        'default' => 'exclude',
                        'enum' => [
                            'exclude',
                            'include',
                            'redact',
                        ],
                    ],
                ],
            ]
        );
    }
}
