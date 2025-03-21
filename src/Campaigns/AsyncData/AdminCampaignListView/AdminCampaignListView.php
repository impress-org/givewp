<?php

namespace Give\Campaigns\AsyncData\AdminCampaignListView;

use Give\Campaigns\AsyncData\AsyncDataHelpers;

/**
 * @unreleased
 */
class AdminCampaignListView
{
    /**
     * @unreleased
     */
    public function maybeChangeAchievedIconOpacity($achievedIconOpacity)
    {
        if (AdminCampaignListViewOptions::isGoalColumnAsync()) {
            $achievedIconOpacity = 0;
        }

        return $achievedIconOpacity;
    }

    public function maybeSetGoalColumnAsync($value, $campaignId)
    {
        if (AdminCampaignListViewOptions::isGoalColumnAsync()) {
            $value['actualFormatted'] = AsyncDataHelpers::getSkeletonPlaceholder('2rem');
        }

        return $value;
    }

    /**
     * @unreleased
     */
    public function maybeSetDonationsColumnAsync($value, $campaignId)
    {
        if (AdminCampaignListViewOptions::isDonationColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('5rem');
        }

        return $value;
    }

    /**
     * @unreleased
     */
    public function maybeSetRevenueColumnAsync($value, $campaignId)
    {
        if (AdminCampaignListViewOptions::isRevenueColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('2rem');
        }

        return $value;
    }
}
