<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;

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
     * @since TBD Refresh the cache of the donation's payment mode only.
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
            $this->dispatch($donation->campaignId, self::modeOf($donation));
        }
    }

    /**
     * Dispatch the cache campaign data action. Without a mode, both modes are refreshed. The test-mode
     * cache is refreshed only once it exists: until test mode is viewed, the first read builds it.
     *
     * @since TBD Accept the payment mode to refresh.
     * @since 4.13.1
     */
    public function dispatch(int $campaignId, string $mode = ''): void
    {
        if ($mode !== DonationMode::TEST) {
            as_enqueue_async_action('givewp_cache_campaign_data', [$campaignId], 'givewp_campaigns_cache');
        }

        $hasTestCache = get_option(CampaignsDataRepository::donationsOption(DonationMode::TEST)) !== false
            || get_option(CampaignsDataRepository::subscriptionsOption(DonationMode::TEST)) !== false;

        if ($mode !== DonationMode::LIVE && $hasTestCache) {
            as_enqueue_async_action('givewp_cache_campaign_data', [$campaignId, DonationMode::TEST], 'givewp_campaigns_cache');
        }
    }

    /**
     * Handle campaign cache
     *
     * @since TBD Refresh the cache of the given payment mode.
     * @since 4.18.0 Read the subscriptions cache from the option it is written to; always compute live stats, keep zero rows when a campaign has no donations.
     * @since 4.8.0
     */
    public function handleCache(int $campaignId, string $mode = DonationMode::LIVE): void
    {
        // The queries follow the site's payment mode, so force the mode of the cache for the
        // duration of the job in case the site's mode was switched before it ran.
        $isTestMode = $mode === DonationMode::TEST ? '__return_true' : '__return_false';
        add_filter('give_is_test_mode', $isTestMode, PHP_INT_MAX);

        try {
            $this->refresh($campaignId, $mode);
        } finally {
            remove_filter('give_is_test_mode', $isTestMode, PHP_INT_MAX);
        }
    }

    /**
     * The payment mode of a donation, live when it has none.
     *
     * @since TBD
     */
    public static function modeOf(Donation $donation): string
    {
        return $donation->mode && $donation->mode->isTest() ? DonationMode::TEST : DonationMode::LIVE;
    }

    /**
     * @since TBD Read and write the options of the given payment mode.
     * @since 4.18.0
     */
    private function refresh(int $campaignId, string $mode): void
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return;
        }

        $donationsOption = CampaignsDataRepository::donationsOption($mode);
        $subscriptionsOption = CampaignsDataRepository::subscriptionsOption($mode);
        $campaignsData = get_option($donationsOption, []);
        $campaignsSubscriptionData = get_option($subscriptionsOption, []);

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
            update_option($donationsOption, $campaignsData);
        }

        if ($isSubscriptionCached) {
            update_option($subscriptionsOption, $campaignsSubscriptionData);
        }

        if ($isCached || $isSubscriptionCached) {
            return;
        }

        update_option($donationsOption, [
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
            update_option($subscriptionsOption, [
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
     * @since 4.18.0
     */
    private function row($rows, int $campaignId, string $column): array
    {
        return $rows[0] ?? ['campaign_id' => (string)$campaignId, $column => 0];
    }
}
