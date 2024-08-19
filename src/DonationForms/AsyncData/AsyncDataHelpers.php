<?php

namespace Give\DonationForms\AsyncData;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\DonationQuery;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModel;

/**
 * @unreleased
 */
class AsyncDataHelpers
{
    /**
     * @unreleased
     */
    public static function getFormDonationsCountValue($formId): int
    {
        return (new ProgressBarModel(['ids' => [$formId]]))->getDonationCount();
    }

    /**
     * @unreleased
     */
    public static function getFormRevenueValue($formId): int
    {
        return (new DonationQuery())->form($formId)->sumIntendedAmount();
    }

    /**
     * @unreleased
     */
    public static function getSkeletonPlaceholder($width = '100%', $height = '0.7rem')
    {
        return '<span class="give-skeleton js-give-async-data" style="width: ' . esc_attr($width) . '; height: ' . esc_attr($height) . ';"></span>';
    }

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
