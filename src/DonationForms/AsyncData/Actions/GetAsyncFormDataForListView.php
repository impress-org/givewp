<?php

namespace Give\DonationForms\AsyncData\Actions;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\AsyncData\AsyncDataHelpers;
use Give\DonationForms\AsyncData\FormGrid\FormGridViewOptions;

/**
 * @unreleased
 */
class GetAsyncFormDataForListView
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $options = give_clean($_GET);

        if ( ! isset($options['formId'])) {
            wp_send_json_error(['errorMsg' => __('Missing Form ID.', 'give')]);
        }

        if ( ! isset($options['nonce']) || ! check_ajax_referer('GiveDonationFormsAsyncDataAjaxNonce', 'nonce')) {
            wp_send_json_error([
                'errorMsg' => __('The current user does not have permission to execute this operation.', 'give'),
            ]);
        }

        $formId = $options['formId'];

        $transientName = 'give_async_data_for_list_view_form_' . $formId;

        $data = get_transient($transientName);

        if ($data) {
            wp_send_json_success($data);
        }

        $amountRaised = 0;
        $percentComplete = 0;
        if ($this->isAsyncProgressBar()) {
            $goalStats = give_goal_progress_stats($formId);
            $amountRaised = $goalStats['actual'];
            $percentComplete = ('percentage' === $goalStats['format']) ? str_replace('%', '',
                $goalStats['actual']) : max(min($goalStats['progress'], 100), 0);
        }

        $donationsCount = 0;
        if ($this->isAsyncDonationCount()) {
            $donationsCount = AsyncDataHelpers::getFormDonationsCountValue($formId);
        }

        $revenue = $amountRaised;
        if (0 === $revenue && $this->isAsyncRevenue()) {
            $revenue = AsyncDataHelpers::getFormRevenueValue($formId);
        }

        $response = [
            'amountRaised' => $amountRaised,
            'percentComplete' => $percentComplete,
            'donationsCount' => $donationsCount,
            'revenue' => $revenue,
        ];

        set_transient($transientName, $response, MINUTE_IN_SECONDS * 5);

        wp_send_json_success($response);
    }

    /**
     * @unreleased
     */
    private function isAsyncProgressBar(): bool
    {
        return AdminFormListViewOptions::isGoalColumnAsync() || FormGridViewOptions::isProgressBarAmountRaisedAsync();
    }

    /**
     * @unreleased
     */
    private function isAsyncDonationCount(): bool
    {
        return AdminFormListViewOptions::isDonationColumnAsync() || FormGridViewOptions::isProgressBarDonationsCountAsync();
    }

    /**
     * @unreleased
     */
    private function isAsyncRevenue(): bool
    {
        return AdminFormListViewOptions::isRevenueColumnAsync();
    }
}
