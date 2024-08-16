<?php

namespace Give\DonationForms\AsyncData\AdminFormListView;

use Give\DonationForms\AsyncData\AsyncDataHelpers;

/**
 * @unreleased
 */
class AdminFormListView
{
    /**
     * @unreleased
     */
    public function maybeChangeAchievedIconOpacity($achievedIconOpacity)
    {
        if (AdminFormListViewOptions::isGoalColumnAsync()) {
            $achievedIconOpacity = 0;
        }

        return $achievedIconOpacity;
    }

    /**
     * @unreleased
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder): bool
    {
        if (AdminFormListViewOptions::isGoalColumnAsync()) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @unreleased
     */
    public function maybeChangeAmountRaisedOutput($amountRaisedCachedValue, $formId)
    {
        $isDetailsPage = isset($_GET['action']) && 'edit' === $_GET['action'];
        if ( ! $isDetailsPage && AdminFormListViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return AsyncDataHelpers::getFormRevenueValue($formId);
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function maybeSetRevenueColumnAsync($revenueCachedValue, $formId)
    {
        if (AdminFormListViewOptions::isRevenueColumnAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        if (AdminFormListViewOptions::useCachedMetaKeys()) {
            return $revenueCachedValue;
        }

        return AsyncDataHelpers::getFormRevenueValue($formId);
    }
}
