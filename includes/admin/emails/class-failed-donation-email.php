<?php
/**
 * Failed Donation Email
 *
 * Donation Notification will be sent to recipient(s) when a donation status is changed to failed.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since 4.14.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Give\Donations\Models\Donation;

if ( ! class_exists( 'Give_Failed_Donation_Email' ) ) :

	/**
	 * @since 4.14.0
	 */
	class Give_Failed_Donation_Email extends Give_Email_Notification {

		/* @var Donation|null $donation */
		private $donation;

		/**
		 * @since 4.14.0
		 */
		public function init() {

			$this->load(
				array(
					'id'                    => 'failed-donation',
					'label'                 => __( 'Failed Donation', 'give' ),
					'description'           => __( 'Sent to designated recipient(s) when a donation status is changed to failed.', 'give' ),
					'has_recipient_field'   => true,
					'notification_status'   => 'enabled',
					'form_metabox_setting'  => true,
					'default_email_subject' => esc_attr__( 'Failed Donation - #{payment_id}', 'give' ),
					'default_email_message' => ( false !== give_get_option( 'failed-donation_email_message' ) ) ? give_get_option( 'failed-donation_email_message' ) : $this->get_default_email_message(),
					'default_email_header'  => __( 'Failed Donation!', 'give' ),
				)
			);

			add_action( "give_{$this->config['id']}_email_notification", array( $this, 'setup_email_notification' ) );
			add_action( 'give_update_payment_status', array( $this, 'maybe_send_email_notification' ), 10, 3 );
		}

		/**
		 * @since 4.14.0
         *
		 * @return string
		 */
		private function get_default_email_message() {
			$default_email_body  = __( 'Hi there,', 'give' ) . "\n\n";
			$default_email_body .= __( 'This email is to inform you that a donation has failed on your website:', 'give' ) . ' {site_url}' . ".\n\n";
			$default_email_body .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {name}' . "\n";
			$default_email_body .= '<strong>' . __( 'Donation:', 'give' ) . '</strong> {donation}' . "\n";
			$default_email_body .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n";
			$default_email_body .= '<strong>' . __( 'Payment Method:', 'give' ) . '</strong> {payment_method}' . "\n";
			$default_email_body .= '<strong>' . __( 'Payment ID:', 'give' ) . '</strong> {payment_id}' . "\n\n";
			$default_email_body .= __( 'Thank you,', 'give' ) . "\n\n";
			$default_email_body .= '{sitename}' . "\n";

			/**
			 * Filter the default failed donation notification email message.
			 *
			 * @since 4.14.0
			 *
			 * @param string $default_email_body Default email message.
			 */
			return apply_filters( 'give_default_failed_donation_notification_email', $default_email_body );
		}

		/**
		 * @since 4.14.0
		 *
		 * @param int    $payment_id The ID number of the payment.
		 * @param string $new_status The status of the payment.
		 * @param string $old_status The status of the payment prior to being changed.
		 *
		 * @return void
		 */
		public function maybe_send_email_notification( $payment_id, $new_status, $old_status ) {
			if ( $new_status === $old_status ) {
				return;
			}

			$donation = Donation::find( (int) $payment_id );
			if ( ! $donation || ! $donation->status->isFailed() ) {
				return;
			}

			if ( ! give_is_setting_enabled( $this->get_notification_status() ) ) {
				return;
			}

			/**
			 * Fires when a donation status is changed to failed.
			 *
			 * @param int $payment_id Payment ID.
			 *
			 * @since 4.14.0
			 */
			do_action( "give_{$this->config['id']}_email_notification", $payment_id );
		}

		/**
		 * @since 4.14.0
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_subject( $form_id = null ) {
			$subject = wp_strip_all_tags(
				Give_Email_Notification_Util::get_value(
					$this,
					Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'email_subject',
					$form_id,
					$this->config['default_email_subject']
				)
			);

			/**
			 * Filters the failed donation notification subject.
			 *
			 * @since 4.14.0
			 */
			$subject = apply_filters( "give_{$this->config['id']}_get_email_subject", $subject, $this, $form_id );

			return $subject;
		}


		/**
		 * @since 4.14.0
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = null ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				Give_Email_Setting_Field::get_prefix( $this, $form_id ) . 'email_message',
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the email message
			 *
			 * @since 4.14.0
			 */
			$message = apply_filters(
				"give_{$this->config['id']}_get_default_email_message",
				$message,
				$this,
				$form_id
			);

			return $message;
		}


		/**
		 * @since 4.14.0
		 *
		 * @param int $form_id
		 * @return array
		 */
		public function get_email_attachments( $form_id = null ) {
			/**
			 * Filters the failed donation notification email attachments.
			 * By default, there is no attachment but plugins can hook in to provide one more multiple.
			 *
			 * @since 4.14.0
			 */
			$attachments = apply_filters(
				"give_{$this->config['id']}_get_email_attachments",
				array(),
				$this,
				$form_id
			);

			return $attachments;
		}

		/**
		 * @since 4.14.0
		 */
		public function setup_email_data() {
			/**
			 * Filters the from name.
			 *
			 * @since 4.14.0
			 */
			$from_name = apply_filters(
				'give_failed_donation_from_name',
				Give()->emails->get_from_name(),
				$this->donation->id,
				null
			);

			/**
			 * Filters the from email.
			 *
			 * @since 4.14.0
			 */
			$from_email = apply_filters(
				'give_failed_donation_from_address',
				Give()->emails->get_from_address(),
				$this->donation->id,
				null
			);

			Give()->emails->__set( 'from_name', $from_name );
			Give()->emails->__set( 'from_email', $from_email );

			/**
			 * Filters the failed donation notification email headers.
			 *
			 * @since 4.14.0
			 */
			$headers = apply_filters(
				'give_failed_donation_notification_headers',
				Give()->emails->get_headers(),
				$this->donation->id,
				null
			);

			Give()->emails->__set( 'headers', $headers );
		}

		/**
		 * @since 4.14.0
         *
		 * @param int $payment_id
		 */
		public function setup_email_notification( $payment_id ) {
			// Get the Donation model.
			$this->donation = Donation::find( $payment_id );

			if ( ! $this->donation ) {
				wp_die(
					esc_html__( 'Cheatin&#8217; uh?', 'give' ),
					esc_html__( 'Error', 'give' ),
					array(
						'response' => 400,
					)
				);
			}

			// Set email data.
			$this->setup_email_data();

			// Send email.
			$this->send_email_notification(
				array(
					'payment_id' => $payment_id,
				)
			);
		}
	}

endif;

return Give_Failed_Donation_Email::get_instance();
