<?php

namespace Give\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationAnonymousMode;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Controllers\DonationRequestController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class RegisterDonationRoutes
{
    /**
     * @var DonationRequestController
     */
    protected $donationRequestController;

    /**
     * @since 4.0.0
     */
    public function __construct(DonationRequestController $donationRequestController)
    {
        $this->donationRequestController = $donationRequestController;
    }

    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        $this->registerGetDonation();
        $this->registerGetDonations();
    }

    /**
     * Get Donation route
     *
     * @since 4.0.0
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
     * @since 4.4.0 add status parameter.
     * @since 4.0.0
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
                    'status' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'any',
                                'publish',
                                'give_subscription',
                                'pending',
                                'processing',
                                'refunded',
                                'revoked',
                                'failed',
                                'cancelled',
                                'abandoned',
                                'preapproval'
                            ],
                        ],
                        'default' => ['any'],
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'donorId' => [
                        'type' => 'integer',
                        'default' => 0,
                    ],
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

        $donationAnonymousMode = new DonationAnonymousMode($request->get_param('anonymousDonations'));
        if ( ! $isAdmin && $donationAnonymousMode->isIncluded()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include anonymous donations.', 'give'),
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
