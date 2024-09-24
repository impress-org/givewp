<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class UpdateCampaign implements RestRoute
{
    /** @var string */
    protected $endpoint = 'campaigns/(?P<id>[0-9]+)';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                        'validate_callback' => function ($id) {
                            return filter_var($id, FILTER_VALIDATE_INT);
                        },
                    ],
                    'title' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $campaign = Campaign::find($request->get_param('id'));

        if ( ! $campaign) {
            return new WP_REST_Response([
                'message' => __('Campaign not found', 'give'),
            ], 400);
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
            '$schema' => 'http://json-schema.org/draft-04/schema#',
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
                    'default' => 1000000,
                    'minimum' => 100,
                    'description' => esc_html__('Campaign goal', 'give'),
                ],
                'goalType' => [
                    'enum' => ['amount', 'donation', 'donors'],
                    'description' => esc_html__('Campaign goal type', 'give'),
                ],
            ],
            'required' => ['title', 'goal', 'goalType'],
        ];
    }
}
