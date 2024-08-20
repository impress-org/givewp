<?php

namespace Give\DonationForms\AsyncData\Actions;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\AsyncData\AsyncDataHelpers;

/**
 * @unreleased
 */
class GiveGoalProgressStats
{
    /**
     * @unreleased
     */
    public function maybeChangeAmountRaisedOutput($amountRaisedCachedValue, $formId)
    {
        // Only use cached values on form list views
        if ( ! $this->isSingleForm() && ! wp_doing_ajax() && AdminFormListViewOptions::useCachedMetaKeys()) {
            return $amountRaisedCachedValue;
        }

        return AsyncDataHelpers::getFormRevenueValue($formId);
    }

    /**
     * @unreleased
     */
    private function isSingleForm(): bool
    {
        $isIframeFormPage = isset($_GET['giveDonationFormInIframe']) && '1' === $_GET['giveDonationFormInIframe'];
        $isSingleFormPage = 'give_forms' === get_post_type() && is_single();
        $isFormEditPage = 'give_forms' === get_post_type() && isset($_GET['action']) && 'edit' === $_GET['action'];

        return $isIframeFormPage || $isSingleFormPage || $isFormEditPage;
    }
}
