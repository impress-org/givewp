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

	public function get_data( $start, $end, $intervalStr ) {

		$tooltips = [];
		$income   = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$refundsForPeriod = $this->get_refunds( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

			$refunds[] = [
				'y' => $refundsForPeriod,
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
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

			// Add interval to set up period end
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );

		}

		$totalForPeriod = $this->get_refunds( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );
		$trend          = $this->get_trend( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

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

	public function get_refunds( $startStr, $endStr ) {

		$args = [
			'start-date' => $startStr,
			'end-date'   => $endStr,
		];

		$payments = give_count_payments( $args );
		$refunds  = $payments->refunded;

		return $refunds;

	}

	public function get_trend( $startStr, $endStr ) {

		$allTimeStartStr = $this->get_all_time_start();

		// Calculate the refunds trend by comparing total refunds in the
		// previous period to refunds in the current period
		$prevTotal    = $this->get_refunds( $allTimeStartStr, $startStr );
		$currentTotal = $this->get_refunds( $allTimeStartStr, $endStr );
		$trend        = $prevTotal > 0 ? round( ( ( $currentTotal - $prevTotal ) / $prevTotal ) * 100 ) : 'NaN';

		return $trend;

	}
}
