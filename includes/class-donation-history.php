<?php
/**
 * Email Access
 *
 * @package     Give
 * @subpackage  Classes/Give_Donation_History
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.17
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donation_History class
 *
 * This class handles email access, allowing non login donors access to their donation history w/o logging in;
 *
 * @since 1.8.17
 */
class Give_Donation_History {

	/**
	 * Class Constructor
	 *
	 * Set up the Give Email Access Class.
	 *
	 * @since  1.8.17
	 * @access public
	 */
	public function __construct() {

		// get it started
		add_action( 'wp_ajax_nopriv_give_donation_history_send_confirmation', array( $this, 'donation_history_send_confirmation' ) );
	}

	public function donation_history_send_confirmation() {
		$this->send_confirmation_mail();

		$response = array(
			'success' => true,
			'text' => __( 'Email send', 'give' ),
			'success_message' => __( 'Please check your email and click on the link to access your complete donation history', 'give' ),
		);
		wp_send_json( $response );
	}

	public function send_confirmation_mail( $customer_id = 17, $email = 'raftaar1191@gmail.com' ) {
		$home       = get_option( 'home' );
		$name       = get_bloginfo( 'name' );
		$verify_key = wp_generate_password( 20, false );

		// Generate a new verify key
		Give()->email_access->set_verify_key( $customer_id, $email, $verify_key );

		// Get the donation history page
		$page_id = give_get_option( 'history_page' );

		$access_url = add_query_arg( array(
			'give_nl' => $verify_key,
		), get_permalink( $page_id ) );

		// Nice subject and message.
		$subject = apply_filters( 'give_email_access_token_subject1', sprintf( __( 'Please confirm your email for %s', 'give' ), $home ) );

		$message = __( 'Dear Name,', 'give' ) . "\n";
		$message .= sprintf( __( 'Please click the link below to access you donation history on %s. If you did not request this email please contact admin@email.com.', 'give' ), $home ) . "\n\n";

		$message .= "\n\n";
		$message .= '<a href="' . esc_url( $access_url ) . '" target="_blank">' . __( 'Click here to view donation history &raquo;', 'give' ) . '</a>' . "\n\n";
		$message .= "\n\n";
		$message .= __( 'Sincerely,', 'give' ) . "\n";
		$message .= $name . "\n";

		$message = apply_filters( 'give_email_access_token_message1', $message );

		// Send the email.
		Give()->emails->__set( 'heading', apply_filters( 'give_email_access_token_heading1', __( 'Confirm Email', 'give' ) ) );
		Give()->emails->send( $email, $subject, $message );
	}
}
new Give_Donation_History();