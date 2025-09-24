<?php

namespace Give\Donations\Endpoints;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.10.0
 */
class ListDonationsStats extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations/stats';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @since 4.10.0
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
                    'testMode' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => give_is_test_mode(),
                    ],
                ],
            ]
        );
    }

    /**
     * @since 4.10.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $statistics = $this->getDonationStatistics();

        return new WP_REST_Response($statistics);
    }

    /**
     * Get all donation statistics in a single optimized query
     *
     * @since 4.10.0
     */
    public function getDonationStatistics(): array
    {
        $testMode = $this->request->get_param('testMode');

        $query = DB::table('posts')
            ->where('post_type', 'give_payment')
            ->where('post_status', 'trash', '<>'); // Exclude trash items

        // Filter by test mode and subscription type
        $query->attachMeta(
            'give_donationmeta',
            'ID',
            'donation_id',
            [DonationMetaKeys::MODE()->getValue(), DonationMetaKeys::MODE()->getKeyAsCamelCase()],
            [DonationMetaKeys::SUBSCRIPTION_ID()->getValue(), DonationMetaKeys::SUBSCRIPTION_ID()->getKeyAsCamelCase()]
        );

        if ($testMode) {
            $query->where('give_donationmeta_attach_meta_mode.meta_value', 'test');
        } else {
            $query->where(function ($query) {
                $query->whereIsNull('give_donationmeta_attach_meta_mode.meta_value')
                    ->orWhere('give_donationmeta_attach_meta_mode.meta_value', 'test', '<>');
            });
        }

        // Use CASE WHEN to count different types in a single query
        $query->selectRaw('
            COUNT(*) as total_donations,
            SUM(CASE WHEN give_donationmeta_attach_meta_subscriptionId.meta_value = 0 THEN 1 ELSE 0 END) as one_time_donations,
            SUM(CASE WHEN give_donationmeta_attach_meta_subscriptionId.meta_value != 0 THEN 1 ELSE 0 END) as recurring_donations
        ');

        $result = $query->get();

        // Handle case when no results are found
        if (!$result) {
            return [
                'donationsCount' => 0,
                'oneTimeDonationsCount' => 0,
                'recurringDonationsCount' => 0,
            ];
        }

        return [
            'donationsCount' => (int) $result->total_donations,
            'oneTimeDonationsCount' => (int) $result->one_time_donations,
            'recurringDonationsCount' => (int) $result->recurring_donations,
        ];
    }
}
