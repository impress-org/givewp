<?php


/**
 * @group give_session
 */
class Tests_Session extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		new \Give_Session;
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set() {
		$this->assertEquals( 'bar', Give()->session->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', Give()->session->get( 'foo' ) );
	}
}