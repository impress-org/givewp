<?php

use Give\DonorDashboards\Helpers;

/**
 * @group give_email_access
 */
class Tests_Email_Access extends Give_Unit_Test_Case {

	/**
	 * Set up tests.
	 */
	public function setUp(): void {

		parent::setUp();

		wp_set_current_user( 0 );

		unset( $_GET['give_nl'], $_COOKIE['give_nl'], $_POST['give_email'], $_POST['_wpnonce'] );

	}

	/**
	 * A crafted give_nl[]= parameter arrives as an array; get_token() must coerce it to an empty string.
	 *
	 * @since TBD
	 */
	public function test_get_token_coerces_array_input_to_empty_string() {

		$_GET['give_nl'] = [ '' ];

		$this->assertSame( '', Give()->email_access->get_token() );

	}

	/**
	 * A missing token must return an empty string.
	 *
	 * @since TBD
	 */
	public function test_get_token_returns_empty_string_when_token_is_missing() {

		$this->assertSame( '', Give()->email_access->get_token() );

	}

	/**
	 * is_valid_token() must reject an array token (give_nl[]=) without querying the database.
	 *
	 * @since TBD
	 */
	public function test_is_valid_token_rejects_array_token() {

		$this->assertFalse( Give()->email_access->is_valid_token( [ '' ] ) );
		$this->assertFalse( Give()->email_access->is_valid_token( [ 'any-token' ] ) );

	}

	/**
	 * is_valid_token() must reject an empty string token (give_nl=).
	 *
	 * @since TBD
	 */
	public function test_is_valid_token_rejects_empty_string_token() {

		$this->assertFalse( Give()->email_access->is_valid_token( '' ) );

	}

	/**
	 * is_valid_token() must still accept a real, non-empty string token.
	 *
	 * @since TBD
	 */
	public function test_is_valid_token_accepts_valid_token() {

		$donor_id = Give()->donors->add(
			[
				'name'    => 'Email Access Donor',
				'email'   => 'email-access-donor@example.org',
				'user_id' => 0,
			]
		);

		Give()->email_access->set_verify_key( $donor_id, 'email-access-donor@example.org', 'real-verify-key' );

		$this->assertTrue( Give()->email_access->is_valid_token( 'real-verify-key' ) );
		$this->assertSame( 'email-access-donor@example.org', Give()->email_access->token_email );

	}

	/**
	 * is_valid_verify_key() must reject an array token (give_nl[]=).
	 *
	 * @since TBD
	 */
	public function test_is_valid_verify_key_rejects_array_token() {

		$this->assertFalse( Give()->email_access->is_valid_verify_key( [ '' ] ) );
		$this->assertFalse( Give()->email_access->is_valid_verify_key( [ 'any-token' ] ) );

	}

	/**
	 * is_valid_verify_key() must reject an empty string token (give_nl=).
	 *
	 * @since TBD
	 */
	public function test_is_valid_verify_key_rejects_empty_string_token() {

		$this->assertFalse( Give()->email_access->is_valid_verify_key( '' ) );

	}

	/**
	 * is_valid_verify_key() must still accept and consume a real, non-empty string key.
	 *
	 * @since TBD
	 */
	public function test_is_valid_verify_key_accepts_valid_key() {

		global $wpdb;

		$donor_id = Give()->donors->add(
			[
				'name'    => 'Verify Key Donor',
				'email'   => 'verify-key-donor@example.org',
				'user_id' => 0,
			]
		);

		Give()->email_access->set_verify_key( $donor_id, 'verify-key-donor@example.org', 'real-verify-key' );

		$this->assertTrue( Give()->email_access->is_valid_verify_key( 'real-verify-key' ) );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT verify_key, token FROM {$wpdb->donors} WHERE id = %d", $donor_id ) );
		$this->assertSame( '', $row->verify_key );
		$this->assertSame( 'real-verify-key', $row->token );

	}

	/**
	 * The donor-dashboard permission callback must deny a crafted give_nl[]= array token.
	 *
	 * @since TBD
	 */
	public function test_is_donor_logged_in_rejects_array_token() {

		give_update_option( 'email_access', 'enabled' );

		$_GET['give_nl'] = [ '' ];

		$this->assertFalse( Helpers::isDonorLoggedIn() );

	}

	/**
	 * The donor-dashboard permission callback must deny an empty give_nl= token.
	 *
	 * @since TBD
	 */
	public function test_is_donor_logged_in_rejects_empty_string_token() {

		give_update_option( 'email_access', 'enabled' );

		$_GET['give_nl'] = '';

		$this->assertFalse( Helpers::isDonorLoggedIn() );

	}

	/**
	 * The donor-dashboard permission callback must still grant access to a real token.
	 *
	 * @since TBD
	 */
	public function test_is_donor_logged_in_accepts_valid_token() {

		give_update_option( 'email_access', 'enabled' );

		$donor_id = Give()->donors->add(
			[
				'name'    => 'Dashboard Donor',
				'email'   => 'dashboard-donor@example.org',
				'user_id' => 0,
			]
		);

		Give()->email_access->set_verify_key( $donor_id, 'dashboard-donor@example.org', 'real-verify-key' );

		$_GET['give_nl'] = 'real-verify-key';

		$this->assertTrue( Helpers::isDonorLoggedIn() );

	}

}
