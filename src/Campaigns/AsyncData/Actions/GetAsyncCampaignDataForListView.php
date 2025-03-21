<?php

namespace Give\Campaigns\AsyncData\Actions;

use Give\Campaigns\AsyncData\AdminCampaignListView\AdminCampaignListViewOptions;
use Give\Campaigns\ListTable\Columns\DonationsCountColumn;
use Give\Campaigns\ListTable\Columns\GoalColumn;
use Give\Campaigns\ListTable\Columns\RevenueColumn;
use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class GetAsyncCampaignDataForListView
{
    public function __invoke()
    {
        $options = give_clean($_GET);

        if ( ! isset($options['nonce']) || ! check_ajax_referer('GiveCampaignsAsyncDataAjaxNonce', 'nonce')) {
            wp_send_json_error([
                'errorMsg' => __('The current user does not have permission to execute this operation.', 'give'),
            ]);
        }

        if ( ! isset($options['campaignId'])) {
            wp_send_json_error(['errorMsg' => __('Missing Campaign ID.', 'give')]);
        }

        $campaignId = absint($options['campaignId']);
        $campaign = Campaign::find($campaignId);
        if ( ! $campaign) {
            wp_send_json_error(['errorMsg' => __('Invalid campaign.', 'give')]);
        }

        $transientName = 'give_async_data_for_list_view_campaign_' . $campaignId;

        $data = get_transient($transientName);

        if ($data) {
            wp_send_json_success($data);
        }

        $amountRaised = 0;
        $percentComplete = 0;
        if ($this->isAsyncProgressBar() && $campaign->goal > 0) {
            $goalData = GoalColumn::getCampaignGoalData($campaign);
            $amountRaised = $goalData->actualFormatted;
            $percentComplete = $goalData->percentage;
        }

        $donationsCount = 0;
        if ($this->isAsyncDonationCount()) {
            $donationsCount = DonationsCountColumn::getTotalDonationsLabel($campaign);
        }

        $revenue = $amountRaised;
        if (0 === $revenue && $this->isAsyncRevenue()) {
            $revenue = RevenueColumn::getFormattedRevenue($campaign);
        }

        $response = [
            'amountRaised' => $amountRaised,
            'percentComplete' => $percentComplete,
            'donationsCount' => $donationsCount,
            'revenue' => $revenue,
        ];

        set_transient($transientName, $response, MINUTE_IN_SECONDS * 5);

        wp_send_json_success($response);
    }

    /**
     * @unreleased
     */
    private function isAsyncProgressBar(): bool
    {
        return AdminCampaignListViewOptions::isGoalColumnAsync();
    }

    /**
     * @unreleased
     */
    private function isAsyncDonationCount(): bool
    {
        return AdminCampaignListViewOptions::isDonationColumnAsync();
    }

    /**
     * @unreleased
     */
    private function isAsyncRevenue(): bool
    {
        return AdminCampaignListViewOptions::isRevenueColumnAsync();
    }
}
