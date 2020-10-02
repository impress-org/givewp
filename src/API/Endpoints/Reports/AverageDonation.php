<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

use WP_REST_Request;
use WP_REST_Response;

class AverageDonation extends Endpoint {

	protected $payments;

	public function __construct() {
		$this->endpoint = 'average-donation';
	}

	/**
	 * Handle rest request.
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function getReport( $request ) {
		$start = date_create( $request->get_param( 'start' ) );
		$end   = date_create( $request->get_param( 'end' ) );
		$diff  = date_diff( $start, $end );

		$data = [];

		switch ( true ) {
			case ( $diff->days > 12 ):
				$interval = round( $diff->days / 12 );
				$data     = $this->get_data( $start, $end, 'P' . $interval . 'D' );
				break;
			case ( $diff->days > 5 ):
				$data = $this->get_data( $start, $end, 'P1D' );
				break;
			case ( $diff->days > 4 ):
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

		$tooltips = [];
		$income   = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$avgIncomeForPeriod = $this->get_average_income( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );
			$time               = $periodEnd->format( 'Y-m-d H:i:s' );

			switch ( $intervalStr ) {
				case 'P1D':
					$periodLabel = $periodStart->format( 'l' );
					break;
				case 'PT12H':
				case 'PT3H':
				case 'PT1H':
					$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
					break;
				default:
					$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			$income[] = [
				'x' => $time,
				'y' => $avgIncomeForPeriod,
			];

			$tooltips[] = [
				'title'  => give_currency_filter(
					give_format_amount( $avgIncomeForPeriod ),
					[
						'currency_code'   => $this->currency,
						'decode_currency' => true,
						'sanitize'        => false,
					]
				),
				'body'   => __( 'Avg Revenue', 'give' ),
				'footer' => $periodLabel,
			];

			// Add interval to set up next period
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		if ( $intervalStr === 'P1D' ) {
			$income   = array_slice( $income, 1 );
			$tooltips = array_slice( $tooltips, 1 );
		}

		$totalAvgIncomeForPeriod = $this->get_average_income( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );
		$trend                   = $this->get_trend( $start, $end, $income );

		$diff = date_diff( $start, $end );
		$info = $diff->days > 1 ? __( 'VS previous', 'give' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day', 'give' );

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $income,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'info'      => $info,
					'highlight' => give_currency_filter(
						give_format_amount( $totalAvgIncomeForPeriod ),
						[
							'currency_code'   => $this->currency,
							'decode_currency' => true,
							'sanitize'        => false,
						]
					),
				],
			],
		];

		return $data;

	}

	public function get_trend( $start, $end, $income ) {

		$interval = $start->diff( $end );

		$prevStart = clone $start;
		$prevStart = date_sub( $prevStart, $interval );

		$prevEnd = clone $start;

		$prevAverage    = $this->get_average_income( $prevStart->format( 'Y-m-d H:i:s' ), $prevEnd->format( 'Y-m-d H:i:s' ) );
		$currentAverage = $this->get_average_income( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		// Set default trend to 0
		$trend = 0;

		// Check that prev value and current value are > 0 (can't divide by 0)
		if ( $prevAverage > 0 && $currentAverage > 0 ) {

			// Check if it is a percent decreate, or increase
			if ( $prevAverage > $currentAverage ) {
				// Calculate a percent decrease
				$trend = ( ( ( $prevAverage - $currentAverage ) / $prevAverage ) * 100 ) * -1;
			} elseif ( $currentAverage > $prevAverage ) {
				// Calculate a percent increase
				$trend = ( ( $currentAverage - $prevAverage ) / $prevAverage ) * 100;
			}
		}

		return $trend;
	}

	/**
	 * Calculate average income for a period
	 *
	 * Based on provided start and end strings, return the calculated income,
	 * rounded to the appropriate decimal place for the currently queried currency
	 *
	 * @param string $startStr Period start string
	 * @param string $endStr Period end string
	 *
	 * @return float Average income float (rounded to the decimal place of currently queried currency)
	 * @since 2.6.0
	 **/
	public function get_average_income( $startStr, $endStr ) {

		$paymentObjects = $this->getPayments( $startStr, $endStr );

		$earnings     = 0;
		$paymentCount = 0;

		foreach ( $paymentObjects as $paymentObject ) {
			if ( $paymentObject->date >= $startStr && $paymentObject->date < $endStr ) {
				if ( $paymentObject->status == 'publish' || $paymentObject->status == 'give_subscription' ) {
					$earnings     += $paymentObject->total;
					$paymentCount += 1;
				}
			}
		}

		$averageIncome = $paymentCount > 0 ? $earnings / $paymentCount : 0;

		// Return rounded average (avoid displaying figures with many decimal places)
		return round( $averageIncome, give_get_price_decimals( $this->currency ) );
	}
}
