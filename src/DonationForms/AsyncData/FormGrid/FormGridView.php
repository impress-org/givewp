<?php

namespace Give\DonationForms\AsyncData\FormGrid;

use Give\DonationForms\AsyncData\AsyncDataHelpers;

/**
 * @unreleased
 */
class FormGridView
{
    /**
     * @unreleased
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder = false): bool
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
            return AsyncDataHelpers::getSkeletonPlaceholder('1rem');
        }

        if (FormGridViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return AsyncDataHelpers::getFormRevenueValue($formId);
    }

    /**
     * @unreleased
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
