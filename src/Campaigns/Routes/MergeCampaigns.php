<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignRoute;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class MergeCampaigns implements RestRoute
{
    /**
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/merge',
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
                    ],
                    'campaignsToMergeIds' => [
                        'type' => 'array',
                        'required' => true,
                        'items' => [
                            'type' => 'integer',
                        ],
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
        $destinationCampaign = Campaign::find($request->get_param('id'));
        $campaignsToMerge = Campaign::query()->whereIn('id', $request->get_param('campaignsToMergeIds'))->getAll();

        $campaignsMerged = $destinationCampaign->merge(...$campaignsToMerge);

        return new WP_REST_Response($campaignsMerged);
    }
}
