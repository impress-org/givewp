<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalIncome extends Endpoint {

	public function __construct() {
		$this->endpoint = 'total-income';
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

		date_sub( $start, $dateInterval );

		while ( $start < $end ) {

			$periodStart = clone $start;
			$periodEnd   = clone $start;

			// Add interval to set up period end
			date_add( $periodEnd, $dateInterval );

			$endStr = $periodEnd->format( 'Y-m-d H:i:s' );

			$incomeForPeriod = $stats->get_earnings( 0, $startStr, $endStr );

			$income[] = [
				'y' => $incomeForPeriod,
				'x' => $endStr,
			];

			if ( $interval == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$tooltips[] = [
				'title'  => give_currency_filter( give_format_amount( $incomeForPeriod ), [ 'decode_currency' => true ] ),
				'body'   => __( 'Total Income', 'give' ),
				'footer' => $periodLabel,
			];

			date_add( $start, $dateInterval );
		}

		$totalForPeriod = $stats->get_earnings( 0, $startStr, $endStr );

		// Calculate the income trend by comparing total earnings in the
		// previous period to earnings in the current period
		$prevTotal    = $stats->get_earnings( 0, $allTimeStartStr, $startStr );
		$currentTotal = $stats->get_earnings( 0, $allTimeStartStr, $endStr );
		$trend        = $prevTotal > 0 ? round( ( ( $currentTotal - $prevTotal ) / $prevTotal ) * 100 ) : 'NaN';

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $income,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'highlight' => give_currency_filter( give_format_amount( $totalForPeriod ), [ 'decode_currency' => true ] ),
				],
			],
		];

		return $data;

	}
}
