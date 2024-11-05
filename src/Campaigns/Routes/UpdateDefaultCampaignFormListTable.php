<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\V2\Endpoints\Endpoint;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class UpdateDefaultCampaignFormListTable extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/forms/updateDefaultCampaignForm/(?P<id>\d+)';

    /**
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            'admin/forms/updateDefaultCampaignForm/(?P<id>\d+)',
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'handleRequest'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => __('The ID of the new default form for the campaign.', 'give'),
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
        $formId = $request->get_param('id');
        $campaign = Campaign::findByFormId($formId);
        give(CampaignRepository::class)->updateDefaultCampaignForm($campaign, $formId);

        return new WP_REST_Response($campaign->toArray());
    }
}
