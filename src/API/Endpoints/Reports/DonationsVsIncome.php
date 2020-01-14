<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class DonationsVsIncome extends Endpoint {

	public function __construct() {
		$this->endpoint = 'donations-vs-income';
	}

	public function get_report($request) {

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => [
				'donations' => $donations,
				'income' => $income,
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
			]
		]);
	}
}
