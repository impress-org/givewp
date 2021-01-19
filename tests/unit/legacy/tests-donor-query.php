<?php

/**
 * Class Give_Tests_Donors
 */
class Tests_Give_Donors_Query extends Give_Unit_Test_Case {
	/**
	 * Give_Donors_Query Object.
	 *
	 * @since  2.0
	 * @access private
	 * @var Give_Donors_Query
	 */
	private $db_query;

	/**
	 * Set it up
	 *
	 * @since 2.0
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear it Down
	 *
	 * @since 2.0
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test get_sql
	 *
	 * @since         2.0
	 * @access        public
	 *
	 * @param array  $donor_query_params
	 * @param string $expected
	 *
	 * @covers        Give_Donors_Query::get_sql
	 * @dataProvider  test_get_sql_provider
	 */
	public function test_get_sql( $donor_query_params, $expected ) {
		$this->db_query = new Give_Donors_Query( $donor_query_params );
		$this->assertSame( $expected, $this->db_query->get_sql() );
	}


	/**
	 * Data provider for test_get_sql
	 *
	 * @since  2.0
	 * @access public
	 */
	public function test_get_sql_provider() {
		return array(
			/**
			 * Case1: number param testing
			 */
			array(
				array(),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'number' => 2 ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,2;',
			),
			array(
				array( 'number' => - 1 ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,99999999999;',
			),

			/**
			 * Case1: where param testing
			 */
			array(
				array( 's' => 'name:Devin' ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.name LIKE \'%Devin%\' ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 's' => 'note:Devin' ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.notes LIKE \'%Devin%\' ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'email' => 'devin@givewp.com' ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.email = \'devin@givewp.com\' ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'email' => array( 'devin@givewp.com', 'matt@givewp.com' ) ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.email IN( \'devin@givewp.com\', \'matt@givewp.com\' ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'donor' => 1 ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.id IN( 1 ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'donor' => array( 1, 2 ) ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.id IN( 1,2 ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'donor' => '1,2' ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.id IN( 1,2 ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'user' => 1 ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.user_id IN( 1 ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'user' => array( 1, 2 ) ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.user_id IN( 1,2 ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'user' => '1, 2' ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND wptests_give_donors.user_id IN( 1,2 ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
			array(
				array( 'date_query' => 'year=2012&monthnum=12&day=12' ),
				'SELECT wptests_give_donors.* FROM wptests_give_donors WHERE 1=1 AND ( ( YEAR( wptests_give_donors.date_created ) = 2012 AND MONTH( wptests_give_donors.date_created ) = 12 AND DAYOFMONTH( wptests_give_donors.date_created ) = 12 ) ) ORDER BY wptests_give_donors.id+0 DESC LIMIT 0,20;',
			),
		);
	}
}
