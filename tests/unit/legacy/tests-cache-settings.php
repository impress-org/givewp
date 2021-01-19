<?php

/**
 * Class Tests_Cache_Settings
 */
class Tests_Cache_Settings extends Give_Unit_Test_Case {

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Initial cache testing
	 *
	 * @since 2.4.0
	 */
	public function test_initial() {
		$default_settings = give_get_default_settings();
		$saved_settings   = Give_Cache_Setting::get_settings();

		$deprecated_settings = array(
			'global_offline_donation_email',
			'donation_notification',
			'donation_receipt',
		);

		foreach ( $default_settings as $name => $value ) {
			if ( in_array( $name, $deprecated_settings ) ) {
				continue;
			}
			$this->assertTrue( array_key_exists( $name, $saved_settings ), "{$name} key does not find." );
		}

		$this->assertTrue( GIVE_VERSION === Give_Cache_Setting::get_option( 'give_version' ), 'Plugin version does not match.' );
	}

	/**
	 * Add setting test
	 *
	 * @since 2.4.0
	 */
	public function test_add_settings() {
		$settings = Give_Cache_Setting::get_settings();
		$this->assertFalse( array_key_exists( 'custom_key', $settings ) );

		$settings['custom_key'] = true;
		update_option( 'give_settings', $settings );

		$settings = Give_Cache_Setting::get_settings();
		$this->assertTrue( array_key_exists( 'custom_key', $settings ) );
	}

	/**
	 * Delete setting test
	 *
	 * @since 2.4.0
	 */
	public function test_delete_settings() {
		$settings = Give_Cache_Setting::get_settings();
		$this->assertEquals( $settings['currency'], 'USD' );

		unset( $settings['currency'] );
		update_option( 'give_settings', $settings );

		$settings = Give_Cache_Setting::get_settings();
		$this->assertFalse( array_key_exists( 'currency', $settings ) );

		$settings['currency'] = 'USD';
		update_option( 'give_settings', $settings );
	}

	/**
	 * Update setting test
	 *
	 * @since 2.4.0
	 */
	public function test_update_settings() {
		$settings = Give_Cache_Setting::get_settings();
		$this->assertEquals( $settings['currency'], 'USD' );

		$settings['currency'] = 'INR';
		update_option( 'give_settings', $settings );

		$settings = Give_Cache_Setting::get_settings();
		$this->assertEquals( $settings['currency'], 'INR' );
	}
}
