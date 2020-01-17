<?php

/**
 * Recent Donations endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class RecentDonations extends Endpoint {

	public function __construct() {
		$this->endpoint = 'recent-donations';
	}

	public function get_report( $request ) {

		// Setup donor query args (get sanitized start/end date from request)
		$args = [
			'number'     => 50,
			'paged'      => 1,
			'orderby'    => 'publish_date',
			'order'      => 'DESC',
			'start_date' => $request['start'],
			'end_date'   => $request['end'],
		];

		// Get array of 50 recent donations
		$donations = new \Give_Payments_Query( $args );
		$donations = $donations->get_payments();

		// Populate $list with arrays in correct shape for frontend RESTList component
		$list = [];
		foreach ( $donations as $donation ) {

			$item = [
				'type'   => 'donation',
				'status' => 'completed',
				'amount' => '$50.00',
				'time'   => '2013-02-08 09:30',
				'donor'  => [
					'name' => 'Test Name',
					'id'   => 456,
				],
				'source' => 'Save the Whales',
			];
			array_push( $list, $item );
		}

		// Return $list of donors for RESTList component
		return new \WP_REST_Response(
			[
				'data' => $list,
			]
		);
	}
}
