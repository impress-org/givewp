<?php

/**
 * @group formatting
 */
class Tests_MISC_Functions extends Give_Unit_Test_Case {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * test for give_get_currency_name
	 *
	 * @since         1.8.8
	 * @access        public
	 *
	 * @param string $value
	 * @param string $expected
	 *
	 * @cover         give_get_currency_name
	 * @dataProvider  give_get_currency_name_data_provider
	 */
	public function test_give_get_currency_name( $value, $expected ) {
		$this->assertEquals( $expected, $value );
	}


	/**
	 * Data Provider.
	 *
	 * @since 1.8.8
	 * @return array
	 */
	public function give_get_currency_name_data_provider() {
		return array(
			array( give_get_currency_name( 'USD' ), __( 'US Dollars', 'give' ) ),
			array( give_get_currency_name( 'EUR' ), __( 'Euros', 'give' ) ),
			array( give_get_currency_name( 'GBP' ), __( 'Pounds Sterling', 'give' ) ),
			array( give_get_currency_name( 'AUD' ), __( 'Australian Dollars', 'give' ) ),
			array( give_get_currency_name( 'BRL' ), __( 'Brazilian Real', 'give' ) ),
			array( give_get_currency_name( 'CAD' ), __( 'Canadian Dollars', 'give' ) ),
			array( give_get_currency_name( 'CZK' ), __( 'Czech Koruna', 'give' ) ),
			array( give_get_currency_name( 'DKK' ), __( 'Danish Krone', 'give' ) ),
			array( give_get_currency_name( 'HKD' ), __( 'Hong Kong Dollar', 'give' ) ),
			array( give_get_currency_name( 'HUF' ), __( 'Hungarian Forint', 'give' ) ),
			array( give_get_currency_name( 'ILS' ), __( 'Israeli Shekel', 'give' ) ),
			array( give_get_currency_name( 'JPY' ), __( 'Japanese Yen', 'give' ) ),
			array( give_get_currency_name( 'MYR' ), __( 'Malaysian Ringgits', 'give' ) ),
			array( give_get_currency_name( 'MXN' ), __( 'Mexican Peso', 'give' ) ),
			array( give_get_currency_name( 'MAD' ), __( 'Moroccan Dirham', 'give' ) ),
			array( give_get_currency_name( 'NZD' ), __( 'New Zealand Dollar', 'give' ) ),
			array( give_get_currency_name( 'NOK' ), __( 'Norwegian Krone', 'give' ) ),
			array( give_get_currency_name( 'PHP' ), __( 'Philippine Pesos', 'give' ) ),
			array( give_get_currency_name( 'PLN' ), __( 'Polish Zloty', 'give' ) ),
			array( give_get_currency_name( 'SGD' ), __( 'Singapore Dollar', 'give' ) ),
			array( give_get_currency_name( 'KRW' ), __( 'South Korean Won', 'give' ) ),
			array( give_get_currency_name( 'ZAR' ), __( 'South African Rand', 'give' ) ),
			array( give_get_currency_name( 'SEK' ), __( 'Swedish Krona', 'give' ) ),
			array( give_get_currency_name( 'CHF' ), __( 'Swiss Franc', 'give' ) ),
			array( give_get_currency_name( 'TWD' ), __( 'Taiwan New Dollars', 'give' ) ),
			array( give_get_currency_name( 'THB' ), __( 'Thai Baht', 'give' ) ),
			array( give_get_currency_name( 'INR' ), __( 'Indian Rupee', 'give' ) ),
			array( give_get_currency_name( 'TRY' ), __( 'Turkish Lira', 'give' ) ),
			array( give_get_currency_name( 'IRR' ), __( 'Iranian Rial', 'give' ) ),
			array( give_get_currency_name( 'RUB' ), __( 'Russian Rubles', 'give' ) ),
			array( give_get_currency_name( 'Wrong_Currency_Symbol' ), '' ),
		);
	}

	/**
	 * test for give post type meta related functions
	 *
	 * @since         1.8.8
	 * @access        public
	 *
	 * @param int $form_or_donation_id
	 *
	 * @cover         give_get_meta
	 * @cover         give_update_meta
	 * @cover         give_delete_meta
	 *
	 * @dataProvider  give_meta_helpers_provider
	 */
	public function test_give_meta_helpers( $form_or_donation_id ) {
		$value = give_get_meta( $form_or_donation_id, 'testing_meta', true, 'TEST1' );
		$this->assertEquals( 'TEST1', $value );

		$status = give_update_meta( $form_or_donation_id, 'testing_meta', 'TEST' );
		$this->assertEquals( true, (bool) $status );

		$status = give_update_meta( $form_or_donation_id, 'testing_meta', 'TEST' );
		$this->assertEquals( false, (bool) $status );

		$value = give_get_meta( $form_or_donation_id, 'testing_meta', true );
		$this->assertEquals( 'TEST', $value );

		$status = give_delete_meta( $form_or_donation_id, 'testing_meta', 'TEST2' );
		$this->assertEquals( false, $status );

		$status = give_delete_meta( $form_or_donation_id, 'testing_meta' );
		$this->assertEquals( true, $status );
	}
	
	
	/**
	 * Data provider for test_give_meta_helpers
	 *
	 * @since 2.0
	 * @access private
	 */
	public function give_meta_helpers_provider(){
		return array(
			array( Give_Helper_Payment::create_simple_payment() ),
			array( Give_Helper_Form::create_simple_form()->id ),
		);
	}

	/**
	 * Test for building Item Title of Payment Gateway.
	 *
	 * @since 1.8.14
	 * @access public
	 *
	 * @cover give_payment_gateway_item_title
	 */
	public function test_give_payment_gateway_item_title() {

		// Setup Simple Donation Form.
		$donation = Give_Helper_Form::setup_simple_donation_form();

		// Simple Donation Form using Payment Gateway Item Title.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form', $title );

		// Setup Simple Donation Form with Custom Amount.
		$donation = Give_Helper_Form::setup_simple_donation_form( true );

		// Simple Donation Form using Payment Gateway Item Title with Custom Amount.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form', $title );

		// Setup MultiLevel Donation Form.
		$donation = Give_Helper_Form::setup_multi_level_donation_form();

		// MultiLevel Donation Form using Payment Gateway Item Title.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form - Mid-size Gift', $title );

		// Setup MultiLevel Donation Form with Custom Amount.
		$donation = Give_Helper_Form::setup_multi_level_donation_form( true );

		// MultiLevel Donation Form using Payment Gateway Item Title with Custom Amount.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form', $title );

	}

	/**
	 * Check if current page/url is give's admin page or not.
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @cover give_is_admin_page
	 */
	public function test_give_is_admin_page() {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php';

		$GLOBALS['typenow'] = 'give_forms';
		$GLOBALS['pagenow'] = 'edit.php';

		// Donation form page, it should return true.
		$this->go_to( admin_url( 'edit.php?post_type=give_forms' ) );
		$this->assertTrue( give_is_admin_page() );

		// Setting pages.
		$this->go_to( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ) );
		$this->assertTrue( give_is_admin_page() );

		// Taxonomies page.
		$GLOBALS['pagenow'] = 'edit-tags.php';

		$this->go_to( admin_url( 'edit-tags.php?taxonomy=give_forms_category&post_type=give_forms' ) );
		$this->assertTrue( give_is_admin_page() );

		// Non-Give pages will not have this variable so, Unset.
		unset( $GLOBALS['typenow'] );

		// WP Plugin page.
		$GLOBALS['pagenow'] = 'plugins.php';
		$this->assertFalse( give_is_admin_page() ); // False.
		$this->assertFalse( give_is_admin_page( 'give_forms' ) ); // False.

		// Admin-ajax.
		$GLOBALS['pagenow'] = 'admin-ajax.php';
		$this->assertFalse( give_is_admin_page() );
	}
}
