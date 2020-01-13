<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class PaymentMethods extends Endpoint {

	public function __construct() {
		$this->endpoint = 'payment-methods';
	}

	public function get_report($request) {

		$labels = [];
		$data = [];

		// Use give_count_payments logic to get payments
		$gateways = give_get_payment_gateways();
		$stats = new \Give_Payment_Stats();

		foreach ( $gateways as $gateway_id => $gateway ) {
			$donations = $stats->get_earnings( 0, date($request['start']), date($request['end']), $gateway_id );
			array_push($labels, $gateway['admin_label']);
			array_push($data, $donations);
		}

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => [
				'labels' => $labels,
				'datasets' => [
					[
						'data' => $data
					]
				],
			]
		]);
	}
}
