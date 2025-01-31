<?php

namespace Give\Donors\Routes;

use Give\Donors\Controllers\DonorRequestController;
use Give\Donors\ValueObjects\DonorRoute;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class RegisterDonorRoutes
{
    /**
     * @var DonorRequestController
     */
    protected $donorRequestController;

    /**
     * @unreleased
     */
    public function __construct(DonorRequestController $donorRequestController)
    {
        $this->donorRequestController = $donorRequestController;
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->registerGetDonor();
        $this->registerGetDonors();
    }

    /**
     * Get Donor route
     *
     * @unreleased
     */
    public function registerGetDonor()
    {
        register_rest_route(
            DonorRoute::NAMESPACE,
            DonorRoute::DONOR,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->donorRequestController->getDonor($request);
                    },
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );
    }

    /**
     * Get Donors route
     *
     * @unreleased
     */
    public function registerGetDonors()
    {
        register_rest_route(
            DonorRoute::NAMESPACE,
            DonorRoute::DONORS,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->donorRequestController->getDonors($request);
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
                            'name',
                            'firstName',
                            'lastName',
                            'totalAmountDonated',
                            'totalNumberOfDonations',
                        ],
                    ],
                    'direction' => [
                        'type' => 'string',
                        'default' => 'ASC',
                        'enum' => ['ASC', 'DESC'],
                    ],
                    'onlyWithDonations' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'hideAnonymousDonors' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                ],
            ]
        );
    }
}
