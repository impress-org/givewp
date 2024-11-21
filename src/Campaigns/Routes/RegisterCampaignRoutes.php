<?php

namespace Give\Campaigns\Routes;

use DateTime;
use Give\Campaigns\Controllers\CampaignRequestController;
use Give\Campaigns\ValueObjects\CampaignRoute;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class RegisterCampaignRoutes
{
    /**
     * @var CampaignRequestController
     */
    protected $campaignRequestController;


    /**
     * @unreleased
     */
    public function __construct(CampaignRequestController $campaignRequestController)
    {
        $this->campaignRequestController = $campaignRequestController;
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->registerGetCampaign();
        $this->registerUpdateCampaign();
        $this->registerGetCampaigns();
        $this->registerCreateCampaign();
    }

    /**
     * Get Campaign route
     *
     * @unreleased
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
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ]
            ]
        );
    }

    /**
     * Get Campaigns route
     *
     * @unreleased
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
                ],
            ]
        );
    }

    /**
     * Update Campaign route
     *
     * @unreleased
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
     * Create Campaign route
     *
     * @unreleased
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
                'args' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'shortDescription' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
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
                ],
            ]
        );
    }


    /**
     * @unreleased
     */
    public function getSchema(): array
    {
        return [
            'title' => 'campaign',
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
                ],
                'goal' => [
                    'type' => 'number',
                    'minimum' => 1,
                    'description' => esc_html__('Campaign goal', 'give'),
                    'errorMessage' => esc_html__('Must be a number', 'give'),
                ],
                'goalProgress' => [
                    'type' => 'number',
                    'readonly' => true,
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
                'enableCampaignPage' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => esc_html__('Enable campaign page for your campaign.', 'give'),
                ],
                'defaultFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Default campaign form ID', 'give'),
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
                                'minimum' => 1,
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
}
