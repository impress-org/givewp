<?php
/**
 * Earnings / Sales Stats
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


/**
 * Give_Stats Class
 *
 * This class is for retrieving stats for earnings and sales
 *
 * Stats can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.0
 */
class Give_Payment_Stats extends Give_Stats {


	/**
	 * Retrieve sale stats
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param $form_id    INT The download product to retrieve stats for. If false, gets stats for all forms
	 * @param $start_date string|bool The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param $end_date   string|bool The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 * @param $status     string|array The sale status(es) to count. Only valid when retrieving global stats
	 *
	 * @return float|int
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

		if ( empty( $form_id ) ) {

			// Global sale stats
			add_filter( 'give_count_payments_where', array( $this, 'count_where' ) );

			if ( is_array( $status ) ) {
				$count = 0;
				foreach ( $status as $payment_status ) {
					$count += give_count_payments()->$payment_status;
				}
			} else {
				$count = give_count_payments()->$status;
			}

			remove_filter( 'give_count_payments_where', array( $this, 'count_where' ) );

		} else {

			// Product specific stats
			global $give_logs;

			add_filter( 'posts_where', array( $this, 'payments_where' ) );

			$count = $give_logs->get_log_count( $form_id, 'sale' );

			remove_filter( 'posts_where', array( $this, 'payments_where' ) );

		}

		return $count;

	}


	/**
	 * Retrieve earning stats
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param $form_id    INT The download product to retrieve stats for. If false, gets stats for all forms
	 * @param $start_date string|bool The starting date for which we'd like to filter our sale stats. If false, we'll use the default start date of `this_month`
	 * @param $end_date   string|bool The end date for which we'd like to filter our sale stats. If false, we'll use the default end date of `this_month`
	 *
	 * @return float|int
	 */
	public function get_earnings( $form_id = 0, $start_date = false, $end_date = false ) {

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

		$earnings = 0;

		add_filter( 'posts_where', array( $this, 'payments_where' ) );

		if ( empty( $form_id ) ) {

			// Global earning stats
			$args = array(
				'post_type'              => 'give_payment',
				'nopaging'               => true,
				'post_status'            => array( 'publish', 'revoked' ),
				'fields'                 => 'ids',
				'update_post_term_cache' => false,
				'suppress_filters'       => false,
				'start_date'             => $this->start_date,
				// These dates are not valid query args, but they are used for cache keys
				'end_date'               => $this->end_date,
				'give_transient_type'    => 'give_earnings',
				// This is not a valid query arg, but is used for cache keying
			);

			$args = apply_filters( 'give_stats_earnings_args', $args );
			$key  = 'give_stats_' . substr( md5( serialize( $args ) ), 0, 15 );

			$earnings = get_transient( $key );
			$earnings = false; //TEMPORARY
			if ( false === $earnings ) {
				$sales    = get_posts( $args );
				$earnings = 0;
				if ( $sales ) {
					$sales = implode( ',', $sales );
					$earnings += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_give_payment_total' AND post_id IN({$sales})" );
				}
				// Cache the results for one hour
				set_transient( $key, $earnings, HOUR_IN_SECONDS );
			}

		} else {

			// Download specific earning stats
			global $give_logs, $wpdb;

			$args = array(
				'post_parent'         => $form_id,
				'nopaging'            => true,
				'log_type'            => 'sale',
				'fields'              => 'ids',
				'suppress_filters'    => false,
				'start_date'          => $this->start_date,
				// These dates are not valid query args, but they are used for cache keys
				'end_date'            => $this->end_date,
				'give_transient_type' => 'give_earnings',
				// This is not a valid query arg, but is used for cache keying
			);

			$args = apply_filters( 'give_stats_earnings_args', $args );
			$key  =  'give_stats_' . substr( md5( serialize( $args ) ), 0, 15 );

			$earnings = get_transient( $key );
			if ( false === $earnings ) {

				$log_ids  = $give_logs->get_connected_logs( $args, 'sale' );
				$earnings = 0;

				if ( $log_ids ) {
					$log_ids     = implode( ',', $log_ids );
					$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_give_log_payment_id' AND post_id IN ($log_ids);" );

				}

				// Cache the results for one hour
				set_transient( $key, $earnings, 60 * 60 );
			}
		}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );

		return round( $earnings, give_currency_decimal_filter() );

	}

	/**
	 * Get the best selling Forms
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param $number int The number of results to retrieve with the default set to 10.
	 *
	 * @return array
	 */
	public function get_best_selling( $number = 10 ) {

		global $wpdb;

		$give_forms = $wpdb->get_results( $wpdb->prepare(
			"SELECT post_id as form_id, max(meta_value) as sales
				FROM $wpdb->postmeta WHERE meta_key='_give_form_sales' AND meta_value > 0
				GROUP BY meta_value+0
				DESC LIMIT %d;", $number
		) );

		return $give_forms;
	}

}