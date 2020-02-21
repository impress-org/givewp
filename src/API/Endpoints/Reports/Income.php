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

		// // Check if a cached version exists
		// $cached_report = $this->get_cached_report( $request );
		// if ( $cached_report !== null ) {
		// Bail and return the cached version
		// return new \WP_REST_Response(
		// [
		// 'data' => $cached_report,
		// ]
		// );
		// }

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

			$values          = $this->get_values( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );
			$incomeForPeriod = $values['earnings'];
			$donorsForPeriod = $values['donor_count'];

			switch ( $intervalStr ) {
				case 'P1D':
					$periodLabel = $periodEnd->format( 'l' );
					break;
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
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
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

		$status = $this->get_give_status();

		// Create data objec to be returned, with 'highlights' object containing total and average figures to display
		$data = [
			'datasets' => [
				[
					'data'     => $income,
					'tooltips' => $tooltips,
				],
			],
			'status'   => $status,
		];

		return $data;

	}

	public function get_values( $startStr, $endStr ) {

		$earnings = 0;
		$donors   = [];

		foreach ( $this->payments as $payment ) {
			if ( $payment->status == 'publish' && $payment->date > $startStr && $payment->date < $endStr ) {
				$earnings += $payment->total;
				$donors[]  = $payment->donor_id;
			}
		}

		$unique = array_unique( $donors );

		return [
			'earnings'    => $earnings,
			'donor_count' => count( $unique ),
		];
	}

	public function get_give_status() {

		$payments_for_site = get_posts(
			[
				'post_type'   => array( 'give_payment' ),
				'post_status' => 'publish',
				'numberposts' => 1,
			]
		);

		$payments_for_period = [];
		foreach ( $this->payments as $payment ) {
			if ( $payment->status === 'publish' ) {
				$payments_for_period[] = $payment;
			}
		}

		switch ( true ) {
			case count( $payments_for_period ) > 0 : {
				return 'donations_found_for_period';
			}
			case count( $payments_for_site ) > 0 : {
				return 'donations_found_on_site';
			}
			default : {
				return 'no_donations_found';
			}
		}

	}

}
