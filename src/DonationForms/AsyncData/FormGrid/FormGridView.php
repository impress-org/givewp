<?php

namespace Give\DonationForms\AsyncData\FormGrid;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\AsyncData\FormStats;

class FormGridView
{
    /**
     * @unreleased
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder): bool
    {
        if (FormGridViewOptions::isProgressBarAmountRaisedAsync()) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @unreleased
     */
    public function maybeSetProgressBarAmountRaisedAsync($amountRaisedCachedValue, $formId)
    {
        if (FormGridViewOptions::isProgressBarAmountRaisedAsync()) {
            return give_get_skeleton_placeholder_for_async_data('1rem');
        }

        if(FormGridViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return FormStats::getRevenueValue($formId);
    }

    /**
     * @unreleased
     */
    public function maybeSetProgressBarDonationsCountAsync($donationsCountCachedValue, $formId)
    {
        if (FormGridViewOptions::isProgressBarDonationsCountAsync()) {
            return give_get_skeleton_placeholder_for_async_data('1rem');
        }

        if(FormGridViewOptions::useCachedMetaKeys()) {
            return $donationsCountCachedValue;
        }

        return FormStats::getDonationsCountValue($formId);
    }
}
