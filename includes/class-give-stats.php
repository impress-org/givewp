<?php
/**
 * Stats
 *
 * @package     Give
 * @subpackage  Classes/Give_Stats
 * @copyright   Copyright (c) 2016, Give
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Stats Class
 *
 * Base class for other stats classes. Primarily for setting up dates and ranges.
 *
 * @since 2.4.1
 */
class Give_Stats {
	/**
	 * Give_Date object
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @var Give_Date
	 */
	protected $date;

	/**
	 * The start date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @var    string
	 */
	public $start_date;

	/**
	 * The end date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The end date is optional
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @var    string
	 */
	public $end_date;

	/**
	 * Flag to determine if current query is based on timestamps
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @var    string
	 */
	public $timestamp;

	/**
	 * Parsed query arguments
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Default query arguments
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @var array
	 */
	protected $query_var_defaults = array(
		'range'               => '',
		'relative'            => false,
		'start_date'          => '',
		'end_date'            => '',
		'relative_start_date' => '',
		'relative_end_date'   => '',
		'where_sql'           => '',
		'inner_join_sql'      => '',
		'inner_join_at'       => '',
		'date_sql'            => '',
		'relative_date_sql'   => '',
		'function'            => 'SUM',
	);

	/**
	 * Counters
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @var    string
	 */
	protected $counters = array();

	/**
	 * Constructor.
	 *
	 * @since 2.4.1
	 * @since 2.4.1 Updated
	 *
	 * @param array $query     {
	 *                         Optional. Array of query parameters.
	 *                         Default empty.
	 *
	 *     Each method accepts query parameters to be passed. Parameters passed to methods override the ones passed in
	 *     the constructor. This is by design to allow for multiple calculations to be executed from one instance of
	 *     this class. Some methods will not allow parameters to be overridden as it could lead to inaccurate calculations.
	 *
	 * @type string $start     Start day and time (based on the beginning of the given day).
	 * @type string $end       End day and time (based on the end of the given day).
	 * @type string $range     Date range. If a range is passed, this will override and `start` and `end`
	 *                             values passed. See \EDD\Reports\get_dates_filter_options() for valid date ranges.
	 * @type string $function  SQL function. Certain methods will only accept certain functions. See each method for
	 *                             a list of accepted SQL functions.
	 * @type string $where_sql Reserved for internal use. Allows for additional WHERE clauses to be appended to the
	 *                             query.
	 * @type string $output    The output format of the calculation. Accepts `raw` and `formatted`. Default `raw`.
	 * }
	 */
	public function __construct( $query = array() ) {
		$this->date = new Give_Date();

		// Maybe parse query.
		if ( ! empty( $query ) ) {
			$this->parse_query( $query );

			// Set defaults.
		} else {
			$this->query_vars = $this->query_var_defaults;
		}
	}

	/**
	 * Get Predefined Dates
	 *
	 * Retrieve the predefined date periods permitted.
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @return array  Predefined dates.
	 */
	public function get_predefined_dates() {
		// Backward compatibility.
		if ( ! $this->date instanceof Give_Date ) {
			$this->date = new Give_Date();
		}

		return $this->date->get_predefined_dates();
	}


	/**
	 * Setup date range
	 *
	 * @since  2.4.1
	 * @access protected
	 */
	protected function set_date_ranges() {
		$range = $this->query_vars['range'];

		// Bailout
		if ( empty( $range ) ) {
			return;
		}

		$current = $this->date->parse_date_for_range( $range );

		if ( empty( $this->query_vars['start_date'] ) ) {
			$this->query_vars['start_date'] = $current['start'];
		}

		if ( empty( $this->query_vars['end_date'] ) ) {
			$this->query_vars['end_date'] = $current['end'];
		}

		// Setup relative time.
		if ( true === $this->query_vars['relative'] ) {

			$relative = $this->date->parse_date_for_range( $range, true );

			if ( empty( $this->query_vars['relative_start_date'] ) ) {
				$this->query_vars['relative_start_date'] = $relative['start'];
			}

			if ( empty( $this->query_vars['relative_end_date'] ) ) {
				$this->query_vars['relative_end_date'] = $relative['end'];
			}
		}
	}

	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly.
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param  string $_start_date Start date. Default is 'this_month'.
	 * @param  bool   $_end_date   End date. Default is false.
	 *
	 * @return void
	 */
	public function setup_dates( $_start_date = 'this_month', $_end_date = false ) {
		if ( empty( $_end_date ) ) {
			$_end_date = $_start_date;
		}

		$this->start_date = $this->convert_date( $_start_date );
		$this->end_date   = $this->convert_date( $_end_date, true );
	}

	/**
	 * Convert Date
	 *
	 * Converts a date to a timestamp.
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param  string $date     Date.
	 * @param  bool   $end_date End date. Default is false.
	 *
	 * @return array|WP_Error   If the date is invalid, a WP_Error object will be returned.
	 */
	public function convert_date( $date, $end_date = false ) {

		$this->timestamp = false;
		$second          = $end_date ? 59 : 0;
		$minute          = $end_date ? 59 : 0;
		$hour            = $end_date ? 23 : 0;
		$day             = 1;
		$month           = date( 'n', current_time( 'timestamp' ) );
		$year            = date( 'Y', current_time( 'timestamp' ) );

		if ( array_key_exists( (string) $date, $this->get_predefined_dates() ) ) {

			// This is a predefined date rate, such as last_week
			switch ( $date ) {

				case 'this_month' :

					if ( $end_date ) {

						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'last_month' :

					if ( $month == 1 ) {

						$month = 12;
						$year --;

					} else {

						$month --;

					}

					if ( $end_date ) {
						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );
					}

					break;

				case 'today' :

					$day = date( 'd', current_time( 'timestamp' ) );

					if ( $end_date ) {
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'yesterday' :

					$day = date( 'd', current_time( 'timestamp' ) ) - 1;

					// Check if Today is the first day of the month (meaning subtracting one will get us 0)
					if ( $day < 1 ) {

						// If current month is 1
						if ( 1 == $month ) {

							$year  -= 1; // Today is January 1, so skip back to last day of December
							$month = 12;
							$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						} else {

							// Go back one month and get the last day of the month
							$month -= 1;
							$day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						}
					}

					break;

				case 'this_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) * 60 * 60 * 24;
					$today              = date( 'j', current_time( 'timestamp' ) ) * 60 * 60 * 24;

					if ( $today <= $days_to_week_start ) {

						if ( $month > 1 ) {
							$month -= 1;
						} else {
							$month = 12;
						}

					}

					if ( ! $end_date ) {

						// Getting the start day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' );

					} else {

						// Getting the end day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'last_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) * 60 * 60 * 24;
					$today              = date( 'j', current_time( 'timestamp' ) ) * 60 * 60 * 24;

					if ( $today <= $days_to_week_start ) {

						if ( $month > 1 ) {
							$month -= 1;
						} else {
							$month = 12;
						}

					}

					if ( ! $end_date ) {

						// Getting the start day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' );

					} else {

						// Getting the end day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'this_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if ( ! $end_date ) {
							$month = 1;
						} else {
							$month  = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 6 ) {

						if ( ! $end_date ) {
							$month = 4;
						} else {
							$month  = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 9 ) {

						if ( ! $end_date ) {
							$month = 7;
						} else {
							$month  = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else {

						if ( ! $end_date ) {
							$month = 10;
						} else {
							$month  = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					}

					break;

				case 'last_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if ( ! $end_date ) {
							$month = 10;
						} else {
							$year   -= 1;
							$month  = 12;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 6 ) {

						if ( ! $end_date ) {
							$month = 1;
						} else {
							$month  = 3;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else if ( $month_now <= 9 ) {

						if ( ! $end_date ) {
							$month = 4;
						} else {
							$month  = 6;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					} else {

						if ( ! $end_date ) {
							$month = 7;
						} else {
							$month  = 9;
							$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
							$hour   = 23;
							$minute = 59;
							$second = 59;
						}

					}

					break;

				case 'this_year' :

					if ( ! $end_date ) {
						$month = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

				case 'last_year' :

					$year -= 1;
					if ( ! $end_date ) {
						$month = 1;
					} else {
						$month  = 12;
						$day    = cal_days_in_month( CAL_GREGORIAN, $month, $year );
						$hour   = 23;
						$minute = 59;
						$second = 59;
					}

					break;

			}


		} else if ( is_numeric( $date ) ) {

			// return $date unchanged since it is a timestamp
			$this->timestamp = true;

		} else if ( false !== strtotime( $date ) ) {

			$date  = strtotime( $date, current_time( 'timestamp' ) );
			$year  = date( 'Y', $date );
			$month = date( 'm', $date );
			$day   = date( 'd', $date );

		} else {

			return new WP_Error( 'invalid_date', esc_html__( 'Improper date provided.', 'give' ) );

		}

		if ( false === $this->timestamp ) {
			// Create an exact timestamp
			$date = mktime( $hour, $minute, $second, $month, $day, $year );
		}

		return apply_filters( 'give_stats_date', $date, $end_date, $this );

	}

	/**
	 * Get growth
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param int $current
	 * @param int $past
	 *
	 * @return float|int
	 */
	protected function get_growth( $current = 0, $past = 0 ) {
		$growth = 0;

		if (
			( 0 !== $current && 0 !== $past )
			|| ( $current !== $past )
		) {
			$growth = ( ( $current - $past ) / $past ) * 100;
		}

		return $growth;
	}

	/**
	 * Set counter
	 * Note: by default counter handle integer increment.
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	protected function set_counter( $key, $value = 0 ) {
		if ( ! $value ) {
			$value = isset( $this->counters[ $key ] ) ? ( $this->counters[ $key ] + 1 ) : 0;
		}

		$this->counters[ $key ] = $value;
	}

	/**
	 * Get counter
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function get_counter( $key ) {

		if ( ! isset( $this->counters[ $key ] ) ) {
			$this->counters[ $key ] = 0;
		}

		return $this->counters[ $key ];
	}

	/**
	 * Set column id for inner join
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param string $table_name
	 */
	private function set_inner_join_col_id( $table_name = '' ) {
		$donation_col_name = Give()->payment_meta->get_meta_type() . '_id';
		$table_name        = ! empty( $table_name ) ? $table_name : $this->query_vars['table'];

		$arr = array(
			$this->get_db()->posts        => 'ID',
			$this->get_db()->donationmeta => $donation_col_name,
		);

		$col_id = isset( $arr[ $table_name ] ) ? $arr[ $table_name ] : '';

		$this->query_vars['inner_join_at'] = $col_id;
	}


	/**
	 * Parse process query
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param array $query
	 */
	protected function parse_query( $query = array() ) {
		if ( empty( $this->query_vars ) ) {
			$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );
		} else {
			$this->query_vars = wp_parse_args( $query, $this->query_vars );
		}
	}


	/**
	 * Pre process query
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param array $query
	 */
	protected function pre_query( $query = array() ) {
		$query = ! is_array( $query ) ? array() : $query;

		$this->parse_query( $query );
		$this->set_date_ranges();
		$this->set_inner_join_col_id();
	}

	/**
	 * Runs after a query. Resets query vars back to the originals passed in via the constructor.
	 *
	 * @since  2.4.1
	 * @access protected
	 */
	protected function reset_query() {
		$this->query_vars = $this->query_var_defaults;
		$this->counters   = array();
	}

	/**
	 * Get WordPress database class object
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @return wpdb
	 */
	protected function get_db() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * @return array
	 */
	public function get_query_var() {
		return $this->query_vars;
	}

	/**
	 * Count Where
	 *
	 * Modifies the WHERE flag for payment counts.
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param  string $where SQL WHERE statment.
	 *
	 * @return string
	 */
	public function count_where( $where = '' ) {
		// Only get payments in our date range

		$start_where = '';
		$end_where   = '';

		if ( $this->start_date ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if ( $this->end_date ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date = date( $format, $this->end_date );

			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

	/**
	 * Payment Where
	 *
	 * Modifies the WHERE flag for payment queries.
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param  string $where SQL WHERE statment.
	 *
	 * @return string
	 */
	public function payments_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if ( ! is_wp_error( $this->start_date ) ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 00:00:00';
			}

			$start_date  = date( $format, $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if ( ! is_wp_error( $this->end_date ) ) {

			if ( $this->timestamp ) {
				$format = 'Y-m-d H:i:s';
			} else {
				$format = 'Y-m-d 23:59:59';
			}

			$end_date = date( $format, $this->end_date );

			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

}

// @todo: deprecated count_where and payment_where
// @todo: document stat query params
// @todo: return query var and other useful information in result
