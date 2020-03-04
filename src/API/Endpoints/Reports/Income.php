<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class Income extends Endpoint {

	protected $payments;

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
		$income   = [];

		$interval = new \DateInterval( $intervalStr );

		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		while ( $periodStart < $end ) {

			$values          = $this->get_values( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );
			$incomeForPeriod = $values['earnings'];
			$donorsForPeriod = $values['donor_count'];
			$time            = $periodEnd->format( 'Y-m-d H:i:s' );

			switch ( $intervalStr ) {
				case 'P1D':
					$time        = $periodStart->format( 'Y-m-d H:i:s' );
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
				'y' => $incomeForPeriod,
			];

			$tooltips[] = [
				'title'  => give_currency_filter( give_format_amount( $incomeForPeriod ), [ 'decode_currency' => true ] ),
				'body'   => $donorsForPeriod . ' ' . __( 'Donors', 'give' ),
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

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'     => $income,
					'tooltips' => $tooltips,
				],
			],
		];

		return $data;

	}

	public function get_values( $startStr, $endStr ) {

		$earnings = 0;
		$donors   = [];

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				if ( $payment->status == 'publish' || $payment->status == 'give_subscription' ) {
					$earnings += $payment->total;
					$donors[]  = $payment->donor_id;
				}
			}
		}

		$unique = array_unique( $donors );

		return [
			'earnings'    => $earnings,
			'donor_count' => count( $unique ),
		];
	}

}
