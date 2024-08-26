<?php

namespace Give\DonationForms\AsyncData\FormGrid;

use Give\DonationForms\AsyncData\AsyncDataHelpers;

/**
 * @since 3.16.0
 */
class FormGridView
{
    /**
     * @since 3.16.0
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder = false): bool
    {
        if (FormGridViewOptions::isProgressBarAmountRaisedAsync()) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @since 3.16.0
     */
    public function maybeSetProgressBarAmountRaisedAsync($amountRaisedCachedValue, $formId)
    {
        if (FormGridViewOptions::isProgressBarAmountRaisedAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        if (FormGridViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return AsyncDataHelpers::getFormRevenueValue($formId);
    }

    /**
     * @since 3.16.0
     */
    public function maybeSetProgressBarDonationsCountAsync($donationsCountCachedValue, $formId)
    {
        if (FormGridViewOptions::isProgressBarDonationsCountAsync()) {
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        if (FormGridViewOptions::useCachedMetaKeys()) {
            return $donationsCountCachedValue;
        }

        return AsyncDataHelpers::getFormDonationsCountValue($formId);
    }
}
