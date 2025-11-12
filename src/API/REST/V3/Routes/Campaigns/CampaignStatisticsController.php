<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\Facades\DateTime\Temporal;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class CampaignStatisticsController extends WP_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = CampaignRoute::NAMESPACE;
    }

    /**
     * @since 4.13.0 add schema
     * @since 4.0.0
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . CampaignRoute::CAMPAIGN . '/statistics',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_items'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                    'args' => [
                        'id' => [
                            'type' => 'integer',
                            'required' => true,
                            'sanitize_callback' => 'absint',
                        ],
                        'rangeInDays' => [
                            'type' => 'integer',
                            'required' => false,
                            'sanitize_callback' => 'absint',
                            'default' => 0,
                        ],
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
    }

    /**
     * @since 4.13.0 return 404 error if campaign is not found
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function get_items($request): WP_REST_Response
    {
        $campaign = Campaign::find($request->get_param('id'));

        if (!$campaign) {
            $response = new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);

            return rest_ensure_response($response);
        }

        $query = new CampaignDonationQuery($campaign);

        if (!$request->get_param('rangeInDays')) {
            $data = [[
                'amountRaised' => $query->sumIntendedAmount(),
                'donationCount' => $query->countDonations(),
                'donorCount' => $query->countDonors(),
            ]];

            $items = new WP_REST_Response($data);

            return rest_ensure_response($items);
        }

        $days = (int)$request->get_param('rangeInDays');
        $date = new DateTimeImmutable('now', wp_timezone());
        $interval = DateInterval::createFromDateString("-$days days");
        $period = new DatePeriod($date, $interval, 1);

        $data = array_map(function ($targetDate) use ($query, $interval) {
            $rangeQuery = $query->between(
                Temporal::withStartOfDay($targetDate->add($interval)),
                Temporal::withEndOfDay($targetDate)
            );

            return [
                'amountRaised' => $rangeQuery->sumIntendedAmount(),
                'donationCount' => $rangeQuery->countDonations(),
                'donorCount' => $rangeQuery->countDonors(),
            ];
        }, iterator_to_array($period));

        $items = new WP_REST_Response($data);

        return rest_ensure_response($items);
    }

    /**
     * @since 4.13.0
     */
    public function get_item_schema(): array
    {
        return [
            'title' => 'givewp/campaign-statistics',
            'description' => esc_html__('Provides statistics for a specific campaign.', 'give'),
            'type' => 'array',
            'readonly' => true,
            'items' => [
                'type' => 'object',
                'properties' => [
                    'amountRaised' => [
                        'type' => ['integer', 'number'],
                        'description' => esc_html__('The amount raised for the campaign.', 'give'),
                    ],
                    'donationCount' => [
                        'type' => 'integer',
                        'description' => esc_html__('The number of donations for the campaign.', 'give'),
                    ],
                    'donorCount' => [
                        'type' => 'integer',
                        'description' => esc_html__('The number of donors for the campaign.', 'give'),
                    ],
                ],
            ],
        ];
    }
}


