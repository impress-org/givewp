<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignRoute;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetCampaignRevenue implements RestRoute
{
    /**
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/revenue',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                        'sanitize_callback' => 'absint',
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

        if (!$campaign) {
            return new WP_REST_Response([], 404);
        }

        $query = new CampaignDonationQuery($campaign);
        $results = $query->getDonationsByDay();

        $data = [];
        if (!empty($results)) {
            foreach ($results as $result) {
                $data[] = [
                    'date' => $result->date,
                    'amount' => $result->amount
                ];
            }
        }

        return new WP_REST_Response($data, 200);
    }
}
