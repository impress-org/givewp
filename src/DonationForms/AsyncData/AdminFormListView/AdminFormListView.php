<?php

namespace Give\DonationForms\AsyncData\AdminFormListView;

use Give\DonationForms\AsyncData\FormStats;

/**
 * @unreleased
 */
class AdminFormListView
{
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
