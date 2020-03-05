<?php

/**
 * Class Tests_URL
 *
 * @group give_url
 */
class Tests_URL extends Give_Unit_Test_Case {

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test AJAX URL.
	 */
	public function test_ajax_url() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['HTTPS']       = 'off';

		$this->assertEquals( give_get_ajax_url(), get_site_url( null, '/wp-admin/admin-ajax.php', 'http' ) );
	}

	/**
	 * Test Current Page URL.
	 */
	public function test_current_page_url() {
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['SERVER_NAME'] = 'example.org';
		$this->assertEquals( 'http://example.org/', give_get_current_page_url() );
	}
}
