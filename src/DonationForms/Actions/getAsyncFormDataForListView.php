<?php

namespace Give\DonationForms\Actions;

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
        if ( ! isset($_GET['nonce'] ) || ! check_ajax_referer( 'give_ajax_nonce', 'nonce')) {
            return false;
        }

        $formId = $_GET['formId'];

        $goalStats       = give_goal_progress_stats( $formId );
        $percentComplete = $goalStats['raw_goal'] ? round( ( $goalStats['raw_actual'] / $goalStats['raw_goal'] ), 3 ) * 100 : 0;

        $amountRaised = $goalStats['actual'];
        $donationsCount = (new ProgressBarModel(['ids' => [$formId]]))->getDonationCount();

        $response = [
            'amountRaised' => $amountRaised,
            'percentComplete' => $percentComplete,
            'earnings' =>  $amountRaised,
            'donationsCount' => $donationsCount
        ];

        wp_send_json_success( $response );
    }
}
