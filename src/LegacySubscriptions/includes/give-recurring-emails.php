<?php

use Give\Log\Log;

/**
 * Give Recurring Emails
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Emails
 */
class Give_Recurring_Emails {

	/**
	 * Give Subscription Object
	 *
	 * @var object Give_Subscription
	 * @since 1.0
	 */
	public $subscription;

	/**
	 * Give_Recurring_Emails constructor.
	 */
	public function __construct() {

		$this->init();
	}

	/**
	 * Initialize Give_Recurring_Emails
	 */
	public function init() {

		add_action( 'give_email_tags', array( $this, 'setup_email_tags' ) );
	}

	/**
	 * Send Reminder.
	 *
	 * Responsible for sending both `renewal` and `expiration` notices.
	 *
	 * @param string $reminder_type required - values of `expiration` or `renewal`.
	 * @param int $subscription_id required
	 * @param int $notice_id
	 */
	public function send_reminder( $reminder_type, $subscription_id = 0, $notice_id = 0 ) {

		$stored_notices = get_option( 'give_recurring_reminder_notices', array() );
		$content_type   = $stored_notices[ $notice_id ]['content_type'];

		// Sanity check: Do we have the required subscription ID?
		if ( empty( $subscription_id ) || empty( $reminder_type ) ) {
			return;
		}

		// Get subscription.
		$this->subscription = new Give_Subscription( $subscription_id );

		// Sanity check: Check for it
		if ( empty( $this->subscription ) ) {
			return;
		}

		// What type of reminder email is this? (renewal or expiration)
		$reminder = $reminder_type == 'renewal'
			? Give_Recurring_Renewal_Reminders::get_instance()
			: Give_Recurring_Expiration_Reminders::get_instance();

		// Sanity check: Are these reminder emails activated?
		if ( ! $reminder->reminders_allowed() ) {
			return;
		}

		$user = get_user_by( 'id', $this->subscription->donor->user_id );
		$send = (bool) apply_filters( 'give_recurring_send_' . $reminder_type . '_reminder', true, $subscription_id, $notice_id, $user );

		// Do not send email if revoked.
		if( ! $send ) {
			return;
		}

		$email_to = $this->subscription->donor->email;

		// Form appropriate email depending on reminder type.
		if ( $reminder_type == 'renewal' ) {
			// Renewing.
			$notice  = $reminder->get_renewal_notice( $notice_id );
			$message = ! empty( $notice['message'] ) ? $notice['message'] : __( "Hello {name},\n\nYour subscription for {subscription_name} will renew on {expiration}.", 'give-recurring' );
			$subject = ! empty( $notice['subject'] ) ? $notice['subject'] : __( 'Your Subscription is About to Renew', 'give-recurring' );

		} else {
			// Expiring.
			$notice  = $reminder->get_expiration_notice( $notice_id );
			$message = ! empty( $notice['message'] ) ? $notice['message'] : __( "Hello {name},\n\nYour subscription for {subscription_name} will expire on {expiration}.", 'give-recurring' );
			$subject = ! empty( $notice['subject'] ) ? $notice['subject'] : __( 'Your Subscription is About to Expire', 'give-recurring' );
		}

		// Filter template tags.
		Give()->emails->__set( 'heading', give_do_email_tags( $notice['header'], $this->subscription->parent_payment_id ) );
		$subject = $this->filter_template_tags( $subject, $this->subscription );
		$message = $this->filter_template_tags( $message, $this->subscription );
		$message = ( 'text/plain' === $content_type ) ? wp_strip_all_tags( $message ) : $message;

		$sent    = Give()->emails->send( $email_to, $subject, $message );

		// Log the email if it indeed sent.
		if ( $sent ) {
			$this->log_recurring_email( $reminder_type, $this->subscription, $subject, $notice );
		}

	}

	/**
	 * Log recurring email.
	 *
	 * When an email is sent by the plugin, log it with Give.
	 *
	 * @since 1.12.2 switch to new Log facade
	 *
	 * @param string $email_type
	 * @param Give_Subscription $subscription
	 * @param                   $subject string
	 * @param int $notice_id
	 * @param                   $notice  array of the email including subj, send period, etc. Used for reminder emails
	 */
	public static function log_recurring_email( $email_type, $subscription, $subject, $notice_id = 0, $notice = array() ) {
		// Dynamic log title based on $email_type
		$log_title = __( 'LOG - Subscription ' . ucfirst( $email_type ) . ' Email Sent', 'give-recurring' );

		Log::notice($log_title, [
			'category' => 'Recurring Donations',
			'source' => 'Email Logs',
			'Customer ID'     => $subscription->donor_id,
			'Subscription ID' => $subscription->id ,
			'Email Subject'   => $subject
		]);

		// Is there a notice ID for this email?
		if ( $notice_id > 0 && ! empty( $notice ) ) {
			// Prevent reminder notices from being sent more than once
			add_user_meta( $subscription->donor->user_id, sanitize_key( '_give_recurring_' . $email_type . '_' . $subscription->id . '_sent_' . $notice['send_period'] ), time() );
		}
	}

	/**
	 * Email reminder template tag.
	 *
	 * @deprecated Use $this->filter_email_tags()
	 *
	 * @param string $content
	 * @param Give_Subscription $subscription
	 *
	 * @return mixed|string
	 */
	public function filter_template_tags( $content = '', $subscription ) {

		$payment_id           = $subscription->parent_payment_id;
		$payment_meta         = give_get_payment_meta( $payment_id );
		$expiration_timestamp = strtotime( $subscription->expiration );
		$interval             = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;

		if ( isset( $this->tags ) && ! is_null( $this->tags ) && ! empty( $this->tags ) ) {

			foreach ( $this->tags as $email_tag ) {

				switch ( $email_tag ) :
					case 'renewal_link':
						$content = str_replace( '{renewal_link}', '<a href="' . get_permalink( $payment_meta['form_id'] ) . '" target="_blank"> ' . $payment_meta['form_title'] . '</a>', $content );
						break;
					case 'completion_date':
						$content = str_replace( '{completion_date}', date_i18n( give_date_format(), $expiration_timestamp ), $content );
						break;
					case 'subscription_frequency':
						$times = (int) $subscription->bill_times;
						$content = str_replace( '{subscription_frequency}', give_recurring_pretty_subscription_frequency( $subscription->period, $times, false, $interval
						), $content );
						break;
					case 'subscriptions_completed':
						$content = str_replace( '{subscriptions_completed}', $subscription->get_subscription_progress(), $content );
						break;
					case 'cancellation_date':
						$content = str_replace( '{cancellation_date}', date_i18n( give_date_format(), current_time( 'timestamp' ) ), $content );
						break;
				endswitch;

			}
		}

		// Filter email content through Give core as well.
		$content = give_do_email_tags( $content, $payment_id );

		return apply_filters( 'give_recurring_filter_template_tags', $content );

	}

	/**
	 * filter the email tag content.
	 *
	 * @access public
	 *
	 * @param array $tag_args
	 * @param string $email_tag
	 *
	 * @return string
	 */
	public static function filter_email_tags( $tag_args, $email_tag ) {

		$subscription_id = 0;
		if ( ! empty( $tag_args['subscription_id'] ) ) {
			$subscription_id = $tag_args['subscription_id'];
		} elseif ( ! empty( $tag_args['payment_id'] ) ) {
			$subscription_id = give_recurring_get_subscription_by( 'payment', $tag_args['payment_id'] );
		}

		// Return "n/a" for one-time (non-recurring) donations for all email tags besides frequency which will return "One Time" text.
		if ( empty( $subscription_id ) && 'subscription_frequency' !== $email_tag ) {
			return apply_filters( 'give_recurring_one_time_filter_template_tags', __( 'n/a', 'give-recurring' ) );
		}

		/* @var Give_Subscription $subscription */
		$subscription         = new Give_Subscription( $subscription_id );
		$payment_meta         = give_get_payment_meta( $subscription->parent_payment_id );
		$expiration_timestamp = strtotime( $subscription->expiration );
		$interval             = ! empty( $subscription->frequency ) ? $subscription->frequency : 1;
		$content              = '';

		// Replace template tags with actual content.
		switch ( $email_tag ) :
			case 'renewal_link':
				$content = str_replace(
					'<a href="%1$s" target="_blank">%2$s</a>',
					get_permalink( $payment_meta['form_id'] ),
					$payment_meta['form_title']
				);
				break;

			case 'completion_date':
				$content = date_i18n( give_date_format(), $expiration_timestamp );
				break;

			case 'subscription_frequency':
				$times = (int) $subscription->bill_times;
				$content = give_recurring_pretty_subscription_frequency( $subscription->period, $times, false, $interval );
				break;

			case 'subscriptions_completed':
				$content = $subscription->get_subscription_progress();
				break;

			case 'cancellation_date':
				$content = date_i18n( give_date_format(), current_time( 'timestamp' ) );
				break;

			case 'renewal_date':
			case 'expiration_date':
				$content = date( give_date_format(), strtotime( $subscription->expiration ) );
				break;
		endswitch;

		return apply_filters( 'give_recurring_filter_template_tags', $content, $tag_args, $email_tag );
	}

	/**
	 * This function is used to setup new email tags for recurring add-on.
	 *
	 * @param array $email_tags List of email tags.
	 *
	 * @since  1.8.5
	 * @access public
	 *
	 * @return array
	 */
	public function setup_email_tags( $email_tags ) {

		$email_tags[] = array(
			'tag'     => 'subscriptions_link',
			'desc'    => esc_html__( 'The donor\'s email access link for subscription history.', 'give-recurring' ),
			'func'    => array( $this, 'email_tag_subscription_history_link' ),
			'context' => 'donor',
		);

		$email_tags[] = array(
			'tag'     => 'next_payment_attempt',
			'desc'    => esc_html__( 'The date and time when the next payment attempt will be made.', 'give-recurring' ),
			'func'    => array( $this, 'email_tag_next_payment_attempt_date' ),
			'context' => 'donor',
		);

		$email_tags[] = array(
			'tag'     => 'update_payment_method_link',
			'desc'    => esc_html__( 'The link to update the payment method of the subscription.', 'give-recurring' ),
			'func'    => array( $this, 'email_tag_update_payment_method_link' ),
			'context' => 'donor',
		);

		return $email_tags;
	}

	/**
	 * Email template tag: {subscriptions_link}
	 *
	 * @since  1.8.5
	 * @access public
	 *
	 * @param array $tag_args Email Tag Arguments.
	 *
	 * @return string
	 */
	public function email_tag_subscription_history_link( $tag_args ) {

		$donor_id = 0;
		$donor    = array();
		$link     = '';

		// Backward compatibility.
		$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

		switch ( true ) {

			case ! empty( $tag_args['payment_id'] ):
				$donor_id = Give()->payment_meta->get_meta( $tag_args['payment_id'], '_give_payment_donor_id', true );
				$donor    = Give()->donors->get_by( 'id', $donor_id );
				break;

			case ! empty( $tag_args['donor_id'] ):
				$donor_id = $tag_args['donor_id'];
				$donor    = Give()->donors->get_by( 'id', $tag_args['donor_id'] );
				break;

			case ! empty( $tag_args['user_id'] ):
				$donor    = Give()->donors->get_by( 'user_id', $tag_args['user_id'] );
				$donor_id = $donor->id;
				break;
		}

		// Set email access link if donor exist.
		if ( $donor_id ) {
			$verify_key = wp_generate_password( 20, false );

			// Generate a new verify key.
			Give()->email_access->set_verify_key( $donor_id, $donor->email, $verify_key );

			// update verify key in email tags.
			$tag_args['verify_key'] = $verify_key;

			// update donor id in email tags.
			$tag_args['donor_id'] = $donor_id;

			$access_url = add_query_arg(
				array(
					'give_nl' => $verify_key,
				),
				give_get_subscriptions_page_uri()
			);

			// Add donation id to email access url, if it exists.
			$donation_id = give_clean( filter_input( INPUT_GET, 'donation_id' ) );
			if ( ! empty( $donation_id ) ) {
				$access_url = add_query_arg(
					array(
						'donation_id' => $donation_id,
					),
					$access_url
				);
			}

			if ( empty( $tag_args['email_content_type'] ) || 'text/html' === $tag_args['email_content_type'] ) {
				$link = sprintf(
					'<a href="%1$s" target="_blank">%2$s</a>',
					esc_url( $access_url ),
					__( 'View your subscription history &raquo;', 'give-recurring' )
				);

			} else {

				$link = sprintf(
					'%1$s: %2$s',
					__( 'View your subscription history', 'give-recurring' ),
					esc_url( $access_url )
				);
			}
		} // End if().

		/**
		 * Filter the {subscriptions_link} email template tag output.
		 *
		 * @since 1.8.5
		 *
		 * @param string $receipt_link_url
		 * @param array  $tag_args
		 */
		return apply_filters(
			'give_recurring_email_tag_subscription_history_link',
			$link,
			$tag_args
		);
	}

	/**
	 * Email tag for next payment attempt.
	 *
	 * @param array $tag_args List of Email Tag arguments.
	 *
	 * @since  1.9.0
	 * @access public
	 *
	 * @return string
	 */
	public function email_tag_next_payment_attempt_date( $tag_args ) {

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$date_time_format = "{$date_format} {$time_format}";
		$next_payment_attempt = ! empty( $tag_args['next_payment_attempt'] ) ? date_i18n( $date_time_format, $tag_args['next_payment_attempt'] ) : false;

		/**
		 * Filter for next payment attempt date.
		 *
		 * @param string $next_payment_attempt Next Payment Attempt Date.
		 *
		 * @since 1.9.0
		 */
		return apply_filters( 'give_recurring_email_tag_next_payment_attempt_date', $next_payment_attempt );
	}

	/**
	 * Email tag containing the link to update the payment method of an active subscription.
	 *
	 * @param array $tag_args List of email tag arguments.
	 *
	 * @since  1.9.0
	 * @access public
	 *
	 * @return mixed
	 */
	public function email_tag_update_payment_method_link( $tag_args ) {

		$subscription_id = ! empty( $tag_args['subscription_id'] ) ? $tag_args['subscription_id'] : 0;

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			add_query_arg(
				array(
					'action'          => 'update',
					'subscription_id' => $subscription_id,
				),
				give_get_subscriptions_page_uri()
			),
			__( 'Update Payment Method', 'give-recurring' )
		);
	}
}
