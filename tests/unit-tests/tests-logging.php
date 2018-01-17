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
	 * Test Log Types
	 */
	public function test_log_types() {
		$types = Give()->logs->log_types();
		$this->assertEquals( 'sale', $types[0] );
		$this->assertEquals( 'gateway_error', $types[1] );
		$this->assertEquals( 'api_request', $types[2] );
	}

	/**
	 * Test Valid Log
	 */
	public function test_valid_log() {
		$this->assertTrue( Give()->logs->valid_type( 'sale' ) );
	}

	/**
	 * Test Fake Log
	 */
	public function test_fake_log() {
		$this->assertFalse( Give()->logs->valid_type( 'foo' ) );
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
		$this->assertNotNull( Give()->logs->insert_log( array(
			'log_type' => 'sale',
		) ) );
		$this->assertInternalType( 'integer', Give()->logs->insert_log( array(
			'log_type' => 'sale',
		) ) );
	}

	/**
	 * Test Get Logs
	 *
	 * @covers Give_Logging::get_logs
	 */
	public function test_get_logs() {
		$args   = array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		);
		$log_id = Give()->logs->insert_log( $args );
		$out    = Give()->logs->get_logs( 1, 'sale' );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'log_date', $out[0] );
		$this->assertObjectHasAttribute( 'log_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'log_content', $out[0] );
		$this->assertObjectHasAttribute( 'log_title', $out[0] );
		$this->assertObjectHasAttribute( 'log_parent', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->log_content );
		$this->assertEquals( 'Test Log', $out[0]->log_title );
		$this->assertEquals( 'sale', $out[0]->log_type );
	}

	/**
	 * Test Get Connected Logs
	 *
	 * @covers Give_Logging::get_connected_logs
	 */
	public function test_get_connected_logs() {
		$log_id = Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );

		$out = Give()->logs->get_connected_logs( array(
			'log_parent' => 1,
			'log_type' => 'sale',
		) );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'log_date', $out[0] );
		$this->assertObjectHasAttribute( 'log_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'log_content', $out[0] );
		$this->assertObjectHasAttribute( 'log_title', $out[0] );
		$this->assertObjectHasAttribute( 'log_parent', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->log_content );
		$this->assertEquals( 'Test Log', $out[0]->log_title );
		$this->assertEquals( 'sale', $out[0]->log_type );
	}

	/**
	 * Test Get Log Count
	 *
	 * @covers Give_Logging::get_log_count
	 */
	public function test_get_log_count() {
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );

		$this->assertInternalType( 'integer', Give()->logs->get_log_count( 1, 'sale' ) );
		$this->assertEquals( 5, Give()->logs->get_log_count( 1, 'sale' ) );
	}

	/**
	 * Test Delete Logs
	 *
	 * @covers Give_Logging::delete_logs
	 */
	public function test_delete_logs() {
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );
		Give()->logs->insert_log( array(
			'log_type'    => 'sale',
			'log_parent'  => 1,
			'log_title'   => 'Test Log',
			'log_content' => 'This is a test log inserted from PHPUnit',
		) );

		$this->assertNull( Give()->logs->delete_logs( 1 ) );
	}

	/**
	 * Test Delete Logs
	 *
	 * @covers Give_DB_Log_Meta::add_meta
	 * @covers Give_DB_Log_Meta::get_meta
	 * @covers Give_DB_Log_Meta::update_meta
	 * @covers Give_DB_Log_Meta::delete_meta
	 */
	public function test_metadata_fx() {
		$log_id = Give()->logs->add( 'Log Metadata', 'for meta data testing' );

		/**
		 * WordPress meta functions.
		 */
		// Test 1: add_post_meta
		add_post_meta( $log_id, 'add_log_meta', 'add_log_meta', true );
		$this->assertEquals( 'add_log_meta', give_get_meta( $log_id, 'add_log_meta', true ) );

		// Test 2: update_post_meta
		update_post_meta( $log_id, 'update_log_meta', 'update_log_meta' );
		$this->assertEquals( 'update_log_meta', give_get_meta( $log_id, 'update_log_meta', true ) );

		// Test 3: delete_post_meta
		$this->assertTrue( delete_post_meta( $log_id, 'update_log_meta' ) );
		$this->assertFalse( delete_post_meta( $log_id, 'update_log_meta' ) );


		/**
		 * Give_DB_Log_Meta functions.
		 */
		// Test 1: add_meta
		Give()->logs->logmeta_db->add_meta( $log_id, 'add_log_meta', 'add_log_meta', true );
		$this->assertEquals( 'add_log_meta', Give()->logs->logmeta_db->get_meta( $log_id, 'add_log_meta', true ) );

		// Test 2: update_meta
		Give()->logs->logmeta_db->update_meta( $log_id, 'update_log_meta', 'update_log_meta' );
		$this->assertEquals( 'update_log_meta', Give()->logs->logmeta_db->get_meta( $log_id, 'update_log_meta', true ) );

		// Test 3: delete_meta
		$this->assertTrue( Give()->logs->logmeta_db->delete_meta( $log_id, 'update_log_meta' ) );
		$this->assertFalse( Give()->logs->logmeta_db->delete_meta( $log_id, 'update_log_meta' ) );
	}
}
