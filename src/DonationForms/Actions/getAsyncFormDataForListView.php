<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\DonationQuery;
use Give\DonationForms\FormListViewsAsyncData\AdminFormListViews\AdminFormListViewOptions;
use Give\DonationForms\FormListViewsAsyncData\FormGrid\FormGridViewOptions;
use Give\DonationForms\FormListViewsAsyncData\FormStats;
use Give\MultiFormGoals\ProgressBar\Model as ProgressBarModel;

/**
 * @unreleased
 */
class getAsyncFormDataForListView
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $options = give_clean($_GET);

        if ( ! isset($options['formId'] )) {
            wp_send_json_error([ 'errorMsg' => __( 'Missing Form ID.', 'give' ) ] );
        }

        if ( ! isset($options['nonce'] ) || ! check_ajax_referer( 'give_ajax_nonce', 'nonce')) {
            wp_send_json_error([ 'errorMsg' => __( 'The current user does not have permission to execute this operation.', 'give' ) ] );
        }

        $formId = $options['formId'];

        $transientName = 'give_async_data_for_list_view_form_' . $formId;

        $data = get_transient($transientName);

        if ($data) {
            wp_send_json_success( $data );
        }

        $amountRaised = 0;
        $percentComplete = 0;
        if ($this->isAsyncProgressBar()) {
            $goalStats       = give_goal_progress_stats( $formId );
            $amountRaised = $goalStats['actual'];
            $percentComplete = $goalStats['raw_goal'] ? round( ( $goalStats['raw_actual'] / $goalStats['raw_goal'] ), 3 ) * 100 : 0;
            $percentComplete = $amountRaised >= $goalStats['goal'] ? 100 : $percentComplete;
        }

        $donationsCount = 0;
        if ($this->isAsyncDonationCount()) {
            $donationsCount = FormStats::getDonationsCountValue($formId);
        }

        $revenue = $amountRaised;
        if (0 === $revenue && $this->isAsyncRevenue()) {
            $revenue = FormStats::getRevenueValue($formId);
        }

        $response = [
            'amountRaised' => $amountRaised,
            'percentComplete' => $percentComplete,
            'donationsCount' => $donationsCount,
            'revenue' =>  $revenue,
        ];

        set_transient($transientName, $response, MINUTE_IN_SECONDS * 5);

        wp_send_json_success( $response );
    }

    /**
     * @unreleased
     */
    private function isAsyncProgressBar(): bool
    {
       return AdminFormListViewOptions::isGoalColumnAsync() || FormGridViewOptions::isProgressBarGoalAsync();
    }

    /**
     * @unreleased
     */
    private function isAsyncDonationCount(): bool
    {
        return AdminFormListViewOptions::isDonationColumnAsync() || FormGridViewOptions::isProgressBarDonationsAsync();
    }

    /**
     * @unreleased
     */
    private function isAsyncRevenue(): bool
    {
        return AdminFormListViewOptions::isRevenueColumnAsync();
    }
}
