<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class PaymentStatuses extends Endpoint {

	public function __construct() {
		$this->endpoint = 'payment-statuses';
	}

	public function get_report($request) {

		$labels = [];
		$data = [];

		// Use give_count_payments logic to get payments
		$gateways = give_get_payment_gateways();
		$stats = new \Give_Payment_Stats();

		foreach ( $gateways as $gateway_id => $gateway ) {
			$donations = $stats->get_earnings( 0, date($request['start']), date($request['end']), $gateway_id );
			array_push($labels, $gateway_id);
			array_push($data = $donations);
		}

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => [
				'labels' => $labels,
				'datasets' => [
					[
						'label' => 'Payments',
						'data' => $data
					]
				],
			]
		]);
	}
}
