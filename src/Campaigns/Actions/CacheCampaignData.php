<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;

/**
 * @unreleased
 *
 * @uses givewp_donation_created hook
 * @uses givewp_donation_updated hook
 *
 * Action used to update campaign's stats data
 *
 */
class CacheCampaignData
{
    /**
     * @unreleased
     */
    public function __invoke(Donation $donation): void
    {
        if ( ! $donation->status->isComplete()) {
            return;
        }

        $campaign = Campaign::findByFormId($donation->formId);

        if ( ! $campaign) {
            return;
        }

        $campaignsData = give_get_option('give_campaigns_data', []);
        $campaignsSubscriptionData = give_get_option('give_campaigns_subscription_data', []);


        $donations = CampaignsDataQuery::donations([$campaign->id]);
        $subscriptions = CampaignsDataQuery::subscriptions([$campaign->id]);

        $isCached = $isSubscriptionCached = false;

        // Update cache only if it exists for this campaign
        foreach ($campaignsData['amounts'] as $i => $data) {
            if ($data['campaign_id'] == $donation->campaignId) {
                $isCached = true;

                $campaignsData['amounts'][$i] = $donations->collectIntendedAmounts()[0];
                break;
            }
        }

        foreach ($campaignsData['donationsCount'] as $i => $data) {
            if ($data['campaign_id'] == $donation->campaignId) {
                $campaignsData['donationsCount'][$i] = $donations->collectDonations()[0];
                break;
            }
        }

        foreach ($campaignsData['donorsCount'] as $i => $data) {
            if ($data['campaign_id'] == $donation->campaignId) {
                $campaignsData['donorsCount'][$i] = $donations->collectDonors()[0];
                break;
            }
        }


        // Update campaign subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {
            foreach ($campaignsSubscriptionData['amounts'] as $i => $data) {
                if ($data['campaign_id'] == $donation->campaignId) {
                    $isSubscriptionCached = true;

                    $campaignsSubscriptionData['amounts'][$i] = $subscriptions->collectInitialAmounts()[0];
                    break;
                }
            }

            foreach ($campaignsSubscriptionData['donationsCount'] as $i => $data) {
                if ($data['campaign_id'] == $donation->campaignId) {
                    $campaignsSubscriptionData['donationsCount'][$i] = $subscriptions->collectDonations()[0];
                    break;
                }
            }

            foreach ($campaignsSubscriptionData['donorsCount'] as $i => $data) {
                if ($data['campaign_id'] == $donation->campaignId) {
                    $campaignsSubscriptionData['donorsCount'][$i] = $subscriptions->collectDonors()[0];
                    break;
                }
            }
        }


        // Save updated cache
        if ($isCached) {
            give_update_option('give_campaigns_data', $campaignsData);
        }

        if ($isSubscriptionCached) {
            give_update_option('give_campaigns_subscriptions_data', $campaignsSubscriptionData);
        }

        if ($isCached || $isSubscriptionCached) {
            return;
        }

        give_update_option('give_campaigns_data', [
            'amounts' => array_merge($campaignsData['amounts'] ?? [], $donations->collectInitialAmounts()),
            'donationsCount' => array_merge($campaignsData['donationsCount'] ?? [], $donations->collectDonations()),
            'donorsCount' => array_merge($campaignsData['donorsCount'] ?? [], $donations->collectDonors()),
        ]);

        if (defined('GIVE_RECURRING_VERSION')) {
            give_update_option('give_campaigns_subscriptions_data', [
                'amounts' => array_merge($campaignsSubscriptionData['amounts'] ?? [], ...$subscriptions->collectIntendedAmounts()),
                'donationsCount' => array_merge($campaignsSubscriptionData['donationsCount'] ?? [], ...$subscriptions->collectDonations()),
                'donorsCount' => array_merge($campaignsSubscriptionData['donorsCount'] ?? [], ...$subscriptions->collectDonors()),
            ]);
        }
    }
}
