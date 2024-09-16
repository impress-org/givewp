<?php

namespace Give\Campaigns\Routes;

use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Routes\Traits\RestResponses;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class UpdateCampaign implements RestRoute
{
    use RestResponses;

    /** @var string */
    protected $endpoint = 'campaigns/(?P<id>[0-9]+)';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'PUT',
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
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $campaignId = $request->get_param('id');
        $campaign = Campaign::find($campaignId);

        if ( ! $campaign) {
            return $this->notFoundResponse(esc_html__(sprintf('Campaign %s not found.', $campaignId), 'give'));
        }

        try {
            $title = $request->get_param('title');
            $campaign->title = $title ? $request->get_param('title') : $campaign->title;

            $campaign->save();

            $response = json_encode($campaign->toArray());
        } catch (Exception $e) {
            return $this->badRequestResponse($e);
        }

        return new WP_REST_Response($response);
    }
}
