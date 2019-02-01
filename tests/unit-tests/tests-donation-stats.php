<?php

/**
 * @group give_stats
 */
class Tests_Donation_Stats extends Give_Unit_Test_Case {

	protected $_post;
	protected $_stats;
	protected $_payment_stats;
	protected $_payment_id;
	protected $_payment_id2;
	protected $_new_form_id;

	/**
	 * Set it Up
	 */
	public function setUp() {

		parent::setUp();

		$this->_payment_id  = Give_Helper_Payment::create_simple_payment(); //$20
		$this->_payment_id2 = Give_Helper_Payment::create_multilevel_payment(); //$25
		give_update_payment_status( $this->_payment_id );
		give_update_payment_status( $this->_payment_id2 );

	}

	/**
	 * Tear it Down
	 */
	public function tearDown() {
		global $wpdb;

		parent::tearDown();
		Give_Helper_Payment::delete_payment( $this->_payment_id );
		Give_Helper_Payment::delete_payment( $this->_payment_id2 );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'give_stats_%'" );
	}


	/**
	 * Test Get Earnings by Date
	 *
	 * @covers Give_Donation_Stats::get_earnings
	 */
	public function test_get_earnings_by_date() {

		$stats    = new Give_Donation_Stats();
		$earnings = $stats->get_earnings( array( 'range' => 'this_month' ) )->total;

		$this->assertEquals( 45, $earnings );

	}

	/**
	 * Test Get Sales by Date
	 *
	 * @covers Give_Donation_Stats::get_sales
	 */
	public function test_get_sales_by_date() {

		$stats = new Give_Donation_Stats();
		$sales = $stats->get_sales( array( 'range' => 'this_month' ) )->sales;

		$this->assertEquals( 2, $sales );
	}

	/**
	 * Test Get Earnings by Date of Give Donation Form
	 *
	 * @covers Give_Donation_Stats::get_earnings
	 */
	public function test_get_earnings_by_date_of_give_form() {
		$stats              = new Give_Donation_Stats();
		$earnings           = $stats->get_earnings( array(
			'range'      => 'this_month',
		) )->total;

		$this->assertEquals( 45, $earnings );

	}

	/**
	 * Test Get Best Selling Donation Forms
	 *
	 * @covers Give_Donation_Stats::get_best_selling
	 */
	// public function test_get_best_selling() {
	//
	// 	$stats        = new Give_Donation_Stats();
	// 	$best_selling = $stats->get_best_selling();
	//
	// 	//Best selling should return an array ordered by sale count
	// 	$this->assertLessThan( $best_selling[0]->sales, $best_selling[1]->sales );
	//
	// }

	/**
	 * Test Get Sales by Date of Give Donation Form
	 */
	public function test_get_sales_by_date_of_give_form() {

		$stats = new Give_Donation_Stats();
		$sales = $stats->get_sales(
			array(
				'range'      => 'this_month',
			)
		)->sales;

		$this->assertEquals( 2, $sales );
	}

}
