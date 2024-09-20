<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Routes\Traits\RestResponses;
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

    /** @var Campaign */
    protected $campaign;

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
        foreach ($request->get_params() as $key => $value) {
            if ('id' !== $key) {
                $this->campaign->setAttribute($key, $value);
            }
        }

        if ($this->campaign->isDirty()) {
            $this->campaign->save();
        }

        $response = json_encode($this->campaign->toArray());

        return new WP_REST_Response($response);
    }
}
