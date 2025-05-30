<?php

/**
 * @group give_session
 */
class Tests_Session extends Give_Unit_Test_Case {

	/**
	 * Set it Up
	 */
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Tear it Down
	 */


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
