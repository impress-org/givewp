<?php

namespace Give\Campaigns\AsyncData\AdminCampaignListView;

use Give\DonationForms\AsyncData\AsyncDataHelpers;

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

    /**
     * @unreleased
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder = false): bool
    {
        if (AdminCampaignListViewOptions::isGoalColumnAsync()) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @unreleased
     */
    public function maybeSetDonationsColumnAsync($donationsCountCachedValue, $formId)
    {
        if (AdminCampaignListViewOptions::isDonationColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        /*if (AdminCampaignListViewOptions::useCachedMetaKeys()) {
            return $donationsCountCachedValue;
        }*/

        return AsyncDataHelpers::getFormDonationsCountValue($formId);
    }

    /**
     * @unreleased
     */
    public function maybeSetRevenueColumnAsync($revenueCachedValue, $formId)
    {
        if (AdminCampaignListViewOptions::isRevenueColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        /*if (AdminCampaignListViewOptions::useCachedMetaKeys()) {
            return $revenueCachedValue;
        }*/

        $revenue = AsyncDataHelpers::getFormRevenueValue($formId);

        return give_currency_filter(give_format_amount($revenue));
    }
}
