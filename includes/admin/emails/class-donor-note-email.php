<?php
/**
 * Donor Note Email
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Donor_Note_Email' ) ) :

	/**
	 * Give_Donor_Note_Email
	 *
	 * @abstract
	 * @since       2.3.0
	 */
	class Give_Donor_Note_Email extends Give_Email_Notification {
		/* @var Give_Payment $payment */
		public $payment;

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.3.0
		 */
		public function init() {
			// Initialize empty payment.
			$this->payment = new Give_Payment( 0 );

			$this->load(
				array(
					'id'                    => 'donor-note',
					'label'                 => __( 'Donation Note', 'give' ),
					'description'           => __( 'Sent when a donation note is added to a donation payment.', 'give' ),
					'notification_status'   => 'enabled',
					'recipient_group_name'  => __( 'Donor', 'give' ),
					'default_email_subject' => sprintf(
						esc_attr__( 'Note added to your %1$s donation from %2$s', 'give' ),
						'{donation}',
						'{date}'
					),
					'default_email_message' => sprintf(
						"Dear %s,\n\nA note has just been added to your donation:\n\n%s\n\nFor your reference, you may view your donation details by clicking the link below:\n%s\n\nThank you,\n%s",
						'{name}',
						'{donor_note}',
						'{receipt_link}',
						'{sitename}'
					),
					'default_email_header'  => __( 'New Donation Note Added', 'give' ),
					'form_metabox_setting'  => false,
				)
			);

			add_action( "give_{$this->config['id']}_email_notification", array( $this, 'send_note' ), 10, 2 );
		}

		/**
		 * Send donor note
		 *
		 * @since  2.3.0
		 * @access public
		 *
		 * @param int $donation_id Donation ID.
		 * @param int $note_id     Donor comment.
		 */
		public function send_note( $note_id, $donation_id ) {
			if ( ! $note_id || ! $donation_id ) {
				wp_die(
					esc_html__( 'Cheatin&#8217; uh?', 'give' ),
					esc_html__( 'Error', 'give' ),
					array(
						'response' => 400,
					)
				);
			}

			$this->recipient_email = give_get_donation_donor_email( $donation_id );

			// Send email.
			$this->send_email_notification(
				array(
					'payment_id' => $donation_id,
					'note_id'    => $note_id,
				)
			);
		}
	}

endif; // End class_exists check

return Give_Donor_Note_Email::get_instance();
