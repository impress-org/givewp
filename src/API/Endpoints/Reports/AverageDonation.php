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

			$startStr = $periodStart->format( 'Y-m-d H:i:s' );
			$endStr   = $periodEnd->format( 'Y-m-d H:i:s' );

			$averageIncomeForPeriod = $this->get_average_donation( $startStr, $endStr );

			$income[] = [
				'y' => $averageIncomeForPeriod,
				'x' => $endStr,
			];

			if ( $interval == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$tooltips[] = [
				'title'  => give_currency_filter( give_format_amount( $averageIncomeForPeriod ), [ 'decode_currency' => true ] ),
				'body'   => __( 'Average Donation', 'give' ),
				'footer' => $periodLabel,
			];

			date_add( $start, $dateInterval );
		}

		$averageForPeriod = $this->get_average_donation( $startStr, $endStr );

		// Calculate the income trend by comparing average earnings in the
		// previous period to average earnings in the current period
		$prevAverage    = $this->get_average_donation( $allTimeStartStr, $startStr );
		$currentAverage = $this->get_average_donation( $allTimeStartStr, $endStr );

		$trend = $prevAverage > 0 ? round( ( ( $currentAverage - $prevAverage ) / $prevAverage ) * 100 ) : 'NaN';

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $income,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'highlight' => give_currency_filter( give_format_amount( $averageForPeriod ), [ 'decode_currency' => true ] ),
				],
			],
		];

		return $data;

	}

	/**
	 * Calculate the average donation for a given period
	 *
	 * @param  $start string|bool  The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param  $end  string|bool  The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 */
	public function get_average_donation( $start, $end ) {

		$stats = new \Give_Payment_Stats();

		$income   = $stats->get_earnings( 0, $start, $end );
		$payments = $stats->get_sales( 0, $start, $end );
		$average  = $payments > 0 ? $income / $payments : 0;

		return $average;

	}
}
