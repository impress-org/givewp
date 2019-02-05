<?php
/**
 * Stats
 *
 * @package     Give
 * @subpackage  Classes/Give_Stats
 * @copyright   Copyright (c) 2016, Give
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
 * Base class for other stats classes. Primarily for setting up dates and ranges.
 *
 * @since 1.0
 */
class Give_Stats {
	/**
	 * Give_Date object
	 *
	 * @since  2.5.0
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
	 * @since  1.0
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
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $end_date;

	/**
	 * Flag to determine if current query is based on timestamps
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $timestamp;

	/**
	 * Parsed query arguments
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Default query arguments
	 *
	 * @since  2.5.0
	 * @access protected
	 *
	 * @var array
	 */
	protected $query_var_defaults = array(
		'range'               => '',
		'relative'            => false,
		'start_date'          => '', // Optional: pass date in mysql format
		'end_date'            => '', // Optional: pass date in mysql format
		'relative_start_date' => '', // Optional: pass date in mysql format
		'relative_end_date'   => '', // Optional: pass date in mysql format
		'where_sql'           => '',
		'inner_join_sql'      => '',
		'inner_join_at'       => '',
		'date_sql'            => '',
		'relative_date_sql'   => '',
		'function'            => 'SUM',
		'number'              => false,
		'offset'              => false,
	);

	/**
	 * Counters
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var    string
	 */
	protected $counters = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 * @since 2.5.0 Updated
	 *
	 * @param array $query
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
	 * @since  2.5.0
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
	 * @since  2.5.0
	 * @access protected
	 */
	protected function set_date_ranges() {
		$range = $this->query_vars['range'];

		$current = $this->date->parse_date_for_range( $range );

		if ( ! empty( $this->query_vars['start_date'] ) ) {
			$this->query_vars['start_date'] = new Give_Date( $this->query_vars['start_date'] );
		}else{
			$this->query_vars['start_date'] = $current['start'];
		}

		if ( ! empty( $this->query_vars['end_date'] ) ) {
			$this->query_vars['end_date'] = new Give_Date( $this->query_vars['end_date'] );
		}else{
			$this->query_vars['end_date'] = $current['end'];
		}

		// Setup relative time.
		if ( true === $this->query_vars['relative'] ) {

			$relative = $this->date->parse_date_for_range( $range, true );

			if ( ! empty( $this->query_vars['relative_start_date'] ) ) {
				$this->query_vars['relative_start_date'] = new Give_Date( $this->query_vars['relative_start_date'] );
			}else{
				$this->query_vars['relative_start_date'] = $relative['start'];
			}

			if ( empty( $this->query_vars['relative_end_date'] ) ) {
				$this->query_vars['relative_end_date'] = new Give_Date( $this->query_vars['relative_end_date'] );
			}else{
				$this->query_vars['relative_end_date'] = $relative['end'];
			}
		}
	}

	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly.
	 *
	 * @since  1.0
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
	 * @since  1.0
	 * @since  2.5.0 Decode date using Give_Date
	 * @access public
	 *
	 * @param  string $date     Date.
	 * @param  bool   $end_date End date. Default is false.
	 *
	 * @return string|WP_Error   If the date is invalid, a WP_Error object will be returned.
	 */
	public function convert_date( $date, $end_date = false ) {
		$this->timestamp = false;
		$rst             = new WP_Error( 'invalid_date', esc_html__( 'Improper date provided.', 'give' ) );

		if ( array_key_exists( (string) $date, $this->get_predefined_dates() ) ) {

			/* @var Give_Date $date */
			$date = $this->date->parse_date_for_range( $date );
			$date = $end_date
				? $date['end']
				: $date['start'];

			$rst = strtotime( $date->toDateTimeString() );

		} else if ( is_numeric( $date ) ) {
			$rst = $date;

			// return $date unchanged since it is a timestamp
			$this->timestamp = true;

		} else if ( is_string( $date ) && false !== strtotime( $date ) ) {
			/* @var Give_Date $date */
			$date = new Give_Date( $date );

			$date = $end_date
				? $date->endOfDay()
				: $date->startOfDay();

			$rst = strtotime( $date->toDateTimeString() );
		}


		/**
		 * Filter the date
		 */
		return apply_filters( 'give_stats_date', $rst, $end_date, $this );
	}

	/**
	 * Get growth
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param int $current
	 * @param int $past
	 *
	 * @return float|int
	 */

	public function get_growth( $current = 0, $past = 0 ) {
		$growth = 0;

		if (
			( 0 !== $current && 0 !== $past )
			|| ( $current !== $past )
		) {
			// Prevent divisible by zero issue by setting one as default divider for $past
			$growth = ( ( $current - $past ) / ( $past ? $past: 1 ) ) * 100;
		}

		return $growth;
	}

	/**
	 * Set counter
	 * Note: by default counter handle integer increment.
	 *
	 * @since  2.5.0
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
	 * @since  2.5.0
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
	 * @since  2.5.0
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
	 * @since  2.5.0
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
	 * @since  2.5.0
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
	 * @since  2.5.0
	 * @access protected
	 */
	protected function reset_query() {
		$this->query_vars = $this->query_var_defaults;
		$this->counters   = array();
	}

	/**
	 * Get WordPress database class object
	 *
	 * @since  2.5.0
	 * @access protected
	 *
	 * @return wpdb
	 */
	protected function get_db() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Set cache
	 *
	 * @since  2.5.0
	 * @access protected
	 *
	 * @param stdClass $result
	 */
	protected function set_cache( $result ) {
		Give_Cache::set_db_query( $this->query_vars['_cache_key'], $result );
	}

	/**
	 * Get cache
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @return mixed
	 */
	protected function get_cache() {
		$this->query_vars['_cache_key'] = $this->get_cache_key();

		return Give_Cache::get_db_query( $this->query_vars['_cache_key'] );
	}

	/**
	 * Get cache key
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @return string
	 */
	private function get_cache_key() {
		return Give_Cache::get_key( 'give_stat', $this->query_vars, false );
	}

	/**
	 * Count Where
	 *
	 * Modifies the WHERE flag for payment counts.
	 *
	 * @since  2.1.0
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
	 * @since  1.0
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
