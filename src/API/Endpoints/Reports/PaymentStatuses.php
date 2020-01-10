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
		return new \WP_REST_Response([
			'data' => [
				'labels' => [
					'Stripe',
					'Paypal',
					'Other'
				],
				'datasets' => [
					[
						'label' => 'Payments',
						'data' => [
							'200',
							'143',
							'33'
						]
					]
				]
			]
		]);
	}
}
