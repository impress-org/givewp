<?php

/**
 * @group email_notification_filters
 */
class Tests_Email_Notification_Filters extends Give_Unit_Test_Case {
	public function setUp(): void {
		parent::setUp();
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

	/**
	 * @since TBD
	 *
	 * @return Give_Email_Notification
	 */
	private function get_donation_receipt_email_notification() {
		foreach ( Give_Email_Notifications::get_instance()->get_email_notifications() as $email ) {
			if ( 'donation-receipt' === $email->config['id'] ) {
				return $email;
			}
		}

		$this->fail( 'donation-receipt email notification not found.' );
	}

	/**
	 * Give_Email_Notifications::email_preview_header() is hooked to
	 * "give_{$email_type}_email_preview", which can also be dispatched directly via
	 * ?give_action=<email_type>_email_preview (see give_get_actions()). The callback must
	 * re-check Give_Email_Notification_Util::can_preview_email() itself so a direct dispatch
	 * never reaches give_get_preview_email_header() without the same capability + nonce that
	 * gate the normal preview_email() flow.
	 *
	 * @since TBD
	 * @cover Give_Email_Notifications::email_preview_header
	 */
	public function test_email_preview_header_denies_direct_dispatch_without_preview_email_action() {
		wp_set_current_user( 1 ); // Administrator — has manage_give_settings.

		// A direct ?give_action=<email_type>_email_preview dispatch never sets
		// $_GET['give_action'] to 'preview_email' — that value belongs to the legitimate,
		// separately-gated admin preview flow only.
		unset( $_GET['give_action'] );

		$email = $this->get_donation_receipt_email_notification();

		ob_start();
		Give_Email_Notifications::get_instance()->email_preview_header( $email );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	/**
	 * The legitimate admin preview flow (?give_action=preview_email, with the matching
	 * capability) must still render the header.
	 *
	 * @since TBD
	 * @cover Give_Email_Notifications::email_preview_header
	 */
	public function test_email_preview_header_renders_for_legitimate_preview_request() {
		wp_set_current_user( 1 ); // Administrator — has manage_give_settings.
		$_GET['give_action']    = 'preview_email';
		$request_uri_backup     = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI'] = '/?give_action=preview_email&email_type=donation-receipt';

		// give_get_preview_email_header() needs at least one *complete* (post_status=publish)
		// donation to render the "preview with a donation" selector — without one it returns
		// nothing, regardless of the capability gate this test is actually exercising.
		$payment_id = Give_Helper_Payment::create_simple_payment();
		give_update_payment_status( $payment_id, 'complete' );

		$email = $this->get_donation_receipt_email_notification();

		ob_start();
		Give_Email_Notifications::get_instance()->email_preview_header( $email );
		$output = ob_get_clean();

		unset( $_GET['give_action'] );
		if ( null === $request_uri_backup ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $request_uri_backup;
		}

		$this->assertNotSame( '', $output );
	}

	/**
	 * Even with the legitimate ?give_action=preview_email value present, a visitor without the
	 * manage_give_settings capability must not see the header (can_preview_email() checks both).
	 *
	 * @since TBD
	 * @cover Give_Email_Notifications::email_preview_header
	 */
	public function test_email_preview_header_denies_visitor_without_capability() {
		wp_set_current_user( 0 );
		$_GET['give_action'] = 'preview_email';

		$email = $this->get_donation_receipt_email_notification();

		ob_start();
		Give_Email_Notifications::get_instance()->email_preview_header( $email );
		$output = ob_get_clean();

		unset( $_GET['give_action'] );

		$this->assertSame( '', $output );
	}
}
