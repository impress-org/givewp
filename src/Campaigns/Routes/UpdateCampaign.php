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

    /** @var Campaign */
    protected $campaign;

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
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function ($id) {
                            return $this->campaign = Campaign::find($id);
                        },
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
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $statusMap = [
            'draft' => CampaignStatus::DRAFT(),
            'active' => CampaignStatus::ACTIVE(),
        ];

        foreach ($request->get_params() as $key => $value) {
            switch ($key) {
                case 'id':
                    break;
                case 'status':
                    if (array_key_exists($value, $statusMap)) {
                        $this->campaign->setAttribute($key, $statusMap[$value]);
                    }
                    break;
                default:
                    if ($this->campaign->hasProperty($key)) {
                        $this->campaign->setAttribute($key, $value);
                    }
            }
        }

        if ($this->campaign->isDirty()) {
            $this->campaign->save();
        }

        return new WP_REST_Response($this->campaign->toArray());
    }
}
