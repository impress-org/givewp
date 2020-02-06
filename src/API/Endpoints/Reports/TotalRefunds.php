<?php

/**
 * Refunds endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalRefunds extends Endpoint {

	public function __construct() {
		$this->endpoint = 'total-refunds';
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
			case ( $diff->days > 900 ):
				$data = $this->get_data( $start, $end, 'P1Y', 'Y' );
				break;
			case ( $diff->days > 700 ):
				$data = $this->get_data( $start, $end, 'P6M', 'F Y' );
				break;
			case ( $diff->days > 400 ):
				$data = $this->get_data( $start, $end, 'P3M', 'F Y' );
				break;
			case ( $diff->days > 120 ):
				$data = $this->get_data( $start, $end, 'P1M', 'M Y' );
				break;
			case ( $diff->days > 30 ):
				$data = $this->get_data( $start, $end, 'P7D', 'M jS' );
				break;
			case ( $diff->days > 10 ):
				$data = $this->get_data( $start, $end, 'P3D', 'M jS' );
				break;
			case ( $diff->days > 4 ):
				$data = $this->get_data( $start, $end, 'P1D', 'l' );
				break;
			case ( $diff->days > 1 ):
				$data = $this->get_data( $start, $end, 'P1D', 'D ga' );
				break;
			case ( $diff->days >= 0 ):
				$data = $this->get_data( $start, $end, 'PT1H', 'D ga' );
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

	public function get_data( $start, $end, $interval, $format ) {

		$allTimeStartStr = $this->get_all_time_start();

		$stats = new \Give_Payment_Stats();

		$startStr = $start->format( 'Y-m-d H:i:s' );
		$endStr   = $end->format( 'Y-m-d H:i:s' );

		// Determine the start date of the previous period (used to calculate trend)
		$prev    = date_sub( date_create( $startStr ), date_diff( $start, $end ) );
		$prevStr = $prev->format( 'Y-m-d H:i:s' );

		$labels  = [];
		$refunds = [];

		$dateInterval = new \DateInterval( $interval );
		while ( $start < $end ) {

			$periodStart = $start->format( 'Y-m-d H:i:s' );

			// Add interval to get period end
			$periodEnd = clone $start;
			date_add( $periodEnd, $dateInterval );

			$label     = $periodEnd->format( $format );
			$periodEnd = $periodEnd->format( 'Y-m-d H:i:s' );

			$refundsForPeriod = $stats->get_sales( 0, $startStr, $periodEnd, 'refunded' );

			$refunds[] = $refundsForPeriod;
			$labels[]  = $label;

			date_add( $start, $dateInterval );
		}

		$totalForPeriod = $stats->get_sales( 0, $startStr, $endStr, 'refunded' );

		// Calculate the refunds trend by comparing total refunds in the
		// previous period to refunds in the current period
		$prevTotal    = $stats->get_sales( 0, $allTimeStartStr, $startStr, 'refunded' );
		$currentTotal = $stats->get_sales( 0, $allTimeStartStr, $endStr, 'refunded' );
		$trend        = $prevTotal > 0 ? round( ( ( $currentTotal - $prevTotal ) / $prevTotal ) * 100 ) : 'NaN';

		// Create data objec to be returned, with total highlighted
		$data = [
			'labels'   => $labels,
			'datasets' => [
				[
					'label'     => __( 'Refunds', 'give' ),
					'data'      => $refunds,
					'trend'     => $trend,
					'highlight' => $totalForPeriod,
				],
			],
		];

		return $data;

	}
}
