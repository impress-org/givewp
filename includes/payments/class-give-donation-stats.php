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
		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql         = '';
		$is_relative = true === $this->query_vars['relative'];

		if ( $is_relative ) {
			$sql = "SELECT IFNULL(COUNT(ID), 0) AS sales, IFNULL(relative, 0) AS relative
					FROM {$this->get_db()->posts}
					CROSS JOIN (
						SELECT IFNULL(COUNT(ID), 0) AS relative
						FROM {$this->get_db()->posts}
						WHERE 1=1
						{$this->get_where_query_sql()}
						AND {$this->get_db()->posts}.post_date>='{$this->query_vars['relative_start_date']}'
						AND {$this->get_db()->posts}.post_date<='{$this->query_vars['relative_end_date']}'
					) o
					WHERE 1=1
					{$this->get_where_query_sql()}
					AND {$this->get_db()->posts}.post_date>='{$this->query_vars['start_date']}'
					AND {$this->get_db()->posts}.post_date<='{$this->query_vars['end_date']}'
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
		// Run pre-query checks and maybe generate SQL.
		$this->pre_query( $query );

		$sql         = '';
		$is_relative = true === $this->query_vars['relative'];

		if ( $is_relative ) {
			$sql = "SELECT IFNULL(SUM(m1.meta_value), 0) AS total, IFNULL(relative, 0) AS relative
					FROM {$this->get_db()->donationmeta} as m1
					CROSS JOIN (
						SELECT IFNULL(SUM(m1.meta_value), 0) AS relative
						FROM {$this->get_db()->donationmeta} as m1
						INNER JOIN {$this->get_db()->posts} on {$this->get_db()->posts}.ID = m1.donation_id
						WHERE 1=1
						{$this->get_where_query_sql()}
						AND m1.meta_key='_give_payment_total'
						AND {$this->get_db()->posts}.post_date>='{$this->query_vars['relative_start_date']}'
						AND {$this->get_db()->posts}.post_date<='{$this->query_vars['relative_end_date']}'
					) o
					INNER JOIN {$this->get_db()->posts} on {$this->get_db()->posts}.ID = m1.donation_id
					WHERE 1=1
					{$this->get_where_query_sql()}
					AND m1.meta_key='_give_payment_total'
					AND {$this->get_db()->posts}.post_date>='{$this->query_vars['start_date']}'
					AND {$this->get_db()->posts}.post_date<='{$this->query_vars['end_date']}'
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

		$where_sql = implode( ' ', $this->query_vars['where_sql'] );

		$sql = "SELECT DAYOFWEEK(post_date) AS day, COUNT(ID) as total
				FROM {$this->get_db()->posts}
				WHERE 1=1 {$where_sql}
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
			{$this->get_where_query_sql()}
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
	 * Pre process query
	 *
	 * @since  2.4.1
	 * @access protected
	 *
	 * @param array $query
	 */
	protected function pre_query( $query = array() ) {
		parent::pre_query( $query );

		// Generate status SQL if statuses have been set.
		if ( ! empty( $this->query_vars['status'] ) ) {
			if ( 'any' === $this->query_vars['status'] ) {
				$this->query_vars['status_sql'] = '';
			} else {
				$this->query_vars['status'] = array_map( 'sanitize_text_field', $this->query_vars['status'] );

				$placeholders = implode( ', ', array_fill( 0, count( $this->query_vars['status'] ), '%s' ) );

				$this->query_vars['where_sql'][] = $this->get_db()->prepare( "AND {$this->get_db()->posts}.post_status IN ({$placeholders})", $this->query_vars['status'] );
			}
		}

		$this->query_vars['where_sql'][] = $this->get_db()->prepare( "AND {$this->get_db()->posts}.post_type=%s", 'give_payment' );
	}

}

// @todo: compatibility with recurring, fee recovery and currency switcher
// @todo: currency formatting compatibility
// @todo review donation earning growth logic
