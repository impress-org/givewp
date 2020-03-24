<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class DonationsVsIncome extends Endpoint {

	public function __construct() {
		$this->endpoint = 'donations-vs-income';
	}

	public function get_report( $request ) {

		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );
		$diff  = date_diff( $start, $end );

		$data = array();

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
				$data = $this->get_data( $start, $end, 'PT6H', 'D ga' );
				break;
			case ( $diff->days >= 0 ):
				$data = $this->get_data( $start, $end, 'PT1H', 'D ga' );
				break;
		}

		return $data;
	}

	public function get_data( $start, $end, $interval, $format ) {

		$stats = new \Give_Payment_Stats();

		$labels    = array();
		$donations = array();
		$income    = array();
		$periods   = array();

		$dateInterval = new \DateInterval( $interval );
		while ( $start < $end ) {

			$periodStart = $start->format( 'Y-m-d H:i:s' );

			// Add interval to get period end
			$periodEnd = clone $start;
			date_add( $periodEnd, $dateInterval );

			$label     = $periodEnd->format( $format );
			$periodEnd = $periodEnd->format( 'Y-m-d H:i:s' );

			$donationsForPeriod = $stats->get_sales( 0, $periodStart, $periodEnd );
			$incomeForPeriod    = $stats->get_earnings( 0, $periodStart, $periodEnd );

			array_push( $donations, $donationsForPeriod );
			array_push( $income, $incomeForPeriod );
			array_push( $labels, $label );
			array_push( $periods, $periodStart );

			date_add( $start, $dateInterval );
		}

		$data = array(
			'periods'  => $periods,
			'labels'   => $labels,
			'datasets' => array(
				array(
					'label' => 'Donations',
					'data'  => $donations,
				),
				array(
					'label' => 'Income',
					'data'  => $income,
				),
			),
		);

		return $data;

	}
}
