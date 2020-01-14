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

		// Setup args for give_count_payments
		$args = [
			'start-date' => $request['start'],
			'end-date' => $request['end']
		];

		// Use give_count_payments logic to get payments
		$payments = give_count_payments( $args );

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => [
				'labels' => [
					'Completed',
					'Pending',
					'Refunded',
					'Abandoned'
				],
				'datasets' => [
					[
						'label' => 'Payments',
						'data' => [
							$payments->publish,
							$payments->pending,
							$payments->refunded,
							$payments->abandoned
						]
					]
				],
			]
		]);
	}
}
