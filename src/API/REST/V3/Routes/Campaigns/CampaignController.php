<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use DateTime;
use Give\API\REST\V3\Routes\Campaigns\Permissions\CampaignPermissions;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\API\REST\V3\Routes\Campaigns\RequestControllers\CampaignRequestController;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class CampaignController extends WP_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $rest_base;

    /**
     * @var CampaignRequestController
     */
    protected $requestController;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = CampaignRoute::NAMESPACE;
        $this->rest_base = CampaignRoute::CAMPAIGNS;
        $this->requestController = new CampaignRequestController();
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        // Single campaign routes
        register_rest_route($this->namespace, '/' . CampaignRoute::CAMPAIGN, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => function (WP_REST_Request $request) {
                    return CampaignPermissions::validationForGetItem($request);
                },
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => rest_get_endpoint_args_for_schema($this->get_item_schema(), WP_REST_Server::EDITABLE),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        // Collections and create
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_items'],
                'permission_callback' => function (WP_REST_Request $request) {
                    return CampaignPermissions::validationForGetItems($request);
                },
                'args' => $this->get_collection_params(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => array_merge(
                    rest_get_endpoint_args_for_schema($this->get_item_schema(), WP_REST_Server::CREATABLE),
                    [
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
                            'format' => 'date-time',
                            'required' => false,
                            'validate_callback' => 'rest_parse_date',
                            'sanitize_callback' => function ($value) {
                                return new DateTime($value, wp_timezone());
                            },
                        ],
                        'endDateTime' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'required' => false,
                            'validate_callback' => 'rest_parse_date',
                            'sanitize_callback' => function ($value) {
                                return new DateTime($value, wp_timezone());
                            },
                        ],
                    ]
                ),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        // Merge campaigns
        register_rest_route($this->namespace, '/' . CampaignRoute::CAMPAIGN . '/merge', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'merge_items'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
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
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        // Create campaign page
        register_rest_route($this->namespace, '/' . CampaignRoute::CAMPAIGN . '/page', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_page'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
        ]);

        // Duplicate campaign
        register_rest_route($this->namespace, '/' . CampaignRoute::CAMPAIGN . '/duplicate', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'duplicate_item'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * @unreleased
     */
    public function get_items($request)
    {
        return $this->requestController->getCampaigns($request);
    }

    /**
     * @return WP_REST_Response|\WP_Error
     */
    public function get_item($request)
    {
        return $this->requestController->getCampaign($request);
    }

    /**
     * @unreleased
     */
    public function create_item($request): WP_REST_Response
    {
        return $this->requestController->createCampaign($request);
    }

    /**
     * @return WP_REST_Response|\WP_Error
     */
    public function update_item($request): WP_REST_Response
    {
        return $this->requestController->updateCampaign($request);
    }

    /**
     * @unreleased
     */
    public function merge_items($request): WP_REST_Response
    {
        return $this->requestController->mergeCampaigns($request);
    }

    /**
     * @unreleased
     */
    public function create_page($request): WP_REST_Response
    {
        return $this->requestController->createCampaignPage($request);
    }

    /**
     * @unreleased
     */
    public function duplicate_item($request): WP_REST_Response
    {
        return $this->requestController->duplicateCampaign($request);
    }

    /**
     * @unreleased
     */
    public function get_collection_params(): array
    {
        return [
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
                'enum' => ['asc', 'desc'],
                'default' => 'desc',
            ],
            'search' => [
                'type' => 'string',
                'default' => '',
            ],
        ];
    }

    /**
     * @unreleased
     */
    public function get_item_schema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/campaign',
            'description' => esc_html__('Campaign routes for CRUD operations', 'give'),
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Campaign ID', 'give'),
                    'readonly' => true,
                ],
                'pagePermalink' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Campaign page permalink', 'give'),
                    'readonly' => true,
                ],
                'title' => [
                    'type' => 'string',
                    'description' => esc_html__('Campaign title', 'give'),
                    'minLength' => 3,
                    'maxLength' => 128,
                    'required' => true,
                ],
                'status' => [
                    'enum' => [CampaignStatus::ACTIVE, CampaignStatus::ARCHIVED],
                    'description' => esc_html__('Campaign status', 'give'),
                    'default' => CampaignStatus::ACTIVE,
                ],
                'shortDescription' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Campaign short description', 'give'),
                    'maxLength' => 120,
                ],
                'longDescription' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Campaign long description', 'give'),
                ],
                'logo' => [
                    'type' => ['string', 'null'],
                    'format' => 'uri',
                    'description' => esc_html__('Campaign logo URL', 'give'),
                ],
                'image' => [
                    'type' => ['string', 'null'],
                    'format' => 'uri',
                    'description' => esc_html__('Campaign featured image URL', 'give'),
                ],
                'primaryColor' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Primary color for the campaign', 'give'),
                ],
                'secondaryColor' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Secondary color for the campaign', 'give'),
                ],
                'goal' => [
                    'type' => 'integer',
                    'description' => esc_html__('Campaign goal', 'give'),
                    'errorMessage' => esc_html__('Must be a number', 'give'),
                    'required' => true,
                ],
                'goalType' => [
                    'enum' => array_values(CampaignGoalType::toArray()),
                    'description' => esc_html__('Campaign goal type', 'give'),
                    'required' => true,
                ],
                'goalStats' => [
                    'type' => 'object',
                    'description' => esc_html__('Campaign goal statistics', 'give'),
                    'readonly' => true,
                    'properties' => [
                        'actual' => [
                            'type' => ['integer', 'number'],
                            'description' => esc_html__('Actual progress value', 'give'),
                        ],
                        'actualFormatted' => [
                            'type' => ['string', 'number'],
                            'description' => esc_html__('Formatted actual progress', 'give'),
                        ],
                        'percentage' => [
                            'type' => 'number',
                            'description' => esc_html__('Progress percentage', 'give'),
                        ],
                        'goal' => [
                            'type' => ['integer', 'number'],
                            'description' => esc_html__('Goal value', 'give'),
                        ],
                        'goalFormatted' => [
                            'type' => ['string', 'number'],
                            'description' => esc_html__('Formatted goal value', 'give'),
                        ],
                    ],
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => array_values(CampaignType::toArray()),
                    'description' => esc_html__('Campaign type', 'give'),
                    'default' => CampaignType::CORE,
                ],
                'defaultFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Default campaign form ID', 'give'),
                    'readonly' => true,
                ],
                'defaultFormTitle' => [
                    'type' => 'string',
                    'description' => esc_html__('Default campaign form title', 'give'),
                    'readonly' => true,
                ],
                'pageId' => [
                    'type' => ['integer', 'null'],
                    'description' => esc_html__('Campaign page ID', 'give'),
                ],
                'startDate' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Campaign start date', 'give'),
                    'format' => 'date-time',
                ],
                'endDate' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Campaign end date', 'give'),
                    'format' => 'date-time',
                ],
                'createdAt' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Campaign creation date (Y-m-d H:i:s)', 'give'),
                    'format' => 'date-time',
                ],
            ],
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
                                'type' => 'integer',
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
                                'type' => 'integer',
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
                                'type' => 'integer',
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
                                'type' => 'integer',
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
                                'type' => 'integer',
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
}
