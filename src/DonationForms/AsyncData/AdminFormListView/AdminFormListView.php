<?php

namespace Give\DonationForms\AsyncData\AdminFormListView;

use Give\DonationForms\AsyncData\AsyncDataHelpers;

/**
 * @since 3.16.0
 */
class AdminFormListView
{
    /**
     * @since 3.16.0
     */
    public function maybeChangeAchievedIconOpacity($achievedIconOpacity)
    {
        if (AdminFormListViewOptions::isGoalColumnAsync()) {
            $achievedIconOpacity = 0;
        }

        return $achievedIconOpacity;
    }

    /**
     * @since 3.16.0
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder = false): bool
    {
        if (AdminFormListViewOptions::isGoalColumnAsync()) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @since 3.16.0
     */
    public function maybeSetDonationsColumnAsync($donationsCountCachedValue, $formId)
    {
        if (AdminFormListViewOptions::isDonationColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        if (AdminFormListViewOptions::useCachedMetaKeys()) {
            return $donationsCountCachedValue;
        }

        return AsyncDataHelpers::getFormDonationsCountValue($formId);
    }

    /**
     * @since 3.16.0
     */
    public function maybeSetRevenueColumnAsync($revenueCachedValue, $formId)
    {
        if (AdminFormListViewOptions::isRevenueColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        if (AdminFormListViewOptions::useCachedMetaKeys()) {
            return $revenueCachedValue;
        }

        $revenue = AsyncDataHelpers::getFormRevenueValue($formId);

        return give_currency_filter(give_format_amount($revenue));
    }
}
