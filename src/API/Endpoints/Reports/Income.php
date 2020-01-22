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

		return new \WP_REST_Response(
			[
				'data' => $data,
			]
		);
	}

	public function get_data( $start, $end, $interval, $format ) {

		$stats = new \Give_Payment_Stats();

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

			$incomeForPeriod = $stats->get_earnings( 0, $periodStart, $periodEnd );

			array_push( $income, $incomeForPeriod );
			array_push( $labels, $label );

			date_add( $start, $dateInterval );
		}

		$totalForPeriod = array_sum( $income );

		$beginning    = '2000-01-01';
		$totalAtStart = $stats->get_earnings( 0, $beginning, $start->format( 'Y-m-d H:i:s' ) );
		$totalAtEnd   = $stats->get_earnings( 0, $beginning, $end->format( 'Y-m-d H:i:s' ) );
		$trend        = $totalAtStart > 0 ? ( ( $totalAtEnd - $totalAtStart ) / $totalAtStart ) * 100 : 'NaN';

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'labels'   => $labels,
			'datasets' => [
				[
					'label'     => 'Income',
					'data'      => $income,
					'trend'     => $trend,
					'highlight' => give_currency_filter( give_format_amount( $totalForPeriod ), [ 'decode_currency' => true ] ),
				],
			],
		];

		return $data;

	}
}
