<?php

namespace Give\DonationForms\AsyncData\Actions;

use Give\DonationForms\AsyncData\AdminFormListView\AdminFormListViewOptions;
use Give\DonationForms\AsyncData\AsyncDataHelpers;
use Give\DonationForms\AsyncData\FormGrid\FormGridViewOptions;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;

/**
 * @since 3.16.0
 */
class GetAsyncFormDataForListView
{
    /**
     * @since 3.20.0 Check if form goal is enabled
     * @since 3.16.0
     */
    public function __invoke()
    {
        $options = give_clean($_GET);

        if ( ! isset($options['nonce']) || ! check_ajax_referer('GiveDonationFormsAsyncDataAjaxNonce', 'nonce')) {
            wp_send_json_error([
                'errorMsg' => __('The current user does not have permission to execute this operation.', 'give'),
            ]);
        }

        if ( ! isset($options['formId'])) {
            wp_send_json_error(['errorMsg' => __('Missing Form ID.', 'give')]);
        }

        $formId = absint($options['formId']);
        if ('give_forms' !== get_post_type($formId)) {
            wp_send_json_error(['errorMsg' => __('Invalid post type.', 'give')]);
        }

        $transientName = 'give_async_data_for_list_view_form_' . $formId;

        $data = get_transient($transientName);

        if ($data) {
            wp_send_json_success($data);
        }

        $amountRaised = 0;
        $percentComplete = 0;
        if ($this->isAsyncProgressBar() && $this->isFormGoalEnabled($formId)) {
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
            $revenue = give_currency_filter(give_format_amount($revenue));
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
     * @since 3.16.0
     */
    private function isAsyncProgressBar(): bool
    {
        return AdminFormListViewOptions::isGoalColumnAsync() || FormGridViewOptions::isProgressBarAmountRaisedAsync();
    }

    /**
     * @since 3.16.0
     */
    private function isAsyncDonationCount(): bool
    {
        return AdminFormListViewOptions::isDonationColumnAsync() || FormGridViewOptions::isProgressBarDonationsCountAsync();
    }

    /**
     * @since 3.16.0
     */
    private function isAsyncRevenue(): bool
    {
        return AdminFormListViewOptions::isRevenueColumnAsync();
    }

    /**
     * @since 3.20.0
     */
    private function isFormGoalEnabled(int $formId): bool
    {
        if ($donationForm = DonationForm::find($formId)) {
            return $donationForm->settings->enableDonationGoal;
        }

        return give_is_setting_enabled(Give()->form_meta->get_meta($formId,
            DonationFormMetaKeys::GOAL_OPTION()->getKeyAsCamelCase(), true));
    }
}
