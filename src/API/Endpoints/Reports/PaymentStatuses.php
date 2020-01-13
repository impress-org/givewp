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

		$args = [
			'start-date' => $request['start'],
			'end-date' => $request['end']
		];
		$payments = give_count_payments( $args );

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
							$payments->completed,
							$payments->pending,
							$payments->refunded,
							$payments->abandoned
						]
					]
				],
				'payments' => $payments
			]
		]);
	}
}
