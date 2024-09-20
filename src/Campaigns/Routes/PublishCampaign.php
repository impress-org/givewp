<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Routes\Traits\RestResponses;
use Give\Campaigns\ValueObjects\CampaignStatus;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class PublishCampaign implements RestRoute
{
    use RestResponses;

    /** @var string */
    protected $endpoint = 'campaigns/(?P<id>[0-9]+)/publish';

    /** @var Campaign */
    protected $campaign;

    /**
     * @unreleased
     */
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
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_Rest_Response
    {
        $this->campaign->status = CampaignStatus::ACTIVE();
        $this->campaign->save();

        $response = json_encode($this->campaign->toArray());

        return new WP_REST_Response($response, 200);
    }
}
