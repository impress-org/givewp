<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class Income extends Endpoint {

	public function __construct() {
		$this->endpoint = 'income';
	}

	public function get_report( $request ) {

		// Check if a cached version exists
		$cached_report = $this->get_cached_report( $request );
		if ( $cached_report !== null ) {
			// Bail and return the cached version
			return new \WP_REST_Response(
				[
					'data' => $cached_report,
				]
			);
		}

		$start = date_create( $request['start'] );
		$end   = date_create( $request['end'] );
		$diff  = date_diff( $start, $end );

		$data = [];

		switch ( true ) {
			case ( $diff->days > 1 ):
				$data = $this->get_data( $start, $end, 'P1D' );
				break;
			case ( $diff->days >= 0 ):
				$data = $this->get_data( $start, $end, 'PT1H' );
				break;
		}

		// Cache the report data
		$result = $this->cache_report( $request, $data );

		return new \WP_REST_Response(
			[
				'data' => $data,
			]
		);
	}

	public function get_data( $start, $end, $interval ) {

		$stats = new \Give_Payment_Stats();

		$startStr = $start->format( 'Y-m-d H:i:s' );
		$endStr   = $end->format( 'Y-m-d H:i:s' );

		// Determine the start date of the previous period (used to calculate trend)
		$prev    = date_sub( date_create( $startStr ), date_diff( $start, $end ) );
		$prevStr = $prev->format( 'Y-m-d H:i:s' );

		$labels = [];
		$income = [];

		$dateInterval = new \DateInterval( $interval );

		date_sub( $start, $dateInterval );

		while ( $start < $end ) {

			$periodStart = $start->format( 'Y-m-d H:i:s' );

			// Add interval to get period end
			$periodEnd = clone $start;
			date_add( $periodEnd, $dateInterval );

			$label     = $periodEnd->format( 'Y-m-d H:i:s' );
			$periodEnd = $periodEnd->format( 'Y-m-d H:i:s' );

			$incomeForPeriod = $stats->get_earnings( 0, $periodStart, $periodEnd );

			$income[] = $incomeForPeriod;
			$labels[] = $label;

			date_add( $start, $dateInterval );
		}

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'labels'   => $labels,
			'datasets' => [
				[
					'label' => __( 'Income', 'give' ),
					'data'  => $income,
				],
			],
		];

		return $data;

	}
}
