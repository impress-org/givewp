<?php

namespace Give\DonationForms\AsyncData;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\AsyncData\FormGrid\FormGridViewOptions;

/**
 * @unreleased
 */
class GiveGoalProgressStats
{
    /**
     * @unreleased
     */
    public function maybeUsePlaceholderOnGoalAmountRaised(bool $usePlaceholder): bool
    {
        $isAdminFormListView =  isset($_GET['post_type']) && 'give_forms' === $_GET['post_type'];

        if ($isAdminFormListView && (AdminFormListViewOptions::isGoalColumnAsync() || FormGridViewOptions::isProgressBarGoalAsync())) {
            $usePlaceholder = true;
        }

        return $usePlaceholder;
    }

    /**
     * @unreleased
     */
    public function maybeChangeAmountRaisedOutput($amountRaisedCachedValue, $formId)
    {
        if(AdminFormListViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return FormStats::getRevenueValue($formId);
    }
}
