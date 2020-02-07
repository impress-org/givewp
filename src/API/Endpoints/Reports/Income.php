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

		$dataset = [];

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

		$stats = new \Give_Payment_Stats();

		$startStr = $start->format( 'Y-m-d H:i:s' );
		$endStr   = $end->format( 'Y-m-d H:i:s' );

		// Determine the start date of the previous period (used to calculate trend)
		$prev    = date_sub( date_create( $startStr ), date_diff( $start, $end ) );
		$prevStr = $prev->format( 'Y-m-d H:i:s' );

		$tooltips = [];
		$income   = [];

		$dateInterval = new \DateInterval( $interval );

		date_sub( $start, $dateInterval );

		while ( $start < $end ) {

			$periodStart = clone $start;
			$periodEnd   = clone $start;

			// Add interval to set up period end
			date_add( $periodEnd, $dateInterval );

			$startStr = $periodStart->format( 'Y-m-d H:i:s' );
			$endStr   = $periodEnd->format( 'Y-m-d H:i:s' );

			$incomeForPeriod = $stats->get_earnings( 0, $startStr, $endStr );
			$donorsForPeriod = $this->get_donor_count( $startStr, $endStr );

			if ( $interval == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$income[] = [
				'x' => $endStr,
				'y' => $incomeForPeriod,
			];

			$tooltips[] = [
				'title'  => give_currency_filter( give_format_amount( $incomeForPeriod ), [ 'decode_currency' => true ] ),
				'body'   => $donorsForPeriod . ' ' . __( 'Donors', 'give' ),
				'footer' => $periodLabel,
			];

			date_add( $start, $dateInterval );
		}

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'label'    => __( 'Income', 'give' ),
					'data'     => $income,
					'tooltips' => $tooltips,
				],
			],
		];

		return $data;

	}

	public function get_donor_count( $start, $end ) {
		// Setup donor query args (get sanitized start/end date from request)
		$args = [
			'number'     => 25,
			'paged'      => 1,
			'orderby'    => 'purchase_value',
			'order'      => 'DESC',
			'start_date' => $start,
			'end_date'   => $end,
		];

		// Get array of top 25 donors
		$donors     = new \Give_Donors_Query( $args );
		$donors     = $donors->get_donors();
		$donorCount = count( $donors );

		return $donorCount;
	}
}
