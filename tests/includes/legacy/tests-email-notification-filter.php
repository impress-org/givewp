<?php

/**
 * @group email_notification_filters
 */
class Tests_Email_Notification_Filters extends Give_Unit_Test_Case {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test give_decode_email_tags function
	 *
	 * @since 2.0
	 * @cover give_decode_email_tags
	 */
	public function test_give_decode_email_tags() {
		$message = 'Decode {donation}';
		$payment = Give_Helper_Payment::create_simple_payment();

		Give()->emails->tag_args = array( 'payment_id' => $payment );
		$message                 = give_decode_email_tags( $message, Give()->emails );
		Give()->emails->tag_args = array();

		$output = strpos( $message, '{donation}' );

		$this->assertFalse( $output );
	}
}
