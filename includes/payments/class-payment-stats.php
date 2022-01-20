<?php
/**
 * Earnings / Sales Stats
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Stats Class
 *
 * This class is for retrieving stats for earnings and sales.
 *
 * Stats can be retrieved for date ranges and pre-defined periods.
 *
 * @since 1.0
 */
class Give_Payment_Stats extends Give_Stats {

	/**
	 * Retrieve sale stats
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $form_id    int          The donation form to retrieve stats for. If false, gets stats for all forms
	 * @param  $start_date string|bool  The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param  $end_date   string|bool  The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param  $status     string|array The sale status(es) to count. Only valid when retrieving global stats
	 *
	 * @return float|int                Total amount of donations based on the passed arguments.
	 */
	public function get_sales( $form_id = 0, $start_date = false, $end_date = false, $status = 'publish' ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$args = [
			'status'     => 'publish',
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'fields'     => 'ids',
			'number'     => - 1,
			'output'     => '',
		];

		if ( ! empty( $form_id ) ) {
			$args['give_forms'] = $form_id;
		}

		/* @var Give_Payments_Query $payments */
		$payments = new Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		return count( $payments );
	}


	/**
	 * Retrieve earning stats
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $form_id     int         The donation form to retrieve stats for. If false, gets stats for all forms.
	 * @param  $start_date  string|bool The starting date for which we'd like to filter our donation earnings stats. If false, method will use the default start date of `this_month`.
	 * @param  $end_date    string|bool The end date for which we'd like to filter the donations stats. If false, method will use the default end date of `this_month`.
	 * @param  $gateway_id  string|bool The gateway to get earnings for such as 'paypal' or 'stripe'.
	 *
	 * @return float|int                Total amount of donations based on the passed arguments.
	 */
	public function get_earnings( $form_id = 0, $start_date = false, $end_date = false, $gateway_id = false ) {
		global $wpdb;
		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$args = [
			'status'     => 'publish',
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'fields'     => 'ids',
			'number'     => - 1,
			'output'     => '',
		];

		// Filter by Gateway ID meta_key
		if ( $gateway_id ) {
			$args['meta_query'][] = [
				'key'   => '_give_payment_gateway',
				'value' => $gateway_id,
			];
		}

		// Filter by Gateway ID meta_key
		if ( $form_id ) {
			$args['meta_query'][] = [
				'key'   => '_give_payment_form_id',
				'value' => $form_id,
			];
		}

		if ( ! empty( $args['meta_query'] ) && 1 < count( $args['meta_query'] ) ) {
			$args['meta_query']['relation'] = 'AND';
		}

		$args = apply_filters( 'give_stats_earnings_args', $args );
		$key  = Give_Cache::get_key( 'give_stats', $args );

		// Set transient for faster stats.
		$earnings = Give_Cache::get( $key );

		if ( false === $earnings ) {

			$this->timestamp = false;
			$payments        = new Give_Payments_Query( $args );
			$payments        = $payments->get_payments();
			$earnings        = 0;

			if ( ! empty( $payments ) ) {
				$donation_id_col = Give()->payment_meta->get_meta_type() . '_id';
				$query           = "SELECT {$donation_id_col} as id, meta_value as total
					FROM {$wpdb->donationmeta}
					WHERE meta_key='_give_payment_total'
					AND {$donation_id_col} IN ('" . implode( '\',\'', $payments ) . "')";

				$payments = $wpdb->get_results( $query, ARRAY_A );

				if ( ! empty( $payments ) ) {
					foreach ( $payments as $payment ) {
						$currency_code = give_get_payment_currency_code( $payment['id'] );

						/**
						 * Filter the donation amount
						 * Note: this filter documented in payments/functions.php:give_donation_amount()
						 *
						 * @since 2.1
						 */
						$formatted_amount = apply_filters(
							'give_donation_amount',
							give_format_amount( $payment['total'], [ 'donation_id' => $payment['id'] ] ),
							$payment['total'],
							$payment['id'],
							[
								'type'     => 'stats',
								'currency' => false,
								'amount'   => false,
							]
						);

						$earnings += (float) give_maybe_sanitize_amount( $formatted_amount, [ 'currency' => $currency_code ] );
					}
				}
			}

			// Cache the results for one hour.
			Give_Cache::set( $key, give_sanitize_amount_for_db( $earnings ), 60 * 60 );
		}

		/**
		 * Filter the earnings.
		 *
		 * @since 1.8.17
		 *
		 * @param  float       $earnings   Earning amount.
		 * @param  int         $form_id    Donation Form ID.
		 * @param  string|bool $start_date Earning start date.
		 * @param  string|bool $end_date   Earning end date.
		 * @param  string|bool $gateway_id Payment gateway id.
		 */
		$earnings = apply_filters( 'give_get_earnings', $earnings, $form_id, $start_date, $end_date, $gateway_id );

		// return earnings
		return round( $earnings, give_get_price_decimals( $form_id ) );

	}

	/**
	 * Retrieve earning stat transient key
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $form_id     int         The donation form to retrieve stats for. If false, gets stats for all forms
	 * @param  $start_date  string|bool The starting date for which we'd like to filter our donation earnings stats. If false, we'll use the default start date of `this_month`
	 * @param  $end_date    string|bool The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param  $gateway_id  string|bool The gateway to get earnings for such as 'paypal' or 'stripe'
	 *
	 * @return float|int                Total amount of donations based on the passed arguments.
	 */
	public function get_earnings_cache_key( $form_id = 0, $start_date = false, $end_date = false, $gateway_id = false ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if ( is_wp_error( $this->start_date ) ) {
			return $this->start_date;
		}

		// Make sure end date is valid
		if ( is_wp_error( $this->end_date ) ) {
			return $this->end_date;
		}

		$args = [
			'status'     => 'publish',
			'start_date' => $this->start_date,
			'end_date'   => $this->end_date,
			'fields'     => 'ids',
			'number'     => - 1,
		];

		// Filter by Gateway ID meta_key
		if ( $gateway_id ) {
			$args['meta_query'][] = [
				'key'   => '_give_payment_gateway',
				'value' => $gateway_id,
			];
		}

		// Filter by Gateway ID meta_key
		if ( $form_id ) {
			$args['meta_query'][] = [
				'key'   => '_give_payment_form_id',
				'value' => $form_id,
			];
		}

		if ( ! empty( $args['meta_query'] ) && 1 < count( $args['meta_query'] ) ) {
			$args['meta_query']['relation'] = 'AND';
		}

		$args = apply_filters( 'give_stats_earnings_args', $args );
		$key  = Give_Cache::get_key( 'give_stats', $args );

		// return earnings
		return $key;

	}

	/**
	 * Get the best selling forms
	 *
	 * @since  1.0
	 * @since 2.9.6 Added an explicit ORDER BY on which to apply a DESC order.
     * @since 2.18.0 Updated sales calculation so that the results are ordered as integers.
	 * @access public
	 * @global wpdb $wpdb
	 *
	 * @param       $number int The number of results to retrieve with the default set to 10.
	 *
	 * @return array       Best selling forms
	 */
	public function get_best_selling( $number = 10 ) {
		global $wpdb;

		$meta_table = __give_v20_bc_table_details( 'form' );

		$give_forms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$meta_table['column']['id']} as form_id, max(ABS(meta_value)) as sales
				FROM {$meta_table['name']} WHERE meta_key='_give_form_sales' AND meta_value > 0
				GROUP BY meta_value+0
				ORDER BY sales DESC
				LIMIT %d;",
				$number
			)
		);

		return $give_forms;
	}

}
