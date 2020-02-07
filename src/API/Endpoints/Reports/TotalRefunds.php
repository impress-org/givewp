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
			case ( $diff->days > 1 ):
				$interval = round( $diff->days / 12 );
				$data     = $this->get_data( $start, $end, 'P' . $interval . 'D' );
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

		$allTimeStartStr = $this->get_all_time_start();

		$stats = new \Give_Payment_Stats();

		$startStr = $start->format( 'Y-m-d H:i:s' );
		$endStr   = $end->format( 'Y-m-d H:i:s' );

		$tooltips = [];
		$income   = [];

		$dateInterval = new \DateInterval( $interval );

		while ( $start < $end ) {

			$periodStart = clone $start;
			$periodEnd   = clone $start;

			// Add interval to set up period end
			date_add( $periodEnd, $dateInterval );

			$endStr = $periodEnd->format( 'Y-m-d H:i:s' );

			$refundsForPeriod = $stats->get_sales( 0, $startStr, $endStr, 'refunded' );

			$refunds[] = [
				'y' => $refundsForPeriod,
				'x' => $endStr,
			];

			if ( $interval == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$tooltips[] = [
				'title'  => $refundsForPeriod,
				'body'   => __( 'Total Refunds', 'give' ),
				'footer' => $periodLabel,
			];

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
			'datasets' => [
				[
					'data'      => $refunds,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'highlight' => $totalForPeriod,
				],
			],
		];

		return $data;

	}
}
