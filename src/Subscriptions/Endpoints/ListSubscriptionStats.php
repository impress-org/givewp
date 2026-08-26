<?php

namespace Give\Subscriptions\Endpoints;

use Give\Framework\Database\DB;
use Give\Donations\ValueObjects\DonationMetaKeys;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.12.0
 */
class ListSubscriptionStats extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/subscriptions/stats';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @since 4.12.0
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
     * @since 4.12.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $statistics = $this->getSubscriptionStatistics();

        return new WP_REST_Response($statistics);
    }

    /**
     * Get subscription statistics for total contributions and active subscriptions
     *
     * @since 4.12.0
     */
    public function getSubscriptionStatistics(): array
    {
        $testMode = $this->request->get_param('testMode');

        $query = DB::table('posts')
            ->where('post_type', 'give_payment')
            ->whereIn('post_status', ['publish', 'give_subscription']);

        $query->attachMeta(
            'give_donationmeta',
            'ID',
            'donation_id',
            [DonationMetaKeys::AMOUNT()->getValue(), DonationMetaKeys::AMOUNT()->getKeyAsCamelCase()],
            [DonationMetaKeys::SUBSCRIPTION_ID()->getValue(), DonationMetaKeys::SUBSCRIPTION_ID()->getKeyAsCamelCase()],
            [DonationMetaKeys::MODE()->getValue(), DonationMetaKeys::MODE()->getKeyAsCamelCase()]
        );

        if ($testMode) {
            $query->where('give_donationmeta_attach_meta_mode.meta_value', 'test');
        } else {
            $query->where(function ($query) {
                $query->whereIsNull('give_donationmeta_attach_meta_mode.meta_value')
                    ->orWhere('give_donationmeta_attach_meta_mode.meta_value', 'test', '<>');
            });
        }

        $query->whereIsNotNull('give_donationmeta_attach_meta_subscriptionId.meta_value')
              ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', '', '!=')
              ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', '0', '!=');

        $query->leftJoin(
            'give_subscriptions',
            'give_donationmeta_attach_meta_subscriptionId.meta_value',
            's.id',
            's'
        );

        $query->selectRaw('
            SUM(give_donationmeta_attach_meta_amount.meta_value) as total_contributions,
            COUNT(DISTINCT CASE WHEN s.status = "active" THEN s.id END) as active_subscriptions
        ');

        $result = $query->get();

        if (!$result) {
             return [
                'activeSubscriptions' => 0,
                'totalContributions' => 0,
            ];
         }

        return [
            'activeSubscriptions' => (int) $result->active_subscriptions,
             'totalContributions' => (float) $result->total_contributions,
        ];
    }
}




