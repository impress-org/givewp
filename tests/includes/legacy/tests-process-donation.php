<?php


/**
 * @group give_process_donation_login
 */
class Tests_Process_Donation extends Give_Unit_Test_Case {

	/**
	 * Set up tests.
	 */
	public function setUp(): void {

		parent::setUp();

		// Prevent give_die() from stopping tests.
		if ( ! defined( 'GIVE_UNIT_TESTS' ) ) {
			define( 'GIVE_UNIT_TESTS', true );
		}

		// Set the current user.
		wp_set_current_user( 0 );

		// Clear errors for other test.
		give_clear_errors();

	}

	/**
	 * Test that the login form requires a username.
	 *
	 * @since TBD
	 */
	public function test_validate_user_login_empty_username() {

		$_POST = array(
			'give_user_login' => '',
			'give_user_pass'  => 'password',
		);

		$result = give_donation_form_validate_user_login();

		$this->assertSame( - 1, $result['user_id'] );
		$this->assertArrayHasKey( 'must_log_in', give_get_errors() );

		give_clear_errors();

	}

	/**
	 * Test that the login form requires a password.
	 *
	 * @since TBD
	 */
	public function test_validate_user_login_empty_password() {

		$_POST = array(
			'give_user_login' => 'admin',
			'give_user_pass'  => '',
		);

		$result = give_donation_form_validate_user_login();

		$this->assertSame( - 1, $result['user_id'] );
		$this->assertArrayHasKey( 'password_empty', give_get_errors() );

		give_clear_errors();

	}

	/**
	 * Test that an unknown login produces a generic invalid-credentials error.
	 *
	 * @since TBD
	 */
	public function test_validate_user_login_unknown_user() {

		$_POST = array(
			'give_user_login' => 'no_such_user',
			'give_user_pass'  => 'password',
		);

		$result = give_donation_form_validate_user_login();

		$this->assertSame( - 1, $result['user_id'] );
		$this->assertArrayHasKey( 'invalid_credentials', give_get_errors() );

		give_clear_errors();

	}

	/**
	 * Test that a wrong password for an existing user produces the same generic
	 * invalid-credentials error as an unknown user.
	 *
	 * @since TBD
	 */
	public function test_validate_user_login_same_error_for_unknown_user_and_wrong_password() {

		$_POST = array(
			'give_user_login' => 'no_such_user',
			'give_user_pass'  => 'password',
		);

		give_donation_form_validate_user_login();

		$unknown_user_errors = give_get_errors();

		give_clear_errors();

		$_POST = array(
			'give_user_login' => 'admin',
			'give_user_pass'  => 'wrong-password',
		);

		give_donation_form_validate_user_login();

		$wrong_password_errors = give_get_errors();

		$this->assertSame( $unknown_user_errors, $wrong_password_errors );

		give_clear_errors();

	}

	/**
	 * Test that correct credentials return the matching user data.
	 *
	 * @since TBD
	 */
	public function test_validate_user_login_correct_credentials() {

		$_POST = array(
			'give_user_login' => 'admin',
			'give_user_pass'  => 'password',
		);

		$result = give_donation_form_validate_user_login();

		$this->assertSame( 1, $result['user_id'] );
		$this->assertSame( 'admin', $result['user_login'] );
		$this->assertEmpty( give_get_errors() );

		give_clear_errors();

	}

	/**
	 * Test that correct credentials work when the login is an email address.
	 *
	 * @since TBD
	 */
	public function test_validate_user_login_correct_credentials_by_email() {

		$_POST = array(
			'give_user_login' => 'admin@example.org',
			'give_user_pass'  => 'password',
		);

		$result = give_donation_form_validate_user_login();

		$this->assertSame( 1, $result['user_id'] );
		$this->assertEmpty( give_get_errors() );

		give_clear_errors();

	}

	/**
	 * Register non-exiting wp_die() handlers so a login AJAX request can be
	 * inspected instead of terminating the test process.
	 *
	 * @return string The JSON payload sent by wp_send_json().
	 */
	private function run_login_ajax(): string {

		$ajax_die_handler = static function () {};
		$die_handler      = static function () {};

		$filter_ajax = static function () use ( $ajax_die_handler ) {
			return $ajax_die_handler;
		};

		$filter_die = static function () use ( $die_handler ) {
			return $die_handler;
		};

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', $filter_ajax );
		add_filter( 'wp_die_handler', $filter_die );

		ob_start();
		give_process_form_login();
		$response = ob_get_clean();

		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', $filter_ajax );
		remove_filter( 'wp_die_handler', $filter_die );

		return (string) $response;
	}

	/**
	 * Test that processing the login form without a nonce is denied and the
	 * user is not logged in.
	 *
	 * @since TBD
	 */
	public function test_process_form_login_denies_missing_nonce() {

		$_POST = array(
			'give_ajax'       => 1,
			'give_user_login' => 'admin',
			'give_user_pass'  => 'password',
		);

		$response = $this->run_login_ajax();

		$this->assertStringContainsString( 'invalid_nonce', $response );
		$this->assertFalse( is_user_logged_in() );

	}

	/**
	 * Test that processing the login form with an invalid nonce is denied and
	 * the user is not logged in.
	 *
	 * @since TBD
	 */
	public function test_process_form_login_denies_invalid_nonce() {

		$_POST = array(
			'give_ajax'        => 1,
			'give_user_login'  => 'admin',
			'give_user_pass'   => 'password',
			'give_login_nonce' => 'not-a-real-nonce',
		);

		$response = $this->run_login_ajax();

		$this->assertStringContainsString( 'invalid_nonce', $response );
		$this->assertFalse( is_user_logged_in() );

	}

	/**
	 * Test that processing the login form with a valid nonce and correct
	 * credentials logs the user in.
	 *
	 * @since TBD
	 */
	public function test_process_form_login_valid_nonce_correct_credentials() {

		$_POST = array(
			'give_ajax'        => 1,
			'give_user_login'  => 'admin',
			'give_user_pass'   => 'password',
			'give_login_nonce' => wp_create_nonce( 'give-login-nonce' ),
		);

		$response = $this->run_login_ajax();

		$this->assertStringContainsString( 'success', $response );
		$this->assertEmpty( give_get_errors() );
		$this->assertTrue( is_user_logged_in() );

	}

}
