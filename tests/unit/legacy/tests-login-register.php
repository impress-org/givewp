<?php


/**
 * @group give_login_register
 */
class Tests_Login_Register extends Give_Unit_Test_Case {

	/**
	 * Set up tests.
	 */
	public function setUp() {

		parent::setUp();

		// Prevent give_die() from stopping tests.
		if ( ! defined( 'GIVE_UNIT_TESTS' ) ) {
			define( 'GIVE_UNIT_TESTS', true );
		}

		// Prevent wp_redirect from sending headers.
		add_filter( 'give_login_redirect', '__return_false' );

		// Set the current user.
		wp_set_current_user( 0 );

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

		give_process_login_form(
			array(
				'give_login_nonce' => wp_create_nonce( 'give-login-nonce' ),
				'give_user_login'  => 'wrong_username',
			)
		);

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

		give_process_login_form(
			array(
				'give_login_nonce' => wp_create_nonce( 'give-login-nonce' ),
				'give_user_login'  => 'admin@example.org',
				'give_user_pass'   => 'falsepass',
			)
		);

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

		ob_start();

		give_process_login_form(
			array(
				'give_login_nonce'    => wp_create_nonce( 'give-login-nonce' ),
				'give_user_login'     => 'admin@example.org',
				'give_user_pass'      => 'password',
				'give_login_redirect' => 'https://examplesite.org/',
			)
		);

		ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( give_get_errors() );

	}

	/**
	 * Test that the give_log_user_in() function successfully logs the user in.
	 *
	 * @since 1.3.2
	 */
	public function test_log_user_in_return() {
		$this->assertFalse( give_log_user_in( 0, '', '' ) );
	}

	/**
	 * Test that the give_log_user_in() function successfully logs the user in.
	 *
	 * @since 1.3.2
	 */
	public function test_log_user_in() {
		wp_logout();
		$user = new WP_User( 1 );
		give_log_user_in( $user->ID, $user->user_email, $user->user_pass );
		$this->assertTrue( is_user_logged_in() );
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
		$this->assertFalse( give_process_register_form( array( 'give_redirect' => '' ) ) );

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
		$this->assertFalse(
			give_process_register_form(
				array(
					'give_register_submit' => '',
				)
			)
		);

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

		give_process_register_form(
			array(
				'give_register_submit' => 1,
				'give_user_login'      => '',
				'give_user_email'      => '',
			)
		);

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

		give_process_register_form(
			array(
				'give_register_submit' => 1,
				'give_user_login'      => 'admin',
				'give_user_email'      => null,
			)
		);
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

		$_POST['give_register_submit'] = 1;
		$_POST['give_user_pass']       = 'password';
		$_POST['give_user_pass2']      = 'other-password';
		give_process_register_form(
			array(
				'give_register_submit' => 1,
				'give_user_login'      => 'admin#!@*&',
				'give_user_email'      => null,
			)
		);
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

		$_POST['give_register_submit'] = 1;
		$_POST['give_user_pass']       = '';
		$_POST['give_user_pass2']      = '';
		give_process_register_form(
			array(
				'give_register_submit' => 1,
				'give_user_login'      => 'random_username',
				'give_user_email'      => 'admin@example.org',
				'give_payment_email'   => 'someotheradminexample.org',
			)
		);
		$this->assertArrayHasKey( 'email_unavailable', give_get_errors() );
		$this->assertArrayHasKey( 'payment_email_invalid', give_get_errors() );

		// Clear errors for other test.
		give_clear_errors();
	}

	/**
	 * Test that the registration success.
	 *
	 * @since 1.3.2
	 */
	public function test_process_register_form_success() {

		// First check that this user does not exist.
		$user = new WP_User( 0, 'random_username' );
		$this->assertEmpty( $user->roles );
		$this->assertEmpty( $user->allcaps );
		$this->assertEmpty( (array) $user->data );

		$_POST['give_register_submit'] = 1;
		$_POST['give_user_pass']       = 'password';
		$_POST['give_user_pass2']      = 'password';

		$args = array(
			'give_register_submit' => 1,
			'give_user_login'      => 'random_username',
			'give_user_email'      => 'random_username@example.org',
			'give_payment_email'   => 'random_username@example.org',
			'give_user_pass'       => 'password',
			'give_redirect'        => '',
		);
		give_process_register_form( $args );

		// Now check to see if the user exists.
		$user = new WP_User( 0, 'random_username' );

		$this->assertEquals( $args['give_payment_email'], $user->user_email );
		$this->assertEquals( $args['give_user_login'], $user->display_name );
		$this->assertEquals( $args['give_user_login'], $user->user_login );
		$this->assertTrue( is_user_logged_in() );

		// Clear errors for other test.
		give_clear_errors();
	}

}
