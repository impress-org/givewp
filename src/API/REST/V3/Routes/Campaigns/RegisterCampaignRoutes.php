<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use DateTime;
use Give\API\REST\V3\Routes\Campaigns\Permissions\CampaignPermissions;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Controllers\CampaignRequestController;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class RegisterCampaignRoutes
{
    /**
     * @var CampaignRequestController
     */
    protected $campaignRequestController;

    /**
     * @since 4.0.0
     */
    public function __construct(CampaignRequestController $campaignRequestController)
    {
        $this->campaignRequestController = $campaignRequestController;
    }

    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        $this->registerGetCampaign();
        $this->registerUpdateCampaign();
        $this->registerGetCampaigns();
        $this->registerMergeCampaigns();
        $this->registerCreateCampaign();
        $this->registerCreateCampaignPage();
        $this->registerDuplicateCampaign();
    }

    /**
     * Get Campaign route
     *
     * @since 4.10.1 Changed permission callback to use validationForGetItem method
     * @since 4.9.0 Add missing schema key to the route level
     * @since 4.0.0
     */
    public function registerGetCampaign()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->getCampaign($request);
                    },
                    'permission_callback' => function (WP_REST_Request $request) {
                        return CampaignPermissions::validationForGetItem($request);
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * Get Campaigns route
     *
     * @since 4.10.1 Changed permission callback to use validationForGetItems method
     * @since 4.0.0
     */
    public function registerGetCampaigns()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGNS,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->getCampaigns($request);
                    },
                    'permission_callback' => function (WP_REST_Request $request) {
                        return CampaignPermissions::validationForGetItems($request);
                    },
                ],
                'args' => [
                    'status' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => ['active', 'draft', 'archived'],
                        ],
                        'default' => ['active'],
                    ],
                    'ids' => [
                        'type' => 'array',
                        'default' => [],
                    ],
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
                    'sortBy' => [
                        'type' => 'string',
                        'enum' => [
                            'date',
                            'amount',
                            'donors',
                            'donations',
                        ],
                        'default' => 'date',
                    ],
                    'orderBy' => [
                        'type' => 'string',
                        'enum' => [
                            'asc',
                            'desc',
                        ],
                        'default' => 'desc',
                    ],
                    'search' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );
    }

    /**
     * Update Campaign route
     *
     * @since 4.0.0
     */
    public function registerUpdateCampaign()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN,
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->updateCampaign($request);
                    },
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => rest_get_endpoint_args_for_schema($this->getSchema(), WP_REST_Server::EDITABLE),
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * Update Campaign route
     *
     * @since 4.9.0 Add missing schema key to the route level
     * @since 4.0.0
     */
    public function registerMergeCampaigns()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/merge',
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->mergeCampaigns($request);
                    },
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'campaignsToMergeIds' => [
                        'type' => 'array',
                        'required' => true,
                        'items' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * Create Campaign route
     *
     * @since 4.9.0 Add missing schema key to the route level
     * @since 4.0.0
     */
    public function registerCreateCampaign()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGNS,
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->createCampaign($request);
                    },
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => array_merge(rest_get_endpoint_args_for_schema($this->getSchema(), WP_REST_Server::CREATABLE), [
                    'logo' => [
                        'type' => 'string',
                        'format' => 'uri',
                        'required' => false,
                    ],
                    'image' => [
                        'type' => 'string',
                        'format' => 'uri',
                        'required' => false,
                    ],
                    'startDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => false,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new DateTime($value);
                        },
                    ],
                    'endDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => false,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new DateTime($value);
                        },
                    ],
                ] ),
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * @since 4.9.0 Add missing schema key to the route level
     * @since 4.2.0
     */
    public function registerDuplicateCampaign()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/duplicate',
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->duplicateCampaign($request);
                    },
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * @since 4.9.0 Set proper JSON Schema version
     * @since 4.0.0
     */
    public function getSchema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/campaign',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Campaign ID', 'give'),
                ],
                'title' => [
                    'type' => 'string',
                    'description' => esc_html__('Campaign title', 'give'),
                    'minLength' => 3,
                    'maxLength' => 128,
                    'errorMessage' => esc_html__('Campaign title is required', 'give'),
                ],
                'status' => [
                    'enum' => ['active', 'inactive', 'draft', 'pending', 'processing', 'failed', 'archived'],
                    'description' => esc_html__('Campaign status', 'give'),
                ],
                'shortDescription' => [
                    'type' => 'string',
                    'description' => esc_html__('Campaign short description', 'give'),
                    'maxLength' => 120,
                ],
                'primaryColor' => [
                    'type' => 'string',
                    'description' => esc_html__('Primary color for the campaign', 'give'),
                ],
                'secondaryColor' => [
                    'type' => 'string',
                    'description' => esc_html__('Secondary color for the campaign', 'give'),
                ],
                'goal' => [
                    'type' => 'number',
                    'description' => esc_html__('Campaign goal', 'give'),
                    'errorMessage' => esc_html__('Must be a number', 'give'),
                ],
                'goalProgress' => [
                    'type' => 'number',
                    'description' => esc_html__('Campaign goal progress', 'give'),
                ],
                'goalType' => [
                    'enum' => [
                        'amount',
                        'donations',
                        'donors',
                        'amountFromSubscriptions',
                        'subscriptions',
                        'donorsFromSubscriptions',
                    ],
                    'description' => esc_html__('Campaign goal type', 'give'),
                ],
                'defaultFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Default campaign form ID', 'give'),
                ],
                'pageId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Campaign page ID', 'give'),
                ],
            ],
            'required' => ['id', 'title', 'goal', 'goalType'],
            'allOf' => [
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'amount',
                            ],
                        ],
                    ],
                    'then' => [
                        'properties' => [
                            'goal' => [
                                //'minimum' => 1,
                                'type' => 'number',
                            ],
                        ],
                        'errorMessage' => [
                            'properties' => [
                                'goal' => esc_html__('Goal amount must be greater than 0', 'give'),
                            ],
                        ],
                    ],
                ],
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'donations',
                            ],
                        ],
                    ],
                    'then' => [
                        'properties' => [
                            'goal' => [
                                'minimum' => 1,
                                'type' => 'number',
                            ],
                        ],
                        'errorMessage' => [
                            'properties' => [
                                'goal' => esc_html__('Number of donations must be greater than 0', 'give'),
                            ],
                        ],
                    ],
                ],
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'donors',
                            ],
                        ],
                    ],
                    'then' => [
                        'properties' => [
                            'goal' => [
                                'minimum' => 1,
                                'type' => 'number',
                            ],
                        ],
                        'errorMessage' => [
                            'properties' => [
                                'goal' => esc_html__('Number of donors must be greater than 0', 'give'),
                            ],
                        ],
                    ],
                ],
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'amountFromSubscriptions',
                            ],
                        ],
                    ],
                    'then' => [
                        'properties' => [
                            'goal' => [
                                'minimum' => 1,
                                'type' => 'number',
                            ],
                        ],
                        'errorMessage' => [
                            'properties' => [
                                'goal' => esc_html__('Goal recurring amount must be greater than 0', 'give'),
                            ],
                        ],
                    ],
                ],
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'subscriptions',
                            ],
                        ],
                    ],
                    'then' => [
                        'properties' => [
                            'goal' => [
                                'minimum' => 1,
                                'type' => 'number',
                            ],
                        ],
                        'errorMessage' => [
                            'properties' => [
                                'goal' => esc_html__('Number of recurring donations must be greater than 0', 'give'),
                            ],
                        ],
                    ],
                ],
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'donorsFromSubscriptions',
                            ],
                        ],
                    ],
                    'then' => [
                        'properties' => [
                            'goal' => [
                                'minimum' => 1,
                                'type' => 'number',
                            ],
                        ],
                        'errorMessage' => [
                            'properties' => [
                                'goal' => esc_html__('Number of recurring donors must be greater than 0', 'give'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @since 4.0.0
     */
    public function registerCreateCampaignPage(): void
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/page',
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->campaignRequestController->createCampaignPage($request);
                    },
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
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
}
