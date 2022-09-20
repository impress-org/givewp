<?php

/**
 * Class Tests_Emails
 */
class Tests_Emails extends Give_Unit_Test_Case {

	/**
	 * @var object Give_Email_Template_Tags
	 */
	protected $_tags;

	/**
	 * @var string Donation payment ID.
	 */
	protected $_payment_id;

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();
		$this->_tags       = new Give_Email_Template_Tags();
		$this->_payment_id = Give_Helper_Payment::create_simple_payment();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test that each of the actions are added and each hooked in with the right priority
	 */
	public function test_email_actions() {
		global $wp_filter;

		$email_functions = array(
			array(
				'hook'     => 'give_admin_donation_email',
				'callback' => 'give_admin_email_notice',
			),
			array(
				'hook'     => 'give_complete_donation',
				'callback' => 'give_trigger_donation_receipt',
				'priority' => 999,
			),
			array(
				'hook'     => 'give_email_links',
				'callback' => 'resend_donation_receipt',
			),
			array(
				'hook'     => 'init',
				'callback' => 'send_preview_email',
			),
			array(
				'hook'     => 'init',
				'callback' => 'preview_email',
			),
		);

		foreach ( $email_functions as $email_function ) {
			$priority    = ! empty( $email_function['priority'] ) ? $email_function['priority'] : 10;
			$add_filters = array_keys( $wp_filter[ $email_function['hook'] ][ $priority ] );

			foreach ( $add_filters as $index => $filter ) {
				if ( false === strpos( $filter, $email_function['callback'] ) ) {
					unset( $add_filters[ $index ] );
				}
			}

			$add_filters = array_values( $add_filters );

			$this->assertTrue( ! empty( $add_filters ) );
			$this->assertTrue( false !== strpos( $add_filters[0], $email_function['callback'] ) );
		}
	}

	/**
	 * Test email notices.
	 */
	public function test_admin_notice_emails() {
		$expected = array( 'admin@example.org' );
		$this->assertEquals( $expected, give_get_admin_notice_emails() );
	}

	/**
	 * Test admin notice is disabled.
	 */
	public function test_admin_notice_disabled() {
		$this->assertFalse( give_admin_notices_disabled() );
	}

	public function test_email_templates() {
		$expected = array(
			'default' => 'Default Template',
			'none'    => 'No template, plain text only',
		);

		$this->assertEquals( $expected, give_get_email_templates() );
	}

	/**
	 * Test get template.
	 */
	public function test_get_template() {

		$this->assertEquals( 'default', Give()->emails->get_template() );
	}

	/**
	 * Test default message.
	 */
	public function test_give_get_default_donation_notification_email() {
		$this->assertContains( 'Hi there', give_get_default_donation_notification_email() );
		$this->assertContains( 'This email is to inform you that a new donation has been made on your website', give_get_default_donation_notification_email() );
		$this->assertContains( 'Donor:', give_get_default_donation_notification_email() );
		$this->assertContains( '{name}', give_get_default_donation_notification_email() );
		$this->assertContains( 'Donation:', give_get_default_donation_notification_email() );
		$this->assertContains( '{donation}', give_get_default_donation_notification_email() );
		$this->assertContains( 'Amount:', give_get_default_donation_notification_email() );
		$this->assertContains( '{amount}', give_get_default_donation_notification_email() );
		$this->assertContains( 'Payment Method:', give_get_default_donation_notification_email() );
		$this->assertContains( '{payment_method}', give_get_default_donation_notification_email() );
		$this->assertContains( 'Thank you,', give_get_default_donation_notification_email() );
		$this->assertContains( '{sitename}', give_get_default_donation_notification_email() );

	}

	/**
	 * Test getting email tags.
	 */
	public function test_email_tags_get_tags() {

		// Should be array type.
		$this->assertInternalType( 'array', give_get_email_tags() );

		// Ensure default tags are in the array.
		$this->assertarrayHasKey( 'donation', give_get_email_tags() );
		$this->assertarrayHasKey( 'form_title', give_get_email_tags() );
		$this->assertarrayHasKey( 'amount', give_get_email_tags() );
		$this->assertarrayHasKey( 'name', give_get_email_tags() );
		$this->assertarrayHasKey( 'fullname', give_get_email_tags() );
		$this->assertarrayHasKey( 'username', give_get_email_tags() );
		$this->assertarrayHasKey( 'user_email', give_get_email_tags() );
		$this->assertarrayHasKey( 'billing_address', give_get_email_tags() );
		$this->assertarrayHasKey( 'date', give_get_email_tags() );
		$this->assertarrayHasKey( 'payment_id', give_get_email_tags() );
		$this->assertarrayHasKey( 'payment_method', give_get_email_tags() );
		$this->assertarrayHasKey( 'sitename', give_get_email_tags() );
		$this->assertarrayHasKey( 'receipt_link', give_get_email_tags() );
		$this->assertarrayHasKey( 'receipt_link_url', give_get_email_tags() );
	}

	/**
	 * Test adding a tag.
	 */
	public function test_email_tags_add() {
		give_add_email_tag( 'test_tag', 'A test tag for the unit test', '__return_empty_array' );
		$this->assertTrue( give_email_tag_exists( 'test_tag' ) );
	}

	/**
	 * Test removing a tag.
	 */
	public function test_email_tags_remove() {
		give_remove_email_tag( 'test_tag' );
		$this->assertFalse( give_email_tag_exists( 'test_tag' ) );
	}

	/**
	 * Test {name} first name email tag.
	 */
	public function test_email_tags_first_name() {
		$this->assertEquals( 'Admin', give_email_tag_first_name( $this->_payment_id ) );
	}

	/**
	 * Test {fullname} email tag.
	 */
	public function test_email_tags_fullname() {
		$this->assertEquals( 'Admin User', give_email_tag_fullname( $this->_payment_id ) );
	}

	/**
	 * Test {username} email tag.
	 */
	public function test_email_tags_username() {
		$this->assertEquals( 'admin', give_email_tag_username( $this->_payment_id ) );
	}

	/**
	 * Test {user_email} tags
	 */
	public function test_email_tags_email() {
		$this->assertEquals( 'admin@example.org', give_email_tag_user_email( $this->_payment_id ) );
	}

	/**
	 * Test {date} email tag.
	 */
	public function test_email_tags_date() {
		$this->assertEquals( date( 'F j, Y', strtotime( get_post_field( 'post_date', $this->_payment_id ) ) ), give_email_tag_date( $this->_payment_id ) );
	}

	/**
	 * Test {amount} email tag.
	 */
	public function test_email_tags_amount() {
		// Actual output without html decode is &#36;&#x200e;20.00.
		$this->assertEquals( '$20.00', give_email_tag_price( $this->_payment_id ) );
	}

	/**
	 * Test {payment_id} email tag.
	 */
	public function test_email_tags_payment_id() {
		give_update_option( 'sequential-ordering_status', 'disabled' );
		$this->assertEquals( Give()->seq_donation_number->get_serial_number( $this->_payment_id ), give_email_tag_payment_id( $this->_payment_id ) );
		give_update_option( 'sequential-ordering_status', 'enabled' );
	}

	/**
	 * Test {payment_method} email tag.
	 */
	public function test_email_tags_payment_method() {
		$this->assertEquals( 'Test Donation', give_email_tag_payment_method( $this->_payment_id ) );
	}

	/**
	 * Test {sitename} email tag.
	 */
	public function test_email_tags_site_name() {
		$this->assertEquals( get_bloginfo( 'name' ), give_email_tag_sitename( $this->_payment_id ) );
	}

	/**
	 * Test {receipt_link} email tag.
	 */
	public function test_email_tags_receipt_link() {

		$receipt_link = give_get_view_receipt_link( $this->_payment_id );

		$this->assertContains( $receipt_link, give_email_tag_receipt_link( $this->_payment_id ) );
	}

	/**
	 * Test {receipt_link_url} email tag.
	 */
	public function test_email_tags_receipt_link_url() {

		$receipt_url = give_get_view_receipt_url( $this->_payment_id );

		$this->assertContains( $receipt_url, give_email_tag_receipt_link( $this->_payment_id ) );
	}

	/**
	 * Test the email from name.
	 */
	public function test_get_from_name() {
		$this->assertEquals( get_bloginfo( 'name' ), Give()->emails->get_from_name() );
	}

	/**
	 * Test from address.
	 */
	public function test_get_from_address() {
		$this->assertEquals( get_bloginfo( 'admin_email' ), Give()->emails->get_from_address() );
	}

	/**
	 * Test invalid emails address fallback.
	 */
	public function test_fallback_for_invalid_from_address() {

		give_update_option( 'from_email', 'not-an-email' );

		$this->assertEquals( get_bloginfo( 'admin_email' ), Give()->emails->get_from_address() );
	}

	/**
	 * Test get content type.
	 */
	public function test_get_content_type() {
		$this->assertEquals( 'text/html', Give()->emails->get_content_type() );

		Give()->emails->content_type = 'text/plain';

		$this->assertEquals( 'text/plain', Give()->emails->get_content_type() );

	}

	/**
	 * Test get headers.
	 */
	public function test_get_headers() {

		$from_name    = Give()->emails->get_from_name();
		$from_address = Give()->emails->get_from_address();
		$this->assertContains( "From: {$from_name} <{$from_address}>", Give()->emails->get_headers() );

	}

	/**
	 * Test get email heading.
	 */
	public function test_get_heading() {

		Give()->emails->__set( 'heading', 'Donation Receipt' );

		$this->assertEquals( 'Donation Receipt', Give()->emails->get_heading() );
	}

	public function test_text_to_html() {

		$message  = "Hello, this is plain text that I am going to convert to HTML\r\n";
		$message .= "Line breaks should become BR tags.\r\n";

		$expected = wpautop( $message );

		$emails               = Give()->emails;
		$emails->content_type = 'text/html';
		$message              = $emails->text_to_html( $message, Give()->emails );

		$this->assertEquals( $expected, $message );
	}

}
