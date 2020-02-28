<?php

/**
 * Total donors endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalDonors extends Endpoint {

	protected $payments;

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
		$donors   = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$donorsForPeriod = $this->get_donors( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

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

			$donors[] = [
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
				'y' => $donorsForPeriod,
			];

			$tooltips[] = [
				'title'  => $donorsForPeriod . ' ' . __( 'Donors', 'give' ),
				'body'   => __( 'Total Donors', 'give' ),
				'footer' => $periodLabel,
			];

			// Add interval to set up next period
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		$totalDonorsForPeriod = $this->get_donors( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );
		$trend                = $this->get_trend( $start, $end, $donors );

		$diff = date_diff( $start, $end );
		$info = $diff->days > 1 ? __( 'VS previous' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day' );

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'      => $donors,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'info'      => $info,
					'highlight' => $totalDonorsForPeriod,
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

		$prevDonors    = $this->get_prev_donors( $prevStart->format( 'Y-m-d H:i:s' ), $prevEnd->format( 'Y-m-d H:i:s' ) );
		$currentDonors = $this->get_donors( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		// Set default trend to 0
		$trend = 0;

		// Check that prev value and current value are > 0 (can't divide by 0)
		if ( $prevDonors > 0 && $currentDonors > 0 ) {

			// Check if it is a percent decreate, or increase
			if ( $prevDonors > $currentDonors ) {
				// Calculate a percent decrease
				$trend = round( ( ( ( $prevDonors - $currentDonors ) / $prevDonors ) * 100 ), 1 ) * -1;
			} elseif ( $currentDonors > $prevDonors ) {
				// Calculate a percent increase
				$trend = round( ( ( ( $currentDonors - $prevDonors ) / $prevDonors ) * 100 ), 1 );
			}
		}

		return $trend;
	}

	public function get_donors( $startStr, $endStr ) {

		$donors = [];

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				if ( $payment->status == 'publish' || $payment->status == 'give_subscription' ) {
					$donors[] = $payment->donor_id;
				}
			}
		}

		$unique     = array_unique( $donors );
		$donorCount = count( $unique );

		return $donorCount;
	}

	public function get_prev_donors( $startStr, $endStr ) {

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

		$donors = [];
		foreach ( $prevPayments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				$donors[] = $payment->donor_id;
			}
		}

		$unique     = array_unique( $donors );
		$donorCount = count( $unique );

		return $donorCount;
	}
}
