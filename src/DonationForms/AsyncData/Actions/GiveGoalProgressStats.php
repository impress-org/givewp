<?php

namespace Give\DonationForms\AsyncData\Actions;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\AsyncData\AsyncDataHelpers;

/**
 * @since 3.16.0
 */
class GiveGoalProgressStats
{
    /**
     * @since 3.16.0
     */
    public function maybeChangeGoalProgressStatsActualValue($stats)
    {
        if (false !== strpos($stats['actual'], 'give-skeleton')) {
            return $stats;
        }

        // Only use cached values on form list views
        if ( ! $this->isSingleForm() && ! wp_doing_ajax() && AdminFormListViewOptions::useCachedMetaKeys()) {
            return $stats;
        }

        if ('amount' === $stats['format']) {
            $actual = AsyncDataHelpers::getFormRevenueValue($stats['form_id']);
            $stats['actual'] = give_currency_filter(give_format_amount($actual));
        }

        return $stats;
    }

    /**
     * @since 3.16.0
     */
    private function isSingleForm(): bool
    {
        $isIframeFormPage = isset($_GET['giveDonationFormInIframe']) && '1' === $_GET['giveDonationFormInIframe'];
        $isSingleFormPage = 'give_forms' === get_post_type() && is_single();
        $isFormEditPage = 'give_forms' === get_post_type() && isset($_GET['action']) && 'edit' === $_GET['action'];

        return $isIframeFormPage || $isSingleFormPage || $isFormEditPage;
    }
}
