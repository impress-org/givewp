<?php

namespace Give\Donors\Routes;

use Give\Donors\Controllers\DonorRequestController;
use Give\Donors\ValueObjects\DonorAnonymousMode;
use Give\Donors\ValueObjects\DonorRoute;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class RegisterDonorRoutes
{
    /**
     * @var DonorRequestController
     */
    protected $donorRequestController;

    /**
     * @since 4.0.0
     */
    public function __construct(DonorRequestController $donorRequestController)
    {
        $this->donorRequestController = $donorRequestController;
    }

    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        $this->registerGetDonor();
        $this->registerGetDonors();
    }

    /**
     * Get Donor route
     *
     * @since 4.0.0
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
                    'permission_callback' => function (WP_REST_Request $request) {
                        return $this->permissionsCheck($request);
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'includeSensitiveData' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'anonymousDonors' => [
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
     * Get Donors route
     *
     * @since 4.0.0
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
                    'permission_callback' => function (WP_REST_Request $request) {
                        return $this->permissionsCheck($request);
                    },
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
                        'default' => 'DESC',
                        'enum' => ['ASC', 'DESC'],
                    ],
                    'mode' => [
                        'type' => 'string',
                        'default' => 'live',
                        'enum' => ['live', 'test'],
                    ],
                    'onlyWithDonations' => [
                        'type' => 'boolean',
                        'default' => true,
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'includeSensitiveData' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'anonymousDonors' => [
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
     * @since 4.0.0
     */
    public function permissionsCheck(WP_REST_Request $request)
    {
        $isAdmin = current_user_can('manage_options');

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if ( ! $isAdmin && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
        if ( ! $isAdmin && $donorAnonymousMode->isIncluded()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include anonymous donors.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since 4.0.0
     */
    public function authorizationStatusCode(): int
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }
}
