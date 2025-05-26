<?php

namespace Give\API\REST\V3\Routes\Donors;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donors\DonorStatisticsQuery;
use Give\Donors\Models\Donor;
use Give\Framework\Support\Facades\DateTime\Temporal;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonorStatisticsController extends WP_REST_Controller
{
    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = DonorRoute::NAMESPACE;
        $this->rest_base = DonorRoute::BASE;
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/statistics', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => '__return_true',
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'mode' => [
                        'description' => __('The mode of donations to filter by "live" or "test".',
                            'give'),
                        'type' => 'string',
                        'default' => 'live',
                        'enum' => ['live', 'test'],
                    ],
                    'campaignId' => [
                        'description' => __('The ID of the campaign to filter donors by. Zero or empty values will be considered as "all campaigns".',
                            'give'),
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'rangeInDays' => [
                        'type' => 'integer',
                        'required' => false,
                        'sanitize_callback' => 'absint',
                        'default' => 0, // Zero to mean "all time".
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get a single donor statistics.
     *
     * @unreleased
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function get_item($request)
    {
        $donor = Donor::find($request->get_param('id'));
        if ( ! $donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $query = new DonorStatisticsQuery($donor, $request->get_param('mode'));
        
        if ($campaign = Campaign::find($request->get_param('campaignId'))) {
            $query->filterByCampaign($campaign);
        }

        if ( ! $request->get_param('rangeInDays')) {
            $item = [
                'lifetimeDonations' => $query->getLifetimeDonationsAmount(),
                'highestDonation' => $query->getHighestDonationAmount(),
                'averageDonation' => $query->getAverageDonationAmount(),
            ];
        } else {
            $days = $request->get_param('rangeInDays');
            $date = new DateTimeImmutable('now', wp_timezone());
            $interval = DateInterval::createFromDateString("-$days days");
            $period = new DatePeriod($date, $interval, 1);

            $item = array_map(function ($targetDate) use ($query, $interval) {
                $query = $query->between(
                    Temporal::withStartOfDay($targetDate->add($interval)),
                    Temporal::withEndOfDay($targetDate)
                );

                return [
                    'lifetimeDonations' => $query->getLifetimeDonationsAmount(),
                    'highestDonation' => $query->getHighestDonationAmount(),
                    'averageDonation' => $query->getAverageDonationAmount(),
                ];
            }, iterator_to_array($period));
        }

        $response = $this->prepare_item_for_response($item, $request);
        return rest_ensure_response($response);
    }

    /**
     * @unreleased
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $self_url = rest_url(sprintf('%s/%s/%d/%s', $this->namespace, $this->rest_base, $request->get_param('id'),
            'statistics'));
        $links = [
            'self' => ['href' => $self_url],
        ];

        $response = new WP_REST_Response($item);
        $response->add_links($links);

        return $response;
    }
}
