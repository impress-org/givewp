<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use DateTime;
use Exception;
use Give\API\REST\V3\Routes\Campaigns\Permissions\CampaignPermissions;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\API\REST\V3\Routes\Campaigns\ViewModels\CampaignViewModel;
use Give\API\REST\V3\Support\Headers;
use Give\API\REST\V3\Support\Item;
use Give\Campaigns\Actions\DuplicateCampaign;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use WP_Error;
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
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = CampaignRoute::NAMESPACE;
        $this->rest_base = CampaignRoute::CAMPAIGNS;
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        // Single campaign routes
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
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
        $ids = $request->get_param('ids');
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $status = $request->get_param('status');
        $sortBy = $request->get_param('sortBy');
        $orderBy = $request->get_param('orderBy');
        $search = $request->get_param('search');

        $query = Campaign::query();

        $query->whereIn('status', $status);

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($search) {
            $query->whereLike('campaign_title', '%%' . $search . '%%');
        }

        $totalQuery = clone $query;

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        switch ($sortBy) {
            case 'date':
                $query->orderBy('date_created', $orderBy);

                break;
            case 'amount':
                $query
                    ->selectRaw(
                        '(SELECT SUM(amount) FROM %1s WHERE campaign_id = campaigns.id) AS amount',
                        DB::prefix('give_revenue')
                    )
                    ->orderBy('amount', $orderBy);

                break;
            case 'donations':
                $query
                    ->selectRaw(
                        '(SELECT COUNT(donation_id) FROM %1s WHERE campaign_id = campaigns.id) AS donationsCount',
                        DB::prefix('give_revenue')
                    )
                    ->orderBy('donationsCount', $orderBy);

                break;
            case 'donors':
                $postsTable = DB::prefix('posts');
                $metaTable = DB::prefix('give_donationmeta');
                $campaignIdKey = DonationMetaKeys::CAMPAIGN_ID;
                $donorIdKey = DonationMetaKeys::DONOR_ID;

                $query
                    ->selectRaw(
                        "(
                            SELECT COUNT(DISTINCT donorId.meta_value)
                            FROM {$postsTable} AS donation
                            LEFT JOIN {$metaTable} campaignId ON donation.ID = campaignId.donation_id AND campaignId.meta_key = '{$campaignIdKey}'
                            LEFT JOIN {$metaTable} donorId ON donation.ID = donorId.donation_id AND donorId.meta_key = '{$donorIdKey}'
                            WHERE post_type = 'give_payment'
                            AND donation.post_status IN ('publish', 'give_subscription')
                            AND campaignId.meta_value = campaigns.id
                        ) AS donorsCount"
                    )
                    ->orderBy('donorsCount', $orderBy);

                break;
        }

        $campaigns = $query->getAll() ?? [];

        $ids = array_map(function ($campaign) {
            return $campaign->id;
        }, $campaigns);

        $campaignsData = !empty($ids)
            ? CampaignsDataRepository::campaigns($ids)
            : null;

        $campaigns = array_map(function ($campaign) use ($campaignsData, $request) {
            $view = new CampaignViewModel($campaign);

            if ($campaignsData) {
                $view->setData($campaignsData);
            }

            $item = $view->exports();

            return $this->prepare_response_for_collection(
                $this->prepare_item_for_response($item, $request)
            );
        }, $campaigns);

        $totalCampaigns = empty($campaigns) ? 0 : $totalQuery->count();
        $response = rest_ensure_response($campaigns);
        $response = Headers::addPagination($response, $request, $totalCampaigns, $perPage, $this->rest_base);

        return $response;
    }

    /**
     * @unreleased
     *
     * @return WP_REST_Response|\WP_Error
     */
    public function get_item($request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if (!$campaign) {
            $response = new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);

            return rest_ensure_response($response);
        }

        if (!$campaign->status->isActive() && !CampaignPermissions::canViewPrivate()) {
            $response = new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to view this campaign.', 'give'),
                ['status' => CampaignPermissions::authorizationStatusCode()]
            );

            return rest_ensure_response($response);
        }

        $item = (new CampaignViewModel($campaign))->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @unreleased
     */
    public function prepare_item_for_response($item, $request)
    {
        try {
            $campaignId = $item['id'] ?? $request->get_param('id') ?? null;

            if ($campaignId) {
                $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $campaignId));

                $links = [
                    'self' => ['href' => $self_url]
                ];
            } else {
                $links = [];
            }

            $itemWithDatesFormatted = Item::formatDatesForResponse($item, ['startDate', 'endDate', 'createdAt']);

            $response = new WP_REST_Response($itemWithDatesFormatted);

            if (!empty($links)) {
                $response->add_links($links);
            }

            $response->data = $this->add_additional_fields_to_object($response->data, $request);

            return $response;
        } catch (Exception $e) {
            return new WP_Error(
                'prepare_item_for_response_error',
                sprintf(
                    __('Error while preparing campaign for response: %s', 'give'),
                    $e->getMessage()
                ),
                ['status' => 400]
            );
        }
    }

    /**
     * @unreleased
     *
     * @return WP_REST_Response|\WP_Error
     */
    public function create_item($request)
    {
        try {
            $campaign = Campaign::create([
                'type' => CampaignType::CORE(),
                'title' => $request->get_param('title'),
                'shortDescription' => $request->get_param('shortDescription') ?? '',
                'longDescription' => '',
                'logo' => '',
                'image' => $request->get_param('image') ?? '',
                'primaryColor' => '#0b72d9',
                'secondaryColor' => '#27ae60',
                'goal' => (int)$request->get_param('goal'),
                'goalType' => new CampaignGoalType($request->get_param('goalType')),
                'status' => CampaignStatus::ACTIVE(),
                'startDate' => $request->get_param('startDateTime'),
                'endDate' => $request->get_param('endDateTime'),
            ]);

            $fieldsUpdate = $this->update_additional_fields_for_object($campaign, $request);

            if (is_wp_error($fieldsUpdate)) {
                return $fieldsUpdate;
            }

            $item = (new CampaignViewModel($campaign))->exports();

            $response = $this->prepare_item_for_response($item, $request);
            $response->set_status(201);

            return rest_ensure_response($response);
        } catch (Exception $e) {
            return new WP_Error('create_campaign_error', __('Error while creating campaign', 'give'), ['status' => 400]);
        }
    }

    /**
     * @return WP_REST_Response|\WP_Error
     */
    public function update_item($request)
    {
        $campaign = Campaign::find($request->get_param('id'));

        if (!$campaign) {
            $response = new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);

            return rest_ensure_response($response);
        }

        $statusMap = [
            'archived' => CampaignStatus::ARCHIVED(),
            'draft' => CampaignStatus::DRAFT(),
            'active' => CampaignStatus::ACTIVE(),
        ];

        foreach ($request->get_params() as $key => $value) {
            switch ($key) {
                case 'id':
                    break;
                case 'status':
                    $status = array_key_exists($value, $statusMap)
                        ? $statusMap[$value]
                        : CampaignStatus::DRAFT();

                    $campaign->status = $status;

                    break;
                case 'goal':
                    $campaign->goal = (int)$value;
                    break;
                case 'goalType':
                    $campaign->goalType = new CampaignGoalType($value);
                    break;
                case 'defaultFormId':
                    give(CampaignRepository::class)->updateDefaultCampaignForm(
                        $campaign,
                        $request->get_param('defaultFormId')
                    );
                    break;
                case 'pageId':
                    $campaign->pageId = (int)$value;
                    break;
                default:
                    if ($campaign->hasProperty($key)) {
                        $campaign->$key = $value;
                    }
            }
        }

        if ($campaign->isDirty()) {
            $campaign->save();
        }

        $fieldsUpdate = $this->update_additional_fields_for_object($campaign, $request);

        if (is_wp_error($fieldsUpdate)) {
            return $fieldsUpdate;
        }

        $item = (new CampaignViewModel($campaign))->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @unreleased
     *
     * @return WP_REST_Response|\WP_Error
     */
    public function merge_items($request)
    {
        try {
            $destinationCampaign = Campaign::find($request->get_param('id'));

            if (!$destinationCampaign) {
                $response = new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);

                return rest_ensure_response($response);
            }

            $campaignsToMerge = Campaign::query()->whereIn('id', $request->get_param('campaignsToMergeIds'))->getAll();

            $destinationCampaign->merge(...$campaignsToMerge);

            $item = (new CampaignViewModel($destinationCampaign))->exports();

            $response = $this->prepare_item_for_response($item, $request);
            $response->set_status(200);

            return rest_ensure_response($response);
        } catch (Exception $e) {
            return new WP_Error('merge_campaigns_error', __('Error while merging campaigns', 'give'), ['status' => 400]);
        }
    }

    /**
     * @unreleased
     */
    public function duplicate_item($request): WP_REST_Response
    {
        $campaign = Campaign::find((int)$request->get_param('id'));

        if (!$campaign) {
            $response = new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);

            return rest_ensure_response($response);
        }

        $duplicatedCampaign = (new DuplicateCampaign())($campaign);

        $item = array_merge((new CampaignViewModel($duplicatedCampaign))->exports(), [
            'errors' => 0, // needed by the list table
        ]);

        $response = $this->prepare_item_for_response($item, $request);
        $response->set_status(201);

        return rest_ensure_response($response);
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
        $schema = [
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
                    'type' => 'string',
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
                    'type' => 'string',
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

        return $this->add_additional_fields_schema($schema);
    }
}
