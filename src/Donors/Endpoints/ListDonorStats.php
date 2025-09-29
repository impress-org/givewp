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
        $recurringDonors = DB::table('posts')
            ->where('post_type', 'give_payment')
            ->where('post_status', 'publish')
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                [DonationMetaKeys::DONOR_ID()->getValue(), DonationMetaKeys::DONOR_ID()->getKeyAsCamelCase()],
                [DonationMetaKeys::SUBSCRIPTION_ID()->getValue(), DonationMetaKeys::SUBSCRIPTION_ID()->getKeyAsCamelCase()]
            )
            ->selectRaw('COUNT(DISTINCT give_donationmeta_attach_meta_donorId.meta_value) as count')
            ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', '0', '<>')
            ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', '0', '<>')
            ->get()
            ->count;
    
        $oneTimeDonors = $totalDonors - $recurringDonors;
    
        return [
            'donorsCount' => (int) $totalDonors,
            'oneTimeDonorsCount' => (int) $oneTimeDonors,
            'subscribersCount' => (int) $recurringDonors,
        ];
    }
    
}
