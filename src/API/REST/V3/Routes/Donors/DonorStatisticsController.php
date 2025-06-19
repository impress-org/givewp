<?php

namespace Give\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donors\DonorStatisticsQuery;
use Give\Donors\Models\Donor;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 4.4.0
 */
class DonorStatisticsController extends WP_REST_Controller
{
    /**
     * @since 4.4.0
     */
    public function __construct()
    {
        $this->namespace = DonorRoute::NAMESPACE;
        $this->rest_base = DonorRoute::BASE;
    }

    /**
     * @since 4.4.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<donorId>[\d]+)/statistics', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'donorId' => [
                        'description' => __('The donor ID.',
                            'give'),
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
                        'description' => __('The ID of the campaign to filter donors by - zero or empty mean "all campaigns".',
                            'give'),
                        'type' => 'integer',
                        'default' => 0,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get a single donor statistics.
     *
     * @since 4.4.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function get_item($request)
    {
        $donor = Donor::find($request->get_param('donorId'));
        if ( ! $donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $query = new DonorStatisticsQuery($donor, $request->get_param('mode'));

        if ($campaign = Campaign::find($request->get_param('campaignId'))) {
            $query = $query->filterByCampaign($campaign);
        }

        $item = [
            'donations' => [
                'lifetimeAmount' => $query->getLifetimeDonationsAmount(),
                'highestAmount' => $query->getHighestDonationAmount(),
                'averageAmount' => $query->getAverageDonationAmount(),
                'count' => $query->getDonationsCount(),
                'first' => $query->getFirstDonation(),
                'last' => $query->getLastDonation()
            ],
            'donorType' => $query->getDonorType(),
            'preferredPaymentMethod' => $query->preferredPaymentMethod(),
        ];

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @since 4.4.0
     *
     * @param WP_REST_Request $request
     *
     * @return bool
     */
    public function get_item_permissions_check($request): bool
    {
        return current_user_can('view_give_reports');
    }

    /**
     * @since 4.4.0
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $self_url = rest_url(sprintf('%s/%s/%d/%s', $this->namespace, $this->rest_base, $request->get_param('donorId'),
            'statistics'));

        $self_url = add_query_arg([
            'mode' => $request->get_param('mode'),
            'campaignId' => $request->get_param('campaignId'),
        ], $self_url);

        $links = [
            'self' => ['href' => $self_url],
        ];

        $response = new WP_REST_Response($item);
        $response->add_links($links);

        return $response;
    }
}
