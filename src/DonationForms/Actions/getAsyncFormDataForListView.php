<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\DonationQuery;
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
        if (give_is_goal_column_on_form_list_async()) {
            $goalStats       = give_goal_progress_stats( $formId );
            $amountRaised = $goalStats['actual'];
            $percentComplete = $goalStats['raw_goal'] ? round( ( $goalStats['raw_actual'] / $goalStats['raw_goal'] ), 3 ) * 100 : 0;
            $percentComplete = $amountRaised >= $goalStats['goal'] ? 100 : $percentComplete;
        }

        $donationsCount = 0;
        if (give_is_donations_column_on_form_list_async()) {
            $donationsCount = (new ProgressBarModel(['ids' => [$formId]]))->getDonationCount();
        }

        $revenue = $amountRaised;
        if (0 === $revenue && give_is_revenue_column_on_form_list_async()) {
            $revenue = (new DonationQuery())->form($formId)->sumIntendedAmount();
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
}
