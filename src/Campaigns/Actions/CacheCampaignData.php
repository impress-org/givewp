<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;

/**
 * @since 4.8.0
 *
 * @uses give_insert_payment hook
 * @uses give_update_payment_status hook
 * @uses give_recurring_add_subscription_payment hook
 * @uses givewp_campaigns_merged hook
 *
 * Action used to update campaign's stats data
 *
 */
class CacheCampaignData
{
    /**
     * @since 4.13.1 added dispatch method
     * @since 4.8.0
     */
    public function __invoke(int $donationId): void
    {
        $donation = Donation::find($donationId);

        if (!$donation) {
            return;
        }

        if ($donation->status->isComplete() || $donation->status->isRenewal()) {
            $this->dispatch($donation->campaignId);
        }
    }

    /**
     * Dispatch the cache campaign data action
     * @since 4.13.1
     */
    public function dispatch(int $campaignId): void
    {
        as_enqueue_async_action('givewp_cache_campaign_data', [$campaignId], 'givewp_campaigns_cache');
    }

    /**
     * Handle campaign cache
     *
     * @since TBD Read the subscriptions cache from the option it is written to; always compute live stats, keep zero rows when a campaign has no donations.
     * @since 4.8.0
     */
    public function handleCache(int $campaignId): void
    {
        // The cache holds live stats only. The queries follow the site's payment mode, so force
        // live for the duration of the job in case test mode was switched on before it ran.
        add_filter('give_is_test_mode', '__return_false', PHP_INT_MAX);

        try {
            $this->refresh($campaignId);
        } finally {
            remove_filter('give_is_test_mode', '__return_false', PHP_INT_MAX);
        }
    }

    /**
     * @since TBD
     */
    private function refresh(int $campaignId): void
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return;
        }

        $campaignsData = get_option('give_campaigns_data', []);
        $campaignsSubscriptionData = get_option('give_campaigns_subscriptions_data', []);

        // Prefill cache structure to ensure keys exist
        $campaignsData = array_merge([
            'amounts' => [],
            'donationsCount' => [],
            'donorsCount' => []
        ], $campaignsData);

        $campaignsSubscriptionData = array_merge([
            'amounts' => [],
            'donationsCount' => [],
            'donorsCount' => []
        ], $campaignsSubscriptionData);

        $donations = CampaignsDataQuery::donations([$campaign->id]);

        $isCached = $isSubscriptionCached = false;

        // Update cache only if it exists for this campaign
        foreach ($campaignsData['amounts'] as $i => $data) {
            if ($data['campaign_id'] == $campaign->id) {
                $isCached = true;

                $campaignsData['amounts'][$i] = $this->row($donations->collectIntendedAmounts(), $campaign->id, 'sum');
                break;
            }
        }

        foreach ($campaignsData['donationsCount'] as $i => $data) {
            if ($data['campaign_id'] == $campaign->id) {
                $campaignsData['donationsCount'][$i] = $this->row($donations->collectDonations(), $campaign->id, 'count');
                break;
            }
        }

        foreach ($campaignsData['donorsCount'] as $i => $data) {
            if ($data['campaign_id'] == $campaign->id) {
                $campaignsData['donorsCount'][$i] = $this->row($donations->collectDonors(), $campaign->id, 'count');
                break;
            }
        }


        // Update campaign subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {

            $subscriptions = CampaignsDataQuery::subscriptions([$campaign->id]);

            foreach ($campaignsSubscriptionData['amounts'] as $i => $data) {
                if ($data['campaign_id'] == $campaign->id) {
                    $isSubscriptionCached = true;

                    $campaignsSubscriptionData['amounts'][$i] = $this->row($subscriptions->collectInitialAmounts(), $campaign->id, 'sum');
                    break;
                }
            }

            foreach ($campaignsSubscriptionData['donationsCount'] as $i => $data) {
                if ($data['campaign_id'] == $campaign->id) {
                    $campaignsSubscriptionData['donationsCount'][$i] = $this->row($subscriptions->collectDonations(), $campaign->id, 'count');
                    break;
                }
            }

            foreach ($campaignsSubscriptionData['donorsCount'] as $i => $data) {
                if ($data['campaign_id'] == $campaign->id) {
                    $campaignsSubscriptionData['donorsCount'][$i] = $this->row($subscriptions->collectDonors(), $campaign->id, 'count');
                    break;
                }
            }
        }


        // Save updated cache
        if ($isCached) {
            update_option('give_campaigns_data', $campaignsData);
        }

        if ($isSubscriptionCached) {
            update_option('give_campaigns_subscriptions_data', $campaignsSubscriptionData);
        }

        if ($isCached || $isSubscriptionCached) {
            return;
        }

        update_option('give_campaigns_data', [
            'amounts' => array_merge(
                $campaignsData['amounts'] ?? [],
                $donations->collectIntendedAmounts()
            ),
            'donationsCount' => array_merge(
                $campaignsData['donationsCount'] ?? [],
                $donations->collectDonations()
            ),
            'donorsCount' => array_merge(
                $campaignsData['donorsCount'] ?? [],
                $donations->collectDonors()
            ),
        ]);

        if (defined('GIVE_RECURRING_VERSION')) {
            update_option('give_campaigns_subscriptions_data', [
                'amounts' => array_merge(
                    $campaignsSubscriptionData['amounts'] ?? [],
                    $subscriptions->collectInitialAmounts()
                ),
                'donationsCount' => array_merge(
                    $campaignsSubscriptionData['donationsCount'] ?? [],
                    $subscriptions->collectDonations()
                ),
                'donorsCount' => array_merge(
                    $campaignsSubscriptionData['donorsCount'] ?? [],
                    $subscriptions->collectDonors()
                ),
            ]);
        }
    }

    /**
     * The aggregate queries return no row for a campaign with no donations. Keep a zero row so the
     * campaign stays cached instead of turning into a null row and a PHP warning.
     *
     * @since TBD
     */
    private function row($rows, int $campaignId, string $column): array
    {
        return $rows[0] ?? ['campaign_id' => (string)$campaignId, $column => 0];
    }
}
