<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TopDonors extends Endpoint {

	public function __construct() {
		$this->endpoint = 'top-donors';
	}

	public function get_report($request) {

		// Add caching logic here...

		return new \WP_REST_Response([
			'data' => [
				[
					'type' => 'donor',
					'name' => 'Name',
					'count' => '4 Donations',
					'total' => '$50.00',
					'image' => 'image.png',
					'email' => 'test@email.com'
				],
			]
		]);
	}
}
