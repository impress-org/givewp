<?php

namespace Give\Donors\Endpoints;

use Give\Framework\Database\DB;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.11.0
 */
class ListDonorsStats extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donors/stats';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @since 4.11.0
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                ],
            ]
        );
    }

    /**
     * @since 4.11.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $statistics = $this->getDonorStatistics();

        return new WP_REST_Response($statistics);
    }

    /**
     * Get all donor statistics in a single optimized query
     *
     * @since 4.11.0
     */
    public function getDonorStatistics(): array
    {
        $query = DB::table('give_donors', 'donors')
            ->leftJoin('give_subscriptions as subscriptions', 'donors.id', 'subscriptions.customer_id')
            ->selectRaw('SELECT
                COUNT(DISTINCT donors.id) as total_donors,
                COUNT(DISTINCT subscriptions.customer_id) as recurring_donors,
                COUNT(DISTINCT donors.id) - COUNT(DISTINCT subscriptions.customer_id) as one_time_donors
            ');

        $result = $query->get();

         // Handle case when no results are found
         if (!$result) {
            return [
                'donorsCount' => 0,
                'oneTimeDonorsCount' => 0,
                'subscribersCount' => 0,
            ];
        }

        return [
            'donorsCount' => (int) $result->total_donors,
            'oneTimeDonorsCount' => (int) $result->one_time_donors,
            'subscribersCount' => (int) $result->recurring_donors,
        ];
    }

}
