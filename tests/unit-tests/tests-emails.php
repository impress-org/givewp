<?php

/**
 * @group emails
 */
class Tests_Emails extends Give_Unit_Test_Case {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test get_content_type function
	 *
	 * @since 2.0
	 * @cover Give_Emails::get_content_type
	 */
	public function test_get_content_type() {
		/*
		 * Case 1
		 */
		$content_type = Give()->emails->get_content_type();
		$this->assertSame( 'text/html', $content_type );

		/*
		 * Case 2
		 */
		Give()->emails->__set( 'content_type', '' );
		$content_type = Give()->emails->get_content_type();
		$this->assertSame( 'text/html', $content_type );

		/*
		 * Case 3
		 */
		Give()->emails->__set( 'content_type', '' );
		Give()->emails->__set( 'html', false );
		$content_type = Give()->emails->get_content_type();
		$this->assertSame( 'text/plain', $content_type );
	}
}