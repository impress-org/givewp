<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class AverageDonation extends Endpoint {

	public function __construct() {
		$this->endpoint = 'average-donation';
	}

	public function get_report( $request ) {

		// // Check if a cached version exists
		// $cached_report = $this->get_cached_report( $request );
		// if ( $cached_report !== false ) {
		// Bail and return the cached version
		// return new \WP_REST_Response(
		// [
		// 'data' => $cached_report,
		// ]
		// );
		// }

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

		$stats = new \Give_Payment_Stats();

		$startStr = $start->format( 'Y-m-d H:i:s' );
		$endStr   = $end->format( 'Y-m-d H:i:s' );

		// Determine the start date of the previous period (used to calculate trend)
		$prev    = date_sub( date_create( $startStr ), date_diff( $start, $end ) );
		$prevStr = $prev->format( 'Y-m-d H:i:s' );

		$labels = [];
		$income = [];

		$dateInterval = new \DateInterval( $interval );
		while ( $start < $end ) {

			$periodStart = $start->format( 'Y-m-d H:i:s' );

			// Add interval to get period end
			$periodEnd = clone $start;
			date_add( $periodEnd, $dateInterval );

			$label     = $periodEnd->format( $format );
			$periodEnd = $periodEnd->format( 'Y-m-d H:i:s' );

			$incomeForPeriod        = $stats->get_earnings( 0, $periodStart, $periodEnd );
			$paymentsForPeriod      = $stats->get_sales( 0, $periodStart, $periodEnd );
			$averageIncomeForPeriod = $paymentsForPeriod > 0 ? $incomeForPeriod / $paymentsForPeriod : 0;

			$income[] = $averageIncomeForPeriod;
			$labels[] = $label;

			date_add( $start, $dateInterval );
		}

		$averageForPeriod = array_sum( $income ) / count( $income );

		// Calculate the income trend by comparing average earnings in the
		// previous period to average earnings in the current period
		$prevIncome   = $stats->get_earnings( 0, $prevStr, $startStr );
		$prevPayments = $stats->get_sales( 0, $prevStr, $startStr );
		$prevAverage  = $prevPayments > 0 ? $prevIncome / $prevPayments : 0;

		$currentIncome   = $stats->get_earnings( 0, $startStr, $endStr );
		$currentPayments = $stats->get_sales( 0, $startStr, $endStr );
		$currentAverage  = $currentPayments > 0 ? $currentIncome / $currentPayments : 0;

		$trend = $prevAverage > 0 ? round( ( ( $currentAverage - $prevAverage ) / $prevAverage ) * 100 ) : 'NaN';

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'labels'   => $labels,
			'datasets' => [
				[
					'label'     => 'Income',
					'data'      => $income,
					'trend'     => $trend,
					'highlight' => give_currency_filter( give_format_amount( $averageForPeriod ), [ 'decode_currency' => true ] ),
				],
			],
		];

		return $data;

	}
}
