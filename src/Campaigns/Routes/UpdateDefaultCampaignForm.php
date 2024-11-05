<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignRoute;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class UpdateDefaultCampaignForm
{
    /**
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/updateDefaultForm',
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
                    'formId' => [
                        'type' => 'integer',
                        'required' => true,
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
    public function handleRequest($request): WP_REST_Response
    {
        $campaign = Campaign::find($request->get_param('id'));
        give(CampaignRepository::class)->updateDefaultCampaignForm($campaign, $request->get_param('formId'));

        return new WP_REST_Response($campaign->toArray());
    }
}
