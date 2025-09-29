<?php

namespace Give\Donors\Endpoints;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class ListDonorStats extends Endpoint
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    public function getDonorStatistics(): array
    {
        // Count total donors from the donors table
        $totalDonors = DB::table('give_donors')->count();
    
        // Count donors who have made recurring donations
        $recurringDonors = DB::table('give_subscriptions')->count('DISTINCT customer_id');
    
        $oneTimeDonors = $totalDonors - $recurringDonors;
    
        return [
            'donorsCount' => (int) $totalDonors,
            'oneTimeDonorsCount' => (int) $oneTimeDonors,
            'subscribersCount' => (int) $recurringDonors,
        ];
    }
    
}
