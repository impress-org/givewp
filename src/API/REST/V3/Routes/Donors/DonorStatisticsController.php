<?php

namespace Give\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use WP_REST_Controller;
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
                ],
            ],
        ]);
    }

    /**
     * @unreleased
     */
    public function get_item($request): WP_REST_Response
    {
        $donorId = $request->get_param('id');
        $donor = Donor::find($donorId);

        if ( ! $donor) {
            return new WP_REST_Response([], 404);
        }

        $item = [
            'lifetimeDonations' => 300,
            'highestDonation' => 250,
            'averageDonation' => 150,
        ];

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
