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
		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );
		$diff  = date_diff( $start, $end );

		$dataset = array();

		switch ( true ) {
			case ( $diff->days > 365 ):
				$data = $this->get_data( $start, $end, 'P1M' );
				break;
			case ( $diff->days > 60 ):
				$data = $this->get_data( $start, $end, 'P1W' );
				break;
			case ( $diff->days > 5 ):
				$data = $this->get_data( $start, $end, 'P1D' );
				break;
			case ( $diff->days >= 0 ):
				$data = $this->get_data( $start, $end, 'PT1H' );
				break;
		}

		return $data;
	}

	public function get_data( $start, $end, $intervalStr ) {

		$this->payments = $this->get_payments( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );

		$tooltips = array();
		$income   = array();

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
				case 'P1M':
					$periodLabel = $periodEnd->format( 'F Y' );
					break;
				case 'P1D':
					$periodLabel = $periodStart->format( 'F j, Y' );
					break;
				case 'PT1H':
					$periodLabel = $periodEnd->format( 'l ga' );
					break;
				default:
					$periodLabel = $periodEnd->format( 'F j, Y' );
			}

			$income[] = array(
				__( 'Date', 'give' )      => $periodLabel,
				__( 'Donors', 'give' )    => $donorsForPeriod,
				__( 'Donations', 'give' ) => $incomeForPeriod,
				__( 'Refunds', 'give' )   => $refundsForPeriod,
				__( 'Net', 'give' )       => $netForPeriod,
			);

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
		$donors      = array();

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				switch ( $payment->status ) {
					case 'give_subscription':
					case 'publish': {
						$income  += $payment->total;
						$donors[] = $payment->donor_id;
						break;
					}
					case 'refunded': {
						$refunds     += 1;
						$income      += $payment->total;
						$refundTotal += $payment->total;
						break;
					}
				}
			}
		}

		$unique = array_unique( $donors );

		return array(
			'income'  => give_currency_filter( give_format_amount( $income ), array( 'decode_currency' => true ) ),
			'donors'  => count( $unique ),
			'refunds' => $refunds,
			'net'     => give_currency_filter( give_format_amount( $income - $refundTotal ), array( 'decode_currency' => true ) ),
		);
	}

}
