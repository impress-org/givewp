<?php

/**
 * @group give_stats
 */
class Tests_Stats extends WP_UnitTestCase {

	protected $_post;
	protected $_stats;
	protected $_payment_stats;
	protected $_payment_id;
	protected $_payment_id2;

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
	 * Test Predefined Date Ranges
	 */
	public function test_predefined_date_rages() {

		$stats = new Give_Stats();
		$out   = $stats->get_predefined_dates();

		$expected = array(
			'today'        => 'Today',
			'yesterday'    => 'Yesterday',
			'this_week'    => 'This Week',
			'last_week'    => 'Last Week',
			'this_month'   => 'This Month',
			'last_month'   => 'Last Month',
			'this_quarter' => 'This Quarter',
			'last_quarter' => 'Last Quarter',
			'this_year'    => 'This Year',
			'last_year'    => 'Last Year'
		);

		$this->assertEquals( $expected, $out );

	}

	/**
	 * Test Setup Dates
	 *
	 * @covers Give_Payment_Stats::setup_dates
	 */
	public function test_setup_dates() {

		$stats = new Give_Stats();

		// Set start date only
		$stats->setup_dates( 'yesterday' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );
		$this->assertEquals( $stats->end_date - $stats->start_date, DAY_IN_SECONDS - 1 );

		// Set some valid predefined date ranges
		$stats->setup_dates( 'yesterday', 'today' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some valid dates
		$stats->setup_dates( '2012-01-12', '2012-04-15' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some valid date strings
		$stats->setup_dates( 'January 15, 2013', 'February 24, 2013' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some valid timestamps
		$stats->setup_dates( '1379635200', '1379645200' );
		$this->assertInternalType( 'numeric', $stats->start_date );
		$this->assertInternalType( 'numeric', $stats->end_date );
		$this->assertGreaterThan( $stats->start_date, $stats->end_date );

		// Set some invalid dates
		$stats->setup_dates( 'nonvaliddatestring', 'nonvaliddatestring' );
		$this->assertInstanceOf( 'WP_Error', $stats->start_date );
		$this->assertInstanceOf( 'WP_Error', $stats->end_date );

	}


	/**
	 * Test Get Earnings by Date
	 *
	 * @covers Give_Payment_Stats::get_earnings
	 */
	public function test_get_earnings_by_date() {

		$stats    = new Give_Payment_Stats();
		$earnings = $stats->get_earnings( 0, 'this_month' );

		$this->assertEquals( 45, $earnings );

	}

	/**
	 * Test Get Sales by Date
	 *
	 * @covers Give_Payment_Stats::get_sales
	 */
	public function test_get_sales_by_date() {

		$stats = new Give_Payment_Stats();
		$sales = $stats->get_sales( 0, 'this_month' );

		$this->assertEquals( 2, $sales );
	}

	/**
	 * Test Get Earnings by Date of Give Donation Form
	 *
	 * @covers Give_Payment_Stats::get_earnings
	 */
	public function test_get_earnings_by_date_of_give_form() {

		//Test Payment 1 (Set Donation)
		$payment = new Give_Payment( $this->_payment_id );
		$form_id = $payment->form_id;

		$stats    = new Give_Payment_Stats();
		$earnings2 = $stats->get_earnings( $form_id, 'this_month' );

		$earnings = $stats->get_earnings( false, 'this_month' );

		echo '<pre>';
		var_dump($this->_payment_id);
		var_dump($payment);
		echo '</pre>';
		die();

		$earnings = $stats->get_earnings( $form_id, 'this_month' );
		$this->assertEquals( 20, $earnings );

		//Now check payment 2
		$payment = new Give_Payment( $this->_payment_id2 );
		$form_id = $payment->form_id;

		$stats    = new Give_Payment_Stats();
		$earnings = $stats->get_earnings( $form_id, 'this_month' );
		$this->assertEquals( 25, $earnings );

	}

	/**
	 * Test Get Best Selling Donation Forms
	 *
	 * @covers Give_Payment_Stats::get_best_selling
	 */
	public function test_get_best_selling() {

		$stats        = new Give_Payment_Stats();
		$best_selling = $stats->get_best_selling();

		//Best selling should return an array ordered by sale count
		$this->assertLessThan( $best_selling[0]->sales, $best_selling[1]->sales );

	}

	/**
	 * Test Get Sales by Date of Give Donation Form
	 */
	public function test_get_sales_by_date_of_give_form() {
		$payment = new Give_Payment( $this->_payment_id );
		$form_id = $payment->form_id;

		$stats = new Give_Payment_Stats;
		$sales = $stats->get_sales( $form_id, 'this_month' );

		$this->assertEquals( 1, $sales );
	}

}
