<?php

/**
 * @group give_session
 */
class Tests_Session extends Give_Unit_Test_Case {

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
	 * Test Set Session Var
	 */
	public function test_set() {
		$this->assertEquals( 'bar', Give()->session->set( 'foo', 'bar' ) );
	}

	/**
	 * Test Get Session Var
	 */
	public function test_get() {
		$this->assertEquals( 'bar', Give()->session->get( 'foo' ) );
	}
}
