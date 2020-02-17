<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class IncomeBreakdown extends Endpoint {

	protected $payments;

	public function __construct() {
		$this->endpoint = 'income-breakdown';
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
			case ( $diff->days > 12 ):
				$interval = round( $diff->days / 12 );
				$data     = $this->get_data( $start, $end, 'P' . $interval . 'D' );
				break;
			case ( $diff->days > 7 ):
				$data = $this->get_data( $start, $end, 'PT12H' );
				break;
			case ( $diff->days > 2 ):
				$data = $this->get_data( $start, $end, 'PT3H' );
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

		$this->payments = $this->get_payments( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		$tooltips = [];
		$income   = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$values           = $this->get_values( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );
			$incomeForPeriod  = $values['income'];
			$donorsForPeriod  = $values['donors'];
			$refundsForPeriod = $values['refunds'];
			$netForPeriod     = $values['net'];

			switch ( $intervalStr ) {
				case 'PT12H':
					$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
					break;
				case 'PT3H':
					$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
					break;
				case 'PT1H':
					$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
					break;
				default:
					$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$income[] = [
				__( 'Date', 'give' )      => $periodLabel,
				__( 'Donors', 'give' )    => $donorsForPeriod,
				__( 'Donations', 'give' ) => $incomeForPeriod,
				__( 'Refunds', 'give' )   => $refundsForPeriod,
				__( 'Net', 'give' )       => $netForPeriod,
			];

			// Add interval to set up next period
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = $income;
		return $data;

	}

	public function get_values( $startStr, $endStr ) {

		$income      = 0;
		$refundTotal = 0;
		$refunds     = 0;
		$donors      = [];

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				switch ( $payment->status ) {
					case 'publish': {
						$income  += $payment->total;
						$donors[] = $payment->donor_id;
						break;
					}
					case 'refunded': {
						$refunds     += 1;
						$refundTotal += $payment->total;
						break;
					}
				}
			}
		}

		$unique = array_unique( $donors );

		return [
			'income'  => $income,
			'donors'  => count( $unique ),
			'refunds' => $refunds,
			'net'     => $income - $refundTotal,
		];
	}

	public function get_payments( $startStr, $endStr ) {

		$args = [
			'number'     => -1,
			'paged'      => 1,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'start_date' => $startStr,
			'end_date'   => $endStr,
		];

		$payments = new \Give_Payments_Query( $args );
		$payments = $payments->get_payments();
		return $payments;

	}
}
