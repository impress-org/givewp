<?php

/**
 * Donor stats
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.2.0
 */
class Give_Donor_Stats extends Give_Stats {
	/**
	 * Object.
	 *
	 * @since 2.4.1
	 * @var Give_Donation_Stats
	 */
	private $donation_stats;

	/**
	 * Required query vars
	 *
	 * @since 2.4.1
	 * @var array
	 */
	protected $required_query_vars = array(
		'donor_id',
	);

	/**
	 * Singleton pattern.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	public function __construct() {
		// Add additional default query params
		$this->query_var_defaults = array_merge( array(
			'donor_id'   => 0,
			'give_forms' => array(),
		), $this->query_var_defaults );

		parent::__construct();

		$this->donation_stats = new Give_Donation_Stats();
	}

	/**
	 *  Get total donated amount
	 *
	 *
	 * @since  2.2.0
	 * @since  2.4.1 Add Give_Donation_Stats integration.
	 * @access public
	 *
	 * @param array $query
	 *
	 * @return stdClass
	 *
	 */
	public function donated( $query = array() ) {
		return $this->donation_stats->get_earnings( $query );
	}

	/**
	 *  Get total donation count
	 *
	 *
	 * @since  2.4.1
	 * @access public
	 *
	 * @param array $query
	 *
	 * @return string
	 *
	 */
	public function donation_count( $query = array() ) {
		return $this->donation_stats->get_sales( $query );
	}
}

// @todo: add document to query params.
