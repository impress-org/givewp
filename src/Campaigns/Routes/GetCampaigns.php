<?php

namespace Give\Campaigns\Routes;

use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetCampaigns implements RestRoute
{

    /** @var string */
    protected $endpoint = 'campaigns';

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
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1,
                        'maximum' => 100,
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
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = give(CampaignRepository::class)->prepareQuery();

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $campaigns = $query->getAll() ?? [];
        $totalCampaigns = empty($campaigns) ? 0 : Campaign::query()->count();
        $totalPages = (int)ceil($totalCampaigns / $perPage);

        $response = rest_ensure_response($campaigns);
        $response->header('X-WP-Total', $totalCampaigns);
        $response->header('X-WP-TotalPages', $totalPages);

        return $response;
    }
}
