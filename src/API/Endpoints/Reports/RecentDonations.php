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

		// Setup donation query args (get sanitized start/end date from request)
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

			$donation = new \Give_Payment( $donation->ID );

			$amount = give_currency_symbol( $payment->currency, true ) . give_format_amount( $donation->total, array( 'sanitize' => false ) );
			$status = $donation->status === 'publish' ? 'completed' : $donation->status;

			$item = [
				'type'     => 'donation',
				'donation' => $donation,
				'status'   => $status,
				'amount'   => $amount,
				'time'     => $donation->date,
				'donor'    => [
					'name' => "{$donation->first_name} {$donation->last_name}",
					'id'   => $donation->donor_id,
				],
				'source'   => $donation->form_title,
			];
			array_push( $list, $item );
		}

		// Return $list of donations for RESTList component
		return new \WP_REST_Response(
			[
				'data' => $list,
			]
		);
	}
}
