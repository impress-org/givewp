<?php
/**
 * Payments report
 *
 * @package Give
 */

namespace Give\Reports\Report;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality and actions specific to the payments report
 */
class PaymentStatuses extends Report {

	/**
	 * Initialize.
	 */
	public function __construct() {
		//Do nothing
    }

    public function get_datasets() {
        $datasets = array(
            array(
                'label' => 'Payment Statuses',
                'data' => $this->get_data()
            )
        );
        return $datasets;
    }

    public function get_data() {
        $data = array(
            '312',
            '206',
            '129',
            '14'
        );
        return $data;
    }

    public function get_labels() {
        $labels = array(
            'PayPal',
            'Stripe',
            'Authorize',
            'Other'
        );
        return $labels;
    }
    
}
