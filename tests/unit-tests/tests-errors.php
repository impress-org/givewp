<?php

/**
 * Class Tests_Errors
 */
class Tests_Errors extends Give_Unit_Test_Case {

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();

		give_set_error( 'invalid_email', 'Please enter a valid email address.' );
		give_set_error( 'invalid_user', 'The user information is invalid.' );
		give_set_error( 'username_incorrect', 'The username you entered does not exist.' );
		give_set_error( 'password_incorrect', 'The password you entered is incorrect.' );
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test setting errors.
	 */
	public function test_set_errors() {
		$errors = Give()->session->get( 'give_errors' );

		$this->assertArrayHasKey( 'invalid_email', $errors );
		$this->assertArrayHasKey( 'invalid_user', $errors );
		$this->assertArrayHasKey( 'username_incorrect', $errors );
		$this->assertArrayHasKey( 'password_incorrect', $errors );
	}

	/**
	 * Test clearing errors.
	 */
	public function test_clear_errors() {
		give_clear_errors();
		$this->assertFalse( Give()->session->get( 'give_errors' ) );
	}

	/**
	 * Test unsetting errors.
	 */
	public function test_unset_error() {
		give_unset_error( 'invalid_email' );
		$errors = Give()->session->get( 'give_errors' );

		$expected = array(
			'invalid_user'       => 'The user information is invalid.',
			'username_incorrect' => 'The username you entered does not exist.',
			'password_incorrect' => 'The password you entered is incorrect.',
		);

		$this->assertEquals( $expected, $errors );
	}
}
