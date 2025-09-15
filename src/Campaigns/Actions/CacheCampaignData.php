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
 *
 * Action used to update campaign's stats data
 *
 */
class CacheCampaignData
{
    /**
     * @since 4.8.0
     */
    public function __invoke(int $donationId): void
    {
        $donation = Donation::find($donationId);

        if (!$donation) {
            return;
        }

        if ($donation->status->isComplete() || $donation->status->isRenewal()) {
            as_enqueue_async_action('givewp_cache_campaign_data', [$donation->campaignId], 'givewp_campaigns_cache');
        }

    }

    /**
     * Handle campaign cache
     * @since 4.8.0
     */
    public function handleCache(int $campaignId): void
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return;
        }

        $campaignsData = get_option('give_campaigns_data', []);
        $campaignsSubscriptionData = get_option('give_campaigns_subscription_data', []);

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

                $campaignsData['amounts'][$i] = $donations->collectIntendedAmounts()[0];
                break;
            }
        }

        foreach ($campaignsData['donationsCount'] as $i => $data) {
            if ($data['campaign_id'] == $campaign->id) {
                $campaignsData['donationsCount'][$i] = $donations->collectDonations()[0];
                break;
            }
        }

        foreach ($campaignsData['donorsCount'] as $i => $data) {
            if ($data['campaign_id'] == $campaign->id) {
                $campaignsData['donorsCount'][$i] = $donations->collectDonors()[0];
                break;
            }
        }


        // Update campaign subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {

            $subscriptions = CampaignsDataQuery::subscriptions([$campaign->id]);

            foreach ($campaignsSubscriptionData['amounts'] as $i => $data) {
                if ($data['campaign_id'] == $campaign->id) {
                    $isSubscriptionCached = true;

                    $campaignsSubscriptionData['amounts'][$i] = $subscriptions->collectInitialAmounts()[0];
                    break;
                }
            }

            foreach ($campaignsSubscriptionData['donationsCount'] as $i => $data) {
                if ($data['campaign_id'] == $campaign->id) {
                    $campaignsSubscriptionData['donationsCount'][$i] = $subscriptions->collectDonations()[0];
                    break;
                }
            }

            foreach ($campaignsSubscriptionData['donorsCount'] as $i => $data) {
                if ($data['campaign_id'] == $campaign->id) {
                    $campaignsSubscriptionData['donorsCount'][$i] = $subscriptions->collectDonors()[0];
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
                    $subscriptions->collectIntendedAmounts()
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
}
