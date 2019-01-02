<?php
/**
 * Earnings / Sales Stats
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donation_Stats Class
 *
 * This class is for retrieving stats for earnings and sales.
 *
 * Stats can be retrieved for date ranges and pre-defined periods.
 *
 * @since 2.4.1
 */
class Give_Donation_Stats extends Give_Stats {
	/**
	 * Give_Donation_Stats constructor.
	 *
	 * @param array $query
	 */
	public function __construct( array $query = array() ) {
		// Add additional default query params
		$this->query_var_defaults = array_merge( array(
			'status'     => array( 'publish' ),
			'give_forms' => array(),
			'gateways'   => array(),
		), $this->query_var_defaults );

		parent::__construct( $query );
	}

	/**
	 * Retrieve sale stats
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param array $query
	 *
	 * @return stdClass
	 */
	public function get_sales( $query = array() ) {
		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']  = $this->get_db()->posts;
		$this->query_vars['column'] = $this->query_vars['inner_join_at'] = 'ID';

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$allowed_functions = array( 'COUNT', 'AVG' );

		$is_relative = true === $this->query_vars['relative'];

		$function = isset( $this->query_vars['function'] ) && in_array( $this->query_vars['function'], $allowed_functions, true )
			? "{$this->query_vars['function']}({$this->query_vars['table']}.{$this->query_vars['column']})"
			: "COUNT({$this->query_vars['table']}.{$this->query_vars['column']})";

		if ( $is_relative ) {
			$sql = "SELECT IFNULL(COUNT({$this->query_vars['table']}.{$this->query_vars['column']}), 0) AS sales, IFNULL(relative, 0) AS relative
					FROM {$this->query_vars['table']}
					CROSS JOIN (
						SELECT IFNULL(COUNT({$this->query_vars['table']}.{$this->query_vars['column']}), 0) AS relative
						FROM {$this->query_vars['table']}
						{$this->query_vars['inner_join_sql']}
						WHERE 1=1
						{$this->query_vars['where_sql']}
						{$this->query_vars['relative_date_sql']}
					) o
					WHERE 1=1
					{$this->query_vars['where_sql']}
					{$this->query_vars['date_sql']}
					";
		} else {
			$sql = "SELECT IFNULL({$function}, 0) AS sales
					FROM {$this->query_vars['table']}
					{$this->query_vars['inner_join_sql']}
					WHERE 1=1
					{$this->query_vars['where_sql']}
					{$this->query_vars['date_sql']}
					";
		}

		$result = $this->get_db()->get_row( $sql );

		if ( is_null( $result ) ) {
			$result        = new stdClass();
			$result->sales = $result->relative = 0;
		}

		if ( $is_relative ) {
			$result->growth = $this->get_growth( $result->sales, $result->relative );
		}

		// Reset query vars.
		$this->reset_query();

		return $result;
	}


	/**
	 * Retrieve earning stats
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param array $query
	 *
	 * @return stdClass
	 */
	public function get_earnings( $query = array() ) {
		$donation_col_name = Give()->payment_meta->get_meta_type() . '_id';

		// Add table and column name to query_vars to assist with date query generation.
		$this->query_vars['table']         = $this->get_db()->donationmeta;
		$this->query_vars['column']        = 'meta_value';
		$this->query_vars['inner_join_at'] = $donation_col_name;

		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$allowed_functions = array( 'SUM', 'AVG' );

		$is_relative = true === $this->query_vars['relative'];

		$function = isset( $this->query_vars['function'] ) && in_array( $this->query_vars['function'], $allowed_functions, true )
			? "{$this->query_vars['function']}({$this->query_vars['table']}.{$this->query_vars['column']})"
			: "SUM({$this->query_vars['table']}.{$this->query_vars['column']})";

		if ( $is_relative ) {
			$sql = "SELECT IFNULL({$function}, 0) AS total, IFNULL(relative, 0) AS relative
					FROM {$this->query_vars['table']}
					CROSS JOIN (
						SELECT IFNULL($function, 0) AS relative
						FROM {$this->query_vars['table']}
						INNER JOIN {$this->get_db()->posts} on {$this->get_db()->posts}.ID = {$this->query_vars['table']}.{$donation_col_name}
						{$this->query_vars['inner_join_sql']}
						WHERE 1=1
						{$this->query_vars['where_sql']}
						{$this->query_vars['relative_date_sql']}
						AND {$this->query_vars['table']}.meta_key='_give_payment_total'
					) o
					INNER JOIN {$this->get_db()->posts} on {$this->get_db()->posts}.ID = {$this->query_vars['table']}.{$donation_col_name}
					{$this->query_vars['inner_join_sql']}
					WHERE 1=1
					{$this->query_vars['where_sql']}
					{$this->query_vars['date_sql']}
					AND {$this->query_vars['table']}.meta_key='_give_payment_total'
					";
		} else {
			$sql = "SELECT IFNULL({$function}, 0) AS total
					FROM {$this->query_vars['table']}
					INNER JOIN {$this->get_db()->posts} on {$this->get_db()->posts}.ID = {$this->query_vars['table']}.{$donation_col_name}
					{$this->query_vars['inner_join_sql']}
					WHERE 1=1
					{$this->query_vars['where_sql']}
					{$this->query_vars['date_sql']}
					AND {$this->query_vars['table']}.meta_key='_give_payment_total'
					";
		}

		$result = $this->get_db()->get_row( $sql );

		if ( is_null( $result ) ) {
			$result        = new stdClass();
			$result->total = $result->relative = $result->growth = 0;
		}

		if ( $is_relative ) {
			$result->growth = $this->get_growth( $result->total, $result->relative );
		}

		// Reset query vars.
		$this->reset_query();

		return $result;
	}

	/**
	 * Get the best selling forms
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param array $query Array of query arguments
	 *
	 * @return string
	 */
	public function get_busiest_day( $query = array() ) {
		$this->pre_query( $query );

		$sql = "SELECT DAYOFWEEK(post_date) AS day, COUNT(ID) as total
				FROM {$this->get_db()->posts}
				{$this->query_vars['inner_join_sql']}
				WHERE 1=1
				{$this->query_vars['where_sql'] }
				{$this->query_vars['date_sql'] }
				GROUP BY day
				ORDER BY day DESC
				LIMIT 1";

		$result = $this->get_db()->get_row( $sql );

		$day = is_null( $result )
			? ''
			: Give_Date::getDays()[ $result->day - 1 ];

		$this->reset_query();

		return $day;
	}

	/**
	 * Get the best selling forms
	 * @todo   : make this function dynamic with new api
	 *
	 * @since  2.4.1
	 * @access public
	 * @global wpdb $wpdb
	 *
	 * @param       $number int The number of results to retrieve with the default set to 10.
	 *
	 * @return array       Best selling forms
	 */
	public function get_best_selling( $number = 10 ) {
		$meta_table = __give_v20_bc_table_details( 'form' );

		$give_forms = $this->get_db()->get_results(
			$this->get_db()->prepare(
				"SELECT {$meta_table['column']['id']} as form_id, max(meta_value) as sales
				FROM {$meta_table['name']} WHERE meta_key='_give_form_sales' AND meta_value > 0
				GROUP BY meta_value+0
				DESC LIMIT %d;", $number
			) );

		return $give_forms;
	}

	/**
	 * Get most valuable cause
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param array $query
	 *
	 * @return int
	 */
	public function get_most_valuable_cause( $query = array() ) {
		$donation_col_name = Give()->payment_meta->get_meta_type() . '_id';

		$this->pre_query( $query );

		$sql = "SELECT m1.meta_value as form, COUNT(m1.{$donation_col_name}) as total_donation
			FROM {$this->get_db()->donationmeta} as m1
			INNER JOIN {$this->get_db()->posts} ON m1.{$donation_col_name}={$this->get_db()->posts}.ID
			WHERE 1=1
			{$this->query_vars['where_sql']}
			{$this->query_vars['date_sql']}
			AND m1.meta_key=%s
			GROUP BY form
			ORDER BY total_donation DESC
			LIMIT 1
			";

		$result = $this->get_db()->get_row(
			$this->get_db()->prepare(
				$sql,
				'_give_payment_form_id'
			)
		);

		$form = is_null( $result ) ? 0 : $result->form;

		return absint( $form );
	}

	/**
	 * Calculate number of refunded donations.
	 *
	 * @since 2.4.1
	 * @acess public
	 *
	 * @param array $query
	 *
	 * @return stdClass
	 */
	public function get_refund_count( $query = array() ) {
		$query['status'] = isset( $query['status'] )
			? $query['status']
			: array( 'refunded' );

		return $this->get_sales( $query );
	}

	/**
	 * Calculate amount of refunded donations.
	 *
	 * @since 2.4.1
	 * @acess public
	 *
	 * @param array $query
	 *
	 * @return stdClass
	 */
	public function get_refund( $query = array() ) {
		$query['status'] = isset( $query['status'] )
			? $query['status']
			: array( 'refunded' );

		return $this->get_earnings( $query );
	}

	/**
	 *  Set meta query
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param string $query_key
	 * @param string $meta_key
	 *
	 */
	private function set_meta_sql( $query_key, $meta_key ) {
		// Bailout.
		if ( empty( $this->query_vars[ $query_key ] ) ) {
			return;
		}

		$donation_col_name              = Give()->payment_meta->get_meta_type() . '_id';
		$this->query_vars[ $query_key ] = (array) $this->query_vars[ $query_key ];

		$alias = "m{$this->get_counter( $this->get_db()->donationmeta )}";
		$data  = implode( '\',\'', $this->query_vars[ $query_key ] );

		$this->query_vars['inner_join_sql'][] = "INNER JOIN {$this->get_db()->donationmeta} as {$alias} on {$alias}.{$donation_col_name}={$this->query_vars['table']}.{$this->query_vars['inner_join_at']}";

		$this->query_vars['where_sql'][] = " AND {$alias}.meta_key='{$meta_key}'";
		$this->query_vars['where_sql'][] = " AND {$alias}.meta_value IN ('{$data}')";


		// Set counter.
		$this->set_counter( $this->get_db()->donationmeta );
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
		parent::pre_query( $query );

		$this->query_vars['function'] = strtoupper( $this->query_vars['function'] );

		$sql_types = array( 'relative_date_sql', 'date_sql', 'inner_join_sql', 'where_sql' );

		// Set empty sql collection string to array
		foreach ( $sql_types as $sql_type ) {
			$this->query_vars[$sql_type] = array_filter( (array) $this->query_vars['where_sql'] );
		}

		// Where sql.
		if ( ! empty( $this->query_vars['status'] ) ) {
			if ( 'any' !== $this->query_vars['status'] ) {
				$this->query_vars['status'] = array_map( 'sanitize_text_field', $this->query_vars['status'] );

				$placeholders = implode( ', ', array_fill( 0, count( $this->query_vars['status'] ), '%s' ) );

				$this->query_vars['where_sql'][] = $this->get_db()->prepare( "AND {$this->get_db()->posts}.post_status IN ({$placeholders})", $this->query_vars['status'] );
			}
		}
		$this->query_vars['where_sql'][] = $this->get_db()->prepare( "AND {$this->get_db()->posts}.post_type=%s", 'give_payment' );

		// Date sql.
		if ( $this->query_vars["start_date"] ) {
			$this->query_vars['date_sql'][] = "AND {$this->get_db()->posts}.post_date>='{$this->query_vars["start_date"]}'";
		}

		if ( $this->query_vars["end_date"] ) {
			$this->query_vars['date_sql'][] = "AND {$this->get_db()->posts}.post_date<='{$this->query_vars["end_date"]}'";
		}

		// Relative date query.
		if ( $this->query_vars['range'] ) {
			if ( $this->query_vars["relative_start_date"] ) {
				$this->query_vars['relative_date_sql'][] = "AND {$this->get_db()->posts}.post_date>='{$this->query_vars["relative_start_date"]}'";
			}

			if ( $this->query_vars["relative_end_date"] ) {
				$this->query_vars['relative_date_sql'][] = "AND {$this->get_db()->posts}.post_date<='{$this->query_vars["relative_end_date"]}'";
			}
		}

		// Add sql for specific donation form.
		$this->set_meta_sql( 'give_forms', '_give_payment_form_id' );

		// Add sql for specific donation payment gateways.
		$this->set_meta_sql( 'gateways', '_give_payment_gateway' );

		// Create sql query string
		$sql_types = array( 'relative_date_sql', 'date_sql', 'inner_join_sql', 'where_sql' );

		foreach ( $sql_types as $sql_type ) {
			$this->query_vars[ $sql_type ] = is_array( $this->query_vars[ $sql_type ] )
				? implode( ' ', $this->query_vars[ $sql_type ] )
				: $this->query_vars[ $sql_type ];
		}
	}

}

// @todo: compatibility with recurring, fee recovery and currency switcher
// @todo: currency formatting compatibility for earnings and other
// @todo  review donation earning growth logic
// @todo: develop logic to sent raw and formatted value
// @todo: review number decimal format
// @todo: document stat query params
// @todo: think about table backward compatibility for paymentmeta
// @todo: think about custom dates

