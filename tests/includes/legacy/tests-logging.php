<?php


/**
 * @group give_logging
 */
class Tests_Logging extends Give_Unit_Test_Case {
	protected $_object = null;

	/**
	 * Set it Up
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear it Down
	 */
	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test Add
	 *
	 * @covers Give_Logging::add
	 */
	public function test_add() {
		$this->assertNotNull( Give()->logs->add() );
		$this->assertInternalType( 'integer', Give()->logs->add() );
	}

	/**
	 * Test Insert Log
	 *
	 * @covers Give_Logging::insert_log
	 */
	public function test_insert_log() {
		$this->assertNotNull(
			Give()->logs->insert_log(
				array(
					'log_type' => 'error',
				)
			)
		);
		$this->assertInternalType(
			'integer',
			Give()->logs->insert_log(
				array(
					'log_type' => 'error',
				)
			)
		);
	}

	/**
	 * Test Get Logs
	 *
	 * @covers Give_Logging::get_logs
	 */
	public function test_get_logs() {
		$args   = array(
			'log_type'    => 'error',
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		);
		$log_id = Give()->logs->insert_log( $args );
		$out    = Give()->logs->get_logs( $log_id );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'log_date', $out[0] );
		$this->assertObjectHasAttribute( 'log_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'log_content', $out[0] );
		$this->assertObjectHasAttribute( 'log_title', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->log_content );
		$this->assertEquals( 'error', $out[0]->log_type );
	}


	/**
	 * Test Get Log Count
	 *
	 * @covers Give_Logging::get_log_count
	 */
	public function test_get_log_count() {
		Give()->logs->insert_log(
			array(
				'log_type'    => 'error',
				'log_content' => 'This is a test log inserted from PHPUnit',
			)
		);
		Give()->logs->insert_log(
			array(
				'log_type'    => 'error',
				'log_content' => 'This is a test log inserted from PHPUnit',
			)
		);
		Give()->logs->insert_log(
			array(
				'log_type'    => 'error',
				'log_content' => 'This is a test log inserted from PHPUnit',
			)
		);
		Give()->logs->insert_log(
			array(
				'log_type'    => 'error',
				'log_content' => 'This is a test log inserted from PHPUnit',
			)
		);
		Give()->logs->insert_log(
			array(
				'log_type'    => 'error',
				'log_content' => 'This is a test log inserted from PHPUnit',
			)
		);

		$count = Give()->logs->get_log_count( 0, 'error' );

		$this->assertInternalType( 'integer', $count );
		$this->assertEquals( 5, $count );
	}
}
