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
		$info = $diff->days > 1 ? __( 'VS previous', 'give' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day', 'give' );

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

		$paymentObjects = $this->get_payments( $startStr, $endStr );

		$donors = [];

		foreach ( $paymentObjects as $paymentObject ) {
			if ( $paymentObject->date > $startStr && $paymentObject->date < $endStr ) {
				if ( $paymentObject->status == 'publish' || $paymentObject->status == 'give_subscription' ) {
					$donors[] = $paymentObject->donor_id;
				}
			}
		}

		$unique     = array_unique( $donors );
		$donorCount = count( $unique );

		return $donorCount;
	}

	public function get_prev_donors( $startStr, $endStr ) {

		$prevPaymentObjects = $this->get_payments( $startStr, $endStr, 'date', -1 );

		$donorIds = [];
		foreach ( $prevPaymentObjects as $paymentObject ) {
			if ( $paymentObject->date > $startStr && $paymentObject->date < $endStr ) {
				$donorIds[] = $paymentObject->donor_id;
			}
		}

		$uniqueDonorIds = array_unique( $donorIds );
		$donorCount     = count( $uniqueDonorIds );

		return $donorCount;
	}
}
