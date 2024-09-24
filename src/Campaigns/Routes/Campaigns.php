<?php

namespace Give\Campaigns\Routes;

use DateTime;
use Exception;
use Give\API\RestRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class Campaigns implements RestRoute
{

    /** @var string */
    protected $endpoint = 'campaigns';

    /**
     * @unreleased
     */
    public function registerRoute()
    {
        // Get Campaigns route
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleGetRequest'],
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

        // Create Campaign route
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'handleCreateRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'startDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => true,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new DateTime($value);
                        },
                    ],
                    'endDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => false,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new DateTime($value);
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
    public function handleGetRequest(WP_REST_Request $request): WP_Rest_Response
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

    /**
     * @unreleased
     *
     * @throws \Give\Framework\Exceptions\Primitives\Exception
     */
    public function handleCreateRequest(WP_REST_Request $request): WP_REST_Response
    {
        $campaign = Campaign::create([
            'type' => CampaignType::CORE(),
            'title' => $request->get_param('title'),
            'shortDescription' => $request->get_param('shortDescription'),
            'longDescription' => '',
            'logo' => '',
            'image' => '',
            'primaryColor' => '',
            'secondaryColor' => '',
            'goal' => 1000,
            'goalType' => 'amount',
            'status' => CampaignStatus::DRAFT(),
            'startDate' => $request->get_param('startDateTime'),
            'endDate' => $request->get_param('endDateTime'),
        ]);

        return new WP_REST_Response($campaign->toArray(), 201);
    }
}
