<?php

/**
 * Average donation endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class AverageDonation extends Endpoint {

	protected $payments;

	/* Initialize endpoint and setup endpoint variable */
	public function __construct() {
		$this->endpoint = 'average-donation';
	}

	/**
	 * Check for cached reports, and return WP_REST_Response with report data and Give status
	 *
	 * @param array $request Sanitized request parameters
	 * @return \WP_REST_Response
	 */
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

		// Setup intervalStr, to be passed to get_data() method
		$intervalStr = 'P1D';
		$diff        = date_diff( $start, $end );
		switch ( true ) {
			case ( $diff->days > 12 ):
				$interval    = round( $diff->days / 12 );
				$intervalStr = 'P' . $interval . 'D';
				break;
			case ( $diff->days > 7 ):
				$intervalStr = 'PT12H';
				break;
			case ( $diff->days > 2 ):
				$intervalStr = 'PT3H';
				break;
			case ( $diff->days >= 0 ):
				$intervalStr = 'PT1H';
				break;
		}

		// Get datasets and tooltips for period, given an interval string
		$data = $this->get_data( $start, $end, $intervalStr );

		// Cache the report data
		$result = $this->cache_report( $request, $data );

		// Get Give status (either 'donations_found' or 'no_donations_found')
		$status = $this->get_give_status();

		// Return WP REST Response with data and Give status
		return new \WP_REST_Response(
			[
				'data'   => $data,
				'status' => $status,
			]
		);
	}

	/**
	 * Get report datasets and tooltips, give the requested start, end and intervals
	 *
	 * @param DateTime $start The start of the requested period
	 * @param DateTime $end The end of the requested period
	 * @param string   $intervalStr The desired intervals to break requested period into
	 *
	 * @return array
	 */
	public function get_data( $start, $end, $intervalStr ) {

		// Setup payments for period
		$this->payments = $this->get_payments( $start->format( 'Y-m-d' ), $end->format( 'Y-m-d' ) );

		// Setup $donations and $tooltips arrays, to be filled by while loop
		$donations = [];
		$tooltips  = [];

		// Setup DateInterval, used to advance while loop
		$interval = new \DateInterval( $intervalStr );

		// Setup $periodStart and $periodEnd, used to track while loop
		$periodStart = clone $start;
		$periodEnd   = clone $start;

		// Subtract interval to set up period start
		date_sub( $periodStart, $interval );

		// Loop through intervals until end of period is reached
		while ( $periodStart < $end ) {

			// Get average donation for period
			$averageForPeriod = $this->get_average_donation( $periodStart->format( 'Y-m-d H:i:s' ), $periodEnd->format( 'Y-m-d H:i:s' ) );

			// Use special label if interval is only one hour
			if ( $intervalStr == 'PT1H' ) {
				$periodLabel = $periodStart->format( 'D ga' ) . ' - ' . $periodEnd->format( 'D ga' );
			} else {
				$periodLabel = $periodStart->format( 'M j, Y' ) . ' - ' . $periodEnd->format( 'M j, Y' );
			}

			// Add datapoint to $donations array
			$donations[] = [
				'x' => $periodEnd->format( 'Y-m-d H:i:s' ),
				'y' => $averageForPeriod,
			];

			// Add associated tooltips to $tooltips array
			$tooltips[] = [
				'title'  => give_currency_filter( give_format_amount( $averageForPeriod ), [ 'decode_currency' => true ] ),
				'body'   => __( 'Avg Donation', 'give' ),
				'footer' => $periodLabel,
			];

			// Add interval to advance while loop
			date_add( $periodStart, $interval );
			date_add( $periodEnd, $interval );
		}

		// Get average donation for entire requested period
		$averageDonationForPeriod = $this->get_average_donation( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		// Get trend in average donations for entire requested period
		$trend = $this->get_trend( $start, $end );

		// Setup MiniChart info tooltip string
		$diff = date_diff( $start, $end );
		$info = $diff->days > 1 ? __( 'VS previous', 'give' ) . ' ' . $diff->days . ' ' . __( 'days', 'give' ) : __( 'VS previous day', 'give' );

		// Create data array to be returned
		$data = [
			'datasets' => [
				[
					'data'      => $donations,
					'tooltips'  => $tooltips,
					'trend'     => $trend,
					'info'      => $info,
					'highlight' => give_currency_filter( give_format_amount( $averageDonationForPeriod ), [ 'decode_currency' => true ] ),
				],
			],
		];

		return $data;

	}

	/**
	 * Get trend in average donations for period
	 *
	 * @param DateTime $start Start of period
	 * @param DateTime $end End of period
	 *
	 * @return int
	 */
	public function get_trend( $start, $end ) {

		$interval = $start->diff( $end );

		$prevStart = clone $start;
		$prevStart = date_sub( $prevStart, $interval );

		$prevEnd = clone $start;

		$prevAverage    = $this->get_prev_average_donation( $prevStart->format( 'Y-m-d H:i:s' ), $prevEnd->format( 'Y-m-d H:i:s' ) );
		$currentAverage = $this->get_average_donation( $start->format( 'Y-m-d H:i:s' ), $end->format( 'Y-m-d H:i:s' ) );

		// Set default trend to 0
		$trend = 0;

		// Check that prev value and current value are > 0 (can't divide by 0)
		if ( $prevAverage > 0 && $currentAverage > 0 ) {

			// Check if it is a percent decreate, or increase
			if ( $prevAverage > $currentAverage ) {
				// Calculate a percent decrease
				$trend = round( ( ( ( $prevAverage - $currentAverage ) / $prevAverage ) * 100 ), 1 ) * -1;
			} elseif ( $currentAverage > $prevAverage ) {
				// Calculate a percent increase
				$trend = round( ( ( ( $currentAverage - $prevAverage ) / $prevAverage ) * 100 ), 1 );
			}
		}

		return $trend;
	}

	/**
	 * Get average donation for a give period, within payments array fetched during get_data()
	 *
	 * @param string $startStr String representation of period start date
	 * @param string $endStr String representation of period end date
	 *
	 * @return int
	 */
	public function get_average_donation( $startStr, $endStr ) {

		$earnings     = 0;
		$paymentCount = 0;

		foreach ( $this->payments as $payment ) {
			if ( $payment->date > $startStr && $payment->date < $endStr ) {
				if ( $payment->status == 'publish' || $payment->status == 'give_subscription' ) {
					$earnings     += $payment->total;
					$paymentCount += 1;
				}
			}
		}

		$average = $paymentCount > 0 ? $earnings / $paymentCount : 0;

		return $average;
	}

	/**
	 * Get average donation for a give period, outside of payments array fetched during get_data()
	 *
	 * @param string $startStr String representation of period start date
	 * @param string $endStr String representation of period end date
	 *
	 * @return int
	 */
	public function get_prev_average_donation( $startStr, $endStr ) {

		$stats = new \Give_Payment_Stats();

		$earnings = $stats->get_earnings( 0, $startStr, $endStr );
		$sales    = $stats->get_sales( 0, $startStr, $endStr );

		$average = $sales > 0 ? $earnings / $sales : 0;

		return $average;
	}
}
