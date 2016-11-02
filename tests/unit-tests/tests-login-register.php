<?php


/**
 * @group give_login_register
 */
class Tests_Login_Register extends Give_Unit_Test_Case {
	public function setUp() {
		parent::setUp();
		wp_set_current_user(0);
	}

	/**
	 * Test that the login form shortcode returns the expected string.
	 */
	public function test_login_form() {
		$this->assertContains( '<legend>Log into Your Account</legend>', give_login_form() );
	}

	/**
	 * Test that the registration form shortcode returns the expected output.
	 */
	public function test_register_form() {
		$this->assertContains( '<legend>Register a New Account</legend>', give_register_form() );
	}

	/**
	 * Test that there is displayed a error when the username is incorrect.
	 *
	 * @since 1.3.2
	 */
	public function test_process_login_form_incorrect_username() {

		give_process_login_form( array(
			'give_login_nonce' => wp_create_nonce( 'give-login-nonce' ),
			'give_user_login'  => 'wrong_username',
		) );

		$this->assertArrayHasKey( 'username_incorrect', give_get_errors() );
		$this->assertContains( 'The username you entered does not exist.', give_get_errors() );

		// Clear errors for other test
		give_clear_errors();

	}

	/**
	 * Test that there is displayed a error when the wrong password is entered.
	 *
	 * @since 1.3.2
	 */
	public function test_process_login_form_correct_username_invalid_pass() {

		give_process_login_form( array(
			'give_login_nonce' => wp_create_nonce( 'give-login-nonce' ),
			'give_user_login'  => 'admin@example.org',
			'give_user_pass'   => 'falsepass',
		) );

		$this->assertArrayHasKey( 'password_incorrect', give_get_errors() );
		$this->assertContains( 'The password you entered is incorrect.', give_get_errors() );

		// Clear errors for other test
		give_clear_errors();

	}

	/**
	 * Test correct login.
	 *
	 * @since 1.3.2
	 */
	public function test_process_login_form_correct_login() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		ob_start();
			give_process_login_form( array(
				'give_login_nonce' 	=> wp_create_nonce( 'give-login-nonce' ),
				'give_user_login' 	=> 'admin@example.org',
				'give_user_pass' 	=> 'password',
			) );
			$return = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( give_get_errors() );
		*/
	}

	/**
	 * Test that the give_log_user_in() function successfully logs the user in.
	 *
	 * @since 1.3.2
	 */
	public function test_log_user_in_return() {
		$this->assertNull( give_log_user_in( 0, '', '' ) );
	}

	/**
	 * Test that the give_log_user_in() function successfully logs the user in.
	 *
	 * @since 1.3.2
	 */
	public function test_log_user_in() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		wp_logout();
		give_log_user_in( 1 );
		$this->assertTrue( is_user_logged_in() );
		*/
	}

	/**
	 * Test that the function returns when the user is already logged in.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_logged_in() {

		$origin_user  = wp_get_current_user();
		$current_user = wp_set_current_user( 1 );

		$_POST['give_register_submit'] = '';
		$this->assertNull( give_process_register_form( array() ) );

		// Reset to origin
		$current_user = $origin_user;

	}

	/**
	 * Test that the function returns when the submit is empty.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_return_submit() {

		$_POST['give_register_submit'] = '';
		$this->assertNull( give_process_register_form( array(
			'give_register_submit' => '',
		) ) );

	}

	/**
	 * Test that 'empty' errors are displayed when certain fields are empty.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_empty_fields() {

		$_POST['give_register_submit'] = 1;
		$_POST['give_user_pass']       = '';
		$_POST['give_user_pass2']      = '';

		give_process_register_form( array(
			'give_register_submit' => 1,
			'give_user_login'      => '',
			'give_user_email'      => '',
		) );

		$errors = give_get_errors();
		$this->assertArrayHasKey( 'empty_username', $errors );
		$this->assertArrayHasKey( 'email_invalid', $errors );
		$this->assertArrayHasKey( 'empty_password', $errors );

		// Clear errors for other test
		give_clear_errors();

	}

	/**
	 * Test that a error is displayed when the username already exists.
	 * Also tests the password mismatch.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_username_exists() {

		$_POST['give_register_submit'] = 1;
		$_POST['give_user_pass']       = 'password';
		$_POST['give_user_pass2']      = 'other-password';

		give_process_register_form( array(
			'give_register_submit' => 1,
			'give_user_login'      => 'admin',
			'give_user_email'      => null,
		) );
		$this->assertArrayHasKey( 'username_unavailable', give_get_errors() );
		$this->assertArrayHasKey( 'password_mismatch', give_get_errors() );

		// Clear errors for other test
		give_clear_errors();
	}

	/**
	 * Test that a error is displayed when the username is invalid.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_username_invalid() {

		$_POST['give_register_submit'] 	= 1;
		$_POST['give_user_pass'] 		= 'password';
		$_POST['give_user_pass2'] 		= 'other-password';
		give_process_register_form( array(
			'give_register_submit' 	=> 1,
			'give_user_login' 		=> 'admin#!@*&',
			'give_user_email' 		=> null,
		) );
		$this->assertArrayHasKey( 'username_invalid', give_get_errors() );

		// Clear errors for other test
		give_clear_errors();
	}

	/**
	 * Test that a error is displayed when the email is already taken.
	 * Test that a error is displayed when the payment email is incorrect.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_payment_email_incorrect() {

		$_POST['give_register_submit'] 	= 1;
		$_POST['give_user_pass'] 		= '';
		$_POST['give_user_pass2'] 		= '';
		give_process_register_form( array(
			'give_register_submit' 	=> 1,
			'give_user_login' 		=> 'random_username',
			'give_user_email' 		=> 'admin@example.org',
			'give_payment_email' 	=> 'someotheradminexample.org',
		) );
		$this->assertArrayHasKey( 'email_unavailable', give_get_errors() );
		$this->assertArrayHasKey( 'payment_email_invalid', give_get_errors() );

		// Clear errors for other test
		give_clear_errors();
	}

	/**
	 * Test that the registration success.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_success() {
		$this->markTestIncomplete( 'Causes headers already sent errors');
		/*
		$_POST['give_register_submit'] 	= 1;
		$_POST['give_user_pass'] 		= 'password';
		$_POST['give_user_pass2'] 		= 'password';
		give_process_register_form( array(
			'give_register_submit' 	=> 1,
			'give_user_login' 		=> 'random_username',
			'give_user_email' 		=> 'random_username@example.org',
			'give_payment_email' 	=> 'random_username@example.org',
			'give_user_pass' 		=> 'password',
			'give_redirect' 			=> '/',
		) );

		// Clear errors for other test
		give_clear_errors();
		*/
	}

}
