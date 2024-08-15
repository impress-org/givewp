<?php

namespace Give\DonationForms\FormListViewsAsyncData\AdminFormListViews;

use Give\DonationForms\FormListViewsAsyncData\FormStats;

/**
 * @unreleased
 */
class AdminFormListView
{
    /**
     * @unreleased
     */
    public function maybeUsePlaceholderOnGoalProgressStatsFunction(bool $usePlaceholder): bool
    {
       $isAdminFormListView =  isset($_GET['post_type']) && 'give_forms' === $_GET['post_type'];
        if ($isAdminFormListView && AdminFormListViewOptions::isGoalColumnAsync()) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @unreleased
     */
    public function maybeChangeAmountRaisedOutputOnGoalProgressStatsFunction($amountRaisedCachedValue, $formId)
    {
        if(AdminFormListViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return FormStats::getRevenueValue($formId);
    }

    /**
     * @unreleased
     */
    public function maybeSetDonationsColumnAsync($donationsCountCachedValue, $formId)
    {
        if (AdminFormListViewOptions::isDonationColumnAsync()) {
            return give_get_skeleton_placeholder_for_async_data('1rem');
        }

        if(AdminFormListViewOptions::useCachedMetaKeys()) {
            return $donationsCountCachedValue;
        }

        return FormStats::getDonationsCountValue($formId);
    }

    /**
     * @unreleased
     */
    public function maybeSetRevenueColumnAsync ($revenueCachedValue, $formId)
    {
        if (AdminFormListViewOptions::isRevenueColumnAsync()) {
            return give_get_skeleton_placeholder_for_async_data('1rem');
        }

        if(AdminFormListViewOptions::useCachedMetaKeys()) {
            return $revenueCachedValue;
        }

        return FormStats::getRevenueValue($formId);
    }
}
