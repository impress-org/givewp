<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalIncome extends Endpoint {

	protected $payments;

	public function __construct() {
		$this->endpoint = 'total-income';
	}

	public function get_report( $request ) {
		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );
		$diff  = date_diff( $start, $end );

		$data = [];

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

		while ( $periodStart <= $end ) {

			$incomeForPeriod = $this->get_income( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

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

			$income[] = array(
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
				'y' => $incomeForPeriod,
			);

			$tooltips[] = array(
				'title'  => give_currency_filter( give_format_amount( $incomeForPeriod ), array( 'decode_currency' => true ) ),
				'body'   => __( 'Total Income', 'give' ),
				'footer' => $periodLabel,
			);

			// Add interval to set up next period
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		$totalIncomeForPeriod = $this->get_earnings( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );
		$trend                = $this->get_trend( $start, $end, $income );

		$diff = date_diff( $start, $end );
		$info = $diff->days > 1 ? __( 'VS previous', 'give' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day', 'give' );

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = array(
			'datasets' => array(
				array(
					'data'      => $income,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'info'      => $info,
					'highlight' => give_currency_filter( give_format_amount( $totalIncomeForPeriod ), array( 'decode_currency' => true ) ),
				),
			),
		);

		return $data;

	}

	public function get_trend( $start, $end, $income ) {

		$interval = $start->diff( $end );

		$prevStart = clone $start;
		$prevStart = date_sub( $prevStart, $interval );

		$prevEnd = clone $start;

		$prevIncome    = $this->get_earnings( $prevStart->format( 'Y-m-d' ), $prevEnd->format( 'Y-m-d' ) );
		$currentIncome = $this->get_earnings( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );

		// Set default trend to 0
		$trend = 0;

		// Check that prev value and current value are > 0 (can't divide by 0)
		if ( $prevIncome > 0 && $currentIncome > 0 ) {

			// Check if it is a percent decreate, or increase
			if ( $prevIncome > $currentIncome ) {
				// Calculate a percent decrease
				$trend = round( ( ( ( $prevIncome - $currentIncome ) / $prevIncome ) * 100 ), 1 ) * -1;
			} elseif ( $currentIncome > $prevIncome ) {
				// Calculate a percent increase
				$trend = round( ( ( ( $currentIncome - $prevIncome ) / $prevIncome ) * 100 ), 1 );
			}
		}

		return $trend;
	}

	public function get_income( $startStr, $endStr ) {

		$income = 0;

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				if ( $payment->status === 'publish' || $payment->status === 'give_subscription' ) {
					$income += $payment->total;
				}
			}
		}

		return $income;
	}

	public function get_earnings( $startStr, $endStr ) {
		$stats  = new \Give_Payment_Stats();
		$income = $stats->get_earnings( 0, $startStr, $endStr );
		return $income;
	}
}
