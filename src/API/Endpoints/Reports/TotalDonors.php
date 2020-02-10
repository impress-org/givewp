<?php

/**
 * Total Donors over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalDonors extends Endpoint {

	public function __construct() {
		$this->endpoint = 'total-donors';
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

		$stats = new \Give_Payment_Stats();

		$tooltips = [];
		$income   = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$donorsForPeriod = $this->get_donor_count( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

			$income[] = [
				'y' => $donorsForPeriod,
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
			];

			if ( $interval == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$tooltips[] = [
				'title'  => $donorsForPeriod . ' ' . __( 'Donors', 'give' ),
				'body'   => __( 'Total Donors', 'give' ),
				'footer' => $periodLabel,
			];

			// Add interval to set up next period
			date_add( $periodEnd, $interval );
			date_add( $periodStart, $interval );
		}

		$totalForPeriod = $this->get_donor_count( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );
		$trend          = $this->get_trend( $start, $end );

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $income,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'highlight' => $totalForPeriod,
				],
			],
		];

		return $data;

	}

	/**
	 * Calculate the donor count for a given period
	 *
	 * @param  $start string|bool  The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param  $end  string|bool  The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 */
	public function get_donor_count( $start, $end ) {
		// Setup donor query args (get sanitized start/end date from request)
		$args = [
			'number'     => -1,
			'start_date' => $start,
			'end_date'   => $end,
		];

		// Get array of top 25 donors
		$donors     = new \Give_Donors_Query( $args );
		$donors     = $donors->get_donors();
		$donorCount = count( $donors );

		return $donorCount;
	}

	public function get_trend( $start, $end ) {

		$allTimeStartStr = $this->get_all_time_start();

		// Calculate the donor trend by comparing total donors in the
		// previous period to donors in the current period
		$prevTotal    = $this->get_donor_count( $allTimeStartStr, $start->format( 'Y-m-d H:i:s' ) );
		$currentTotal = $this->get_donor_count( $allTimeStartStr, $end->format( 'Y-m-d H:i:s' ) );
		$trend        = $prevTotal > 0 ? round( ( ( $currentTotal - $prevTotal ) / $prevTotal ) * 100 ) : 'NaN';

		return $trend;
	}
}
