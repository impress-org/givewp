<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalRefunds extends Endpoint {

	protected $payments;

	public function __construct() {
		$this->endpoint = 'total-refunds';
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
		$status = $this->get_give_status();

		return new \WP_REST_Response(
			[
				'data'   => $data,
				'status' => $status,
			]
		);
	}

	public function get_data( $start, $end, $intervalStr ) {

		$this->payments = $this->get_payments( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );

		$tooltips = [];
		$refunds  = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$refundsForPeriod = $this->get_refunds( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

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

			$refunds[] = [
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
				'y' => $refundsForPeriod,
			];

			$tooltips[] = [
				'title'  => $refundsForPeriod . ' ' . __( 'Refunds', 'give' ),
				'body'   => __( 'Total Refunds', 'give' ),
				'footer' => $periodLabel,
			];

			// Add interval to set up next period
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		$totalRefundsForPeriod = $this->get_refunds( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );
		$trend                 = $this->get_trend( $start, $end, $refunds );

		$diff = date_diff( $start, $end );
		$info = $diff->days > 1 ? __( 'VS previous' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day' );

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $refunds,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'info'      => $info,
					'highlight' => $totalRefundsForPeriod,
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

		$prevRefunds    = $this->get_prev_refunds( $prevStart->format( 'Y-m-d H:i:s' ), $prevEnd->format( 'Y-m-d H:i:s' ) );
		$currentRefunds = $this->get_refunds( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		// Set default trend to 0
		$trend = 0;

		// Check that prev value and current value are > 0 (can't divide by 0)
		if ( $prevRefunds > 0 && $currentRefunds > 0 ) {

			// Check if it is a percent decreate, or increase
			if ( $prevRefunds > $currentRefunds ) {
				// Calculate a percent decrease
				$trend = round( ( ( ( $prevRefunds - $currentRefunds ) / $prevRefunds ) * 100 ), 1 ) * -1;
			} elseif ( $currentRefunds > $prevRefunds ) {
				// Calculate a percent increase
				$trend = round( ( ( ( $currentRefunds - $prevRefunds ) / $prevRefunds ) * 100 ), 1 );
			}
		}

		return $trend;
	}

	public function get_refunds( $startStr, $endStr ) {

		$refunds = 0;
		foreach ( $this->payments as $payment ) {
			if ( $payment->status == 'refunded' && $payment->date > $startStr && $payment->date < $endStr ) {
				$refunds += 1;
			}
		}

		return $refunds;
	}

	public function get_prev_refunds( $startStr, $endStr ) {

		$args = [
			'number'     => -1,
			'paged'      => 1,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'start_date' => $startStr,
			'end_date'   => $endStr,
		];

		$prevPayments = new \Give_Payments_Query( $args );
		$prevPayments = $prevPayments->get_payments();

		$refunds = 0;
		foreach ( $prevPayments as $payment ) {
			if ( $payment->status == 'refunded' && $payment->date > $startStr && $payment->date < $endStr ) {
				$refunds += 1;
			}
		}

		return $refunds;
	}
}
