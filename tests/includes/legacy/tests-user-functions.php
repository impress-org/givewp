<?php

/**
 * @group user_function
 */
class Tests_User_Function extends Give_Unit_Test_Case {
	/**
	 * @var WP_Post Simple form id.
	 */
	private $_simple_form;

	/**
	 * Setup
	 */
	public function setUp() {
		parent::setUp();

		$this->_simple_form = Give_Helper_Form::create_simple_form();
	}

	/**
	 * Tear it Down
	 */
	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test give_validate_username function
	 *
	 * @since 1.8
	 *
	 * @cover give_validate_username
	 */
	function test_give_validate_username() {
		/*
		 * Check 1
		 *
		 * Username is empty.
		 */
		$output = give_validate_username( '' );

		$this->assertFalse( $output );

		/*
		 * Check 2
		 *
		 * Username does not exit (New Registration)
		 */
		$output = give_validate_username( 'devin' );

		$this->assertTrue( $output );

		/*
		 * Check 3
		 *
		 * Username is empty and registration required for form.
		 */
		// Stop guest checkout.
		give_update_meta( $this->_simple_form->ID, '_give_logged_in_only', 'enabled' );

		$output = give_validate_username( '', $this->_simple_form->ID );

		$this->assertFalse( $output );

		/*
		 * Check 4
		 *
		 * Username exits
		 */
		$output = give_validate_username( 'admin' );

		$this->assertFalse( $output );
	}


	/**
	 * Test give_validate_user_email function
	 *
	 * @since 1.8
	 *
	 * @cover give_validate_user_email
	 */
	function test_give_validate_user_email() {
		/*
		 * Check 1
		 *
		 * Empty email
		 */
		$output = give_validate_user_email( '' );

		$this->assertFalse( $output );

		/*
		 * Check 2
		 *
		 * Bad email
		 */
		$output = give_validate_user_email( 'xyz' );

		$this->assertFalse( $output );

		/*
		 * Check 3
		 *
		 * Verify email ( User already exist with primary email )
		 */
		$output = give_validate_user_email( 'admin@example.org' );

		$this->assertFalse( $output );

		/*
		 * Check 4
		 *
		 * Verify email ( User doesn't exists with primary email)
		 */
		$output = give_validate_user_email( 'hello@example.org' );

		$this->assertTrue( $output );

		/*
		 * Check 5
		 *
		 * Already registered email and newly register user
		 */
		$output = give_validate_user_email( 'admin@example.org', true );

		$this->assertFalse( $output );

		/*
		 * Check 6
		 *
		 * Email doesn't exists and newly register user
		 */
		$output = give_validate_user_email( 'hello@example.org', true );

		$this->assertTrue( $output );
	}

	/**
	 * Test give_validate_user_password function
	 *
	 * @since 1.8
	 *
	 * @cover give_validate_user_password
	 */
	function test_give_validate_user_password() {
		/*
		 * Check 1
		 *
		 * Password & Confirm password is empty
		 */
		$output = give_validate_user_password();

		$this->assertTrue( $output );

		/*
		 * Check 2
		 *
		 * Register new user & empty confirm password
		 */
		$output = give_validate_user_password( 'xyz', '', true );

		$this->assertFalse( $output );

		/*
		 * Check 3
		 *
		 * Register new user & empty password
		 */
		$output = give_validate_user_password( '', 'xyz', true );

		$this->assertFalse( $output );

		/*
		 * Check 4
		 *
		 *  Register new user & password/confirm password is not empty and weak password
		 */
		$output = give_validate_user_password( 'xyz', 'xyz', true );

		$this->assertFalse( $output );

		/*
		 * Check 5
		 *
		 *  Register new user & password and confirm password mismatch
		 */
		$output = give_validate_user_password( 'xyzabc', 'abcxyz', true );

		$this->assertFalse( $output );

		/*
		 * Check 6
		 *
		 *  Register new user & password/confirm password is not empty
		 */
		$output = give_validate_user_password( 'xyzabc', 'xyzabc', true );

		$this->assertTrue( $output );

		/*
		 * Check 7
		 *
		 * Existing user & empty confirm password
		 */
		$output = give_validate_user_password( 'xyz', '', false );

		$this->assertFalse( $output );

		/*
		 * Check 8
		 *
		 * Existing user & empty password
		 */
		$output = give_validate_user_password( '', 'xyz', false );

		$this->assertFalse( $output );

		/*
		 * Check 9
		 *
		 *  Existing user & password/confirm password is not empty and weak password
		 */
		$output = give_validate_user_password( 'xyz', 'xyz', false );

		$this->assertFalse( $output );

		/*
		 * Check 10
		 *
		 *  Existing user & password and confirm password mismatch
		 */
		$output = give_validate_user_password( 'xyzabc', 'abcxyz', false );

		$this->assertFalse( $output );

		/*
		 * Check 11
		 *
		 *  Existing user & password/confirm password is not empty
		 */
		$output = give_validate_user_password( 'xyzabc', 'xyzabc', false );

		$this->assertTrue( $output );
	}

	/**
	 * Test give_donor_email_exists function
	 *
	 * @since 1.8.9
	 *
	 * @todo We need to add Check 3 for Email already exists for donor,once we add additional_email test check for donor.
	 *
	 * @cover give_donor_email_exists
	 */
	function test_give_donor_email_exists() {
		/*
		 * Check 1
		 *
		 * Empty email doesn't exists
		 */
		$output = give_donor_email_exists( '' );
		$this->assertFalse( $output );

		/*
		 * Check 2
		 *
		 * Bad Email doesn't exists
		 */
		$output = give_donor_email_exists( 'xyz' );

		$this->assertFalse( $output );

	}

	/**
	 * Test give_is_additional_email function
	 *
	 * @since 1.8.13
	 *
	 * @cover give_is_additional_email
	 */
	function test_give_is_additional_email() {
		/*
		 * Check 1
		 *
		 * Empty email doesn't exists
		 */
		$output = give_is_additional_email( '' );
		$this->assertFalse( $output );

		/*
		 * Check 2
		 *
		 * Bad Email doesn't exists
		 */
		$output = give_is_additional_email( 'xyz' );

		$this->assertFalse( $output );

		/*
		 * Check 3
		 *
		 * Not an additional email. i.e. Primary Email
		 */
		$output = give_is_additional_email( 'admin@example.org' );

		$this->assertFalse( $output );
	}
}

