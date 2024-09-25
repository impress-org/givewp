<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign as CampaignModel;
use Give\Campaigns\ValueObjects\CampaignStatus;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class Campaign implements RestRoute
{

    /** @var string */
    protected $endpoint = 'campaigns/(?P<id>[0-9]+)';

    /**
     * @unreleased
     */
    public function registerRoute(): void
    {
        // Get campaign
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleGetRequest'],
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


        // Update Campaign
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'handleUpdateRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @return WP_Error | WP_REST_Response
     *
     * @throws Exception
     */
    public function handleGetRequest(WP_REST_Request $request)
    {
        $campaign = CampaignModel::find($request->get_param('id'));

        if ( ! $campaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        return new WP_REST_Response($campaign->toArray());
    }

    /**
     * @unreleased
     *
     * @return WP_Error | WP_REST_Response
     *
     * @throws Exception
     */
    public function handleUpdateRequest(WP_REST_Request $request)
    {
        $campaign = CampaignModel::find($request->get_param('id'));

        if ( ! $campaign) {
            return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
        }

        $statusMap = [
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

                    $campaign->setAttribute('status', $status);

                    break;
                default:
                    if ($campaign->hasProperty($key)) {
                        $campaign->setAttribute($key, $value);
                    }
            }
        }

        if ($campaign->isDirty()) {
            $campaign->save();
        }

        return new WP_REST_Response($campaign->toArray());
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
                    'enum' => ['active', 'inactive', 'draft', 'pending', 'processing', 'failed'],
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
                    'errorMessage' => esc_html__('Campaign goal is required', 'give')
                ],
                'goalType' => [
                    'enum' => ['amount', 'donation', 'donors'],
                    'description' => esc_html__('Campaign goal type', 'give'),
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
                                'goal' => esc_html__('Goal amount is required', 'give'),
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
                                'goal' => esc_html__('Number of donors is required', 'give'),
                            ],
                        ],
                    ],
                ],
                [
                    'if' => [
                        'properties' => [
                            'goalType' => [
                                'const' => 'donation',
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
                                'goal' => esc_html__('Number of donations is required', 'give'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
