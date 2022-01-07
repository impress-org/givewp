<?php
/**
 * Give Recurring Expiration Reminders
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

class Give_Recurring_Expiration_Reminders{
	/**
	 * Instance.
	 *
	 * @since  1.7
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.7
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.7
	 * @access public
	 * @return Give_Recurring_Expiration_Reminders
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->setup();
		}

		return self::$instance;
	}


	/**
	 * Setup
	 *
	 * @since 1.7
	 * @access private
	 */
	private function setup(){
		add_action( 'give_daily_scheduled_events', array( $this, 'scheduled_expiration_reminders' ) );
	}

	/**
	 * Returns if expirations are enabled
	 *
	 * @return bool True if enabled, false if not
	 */
	public function reminders_allowed() {

		$expiration_reminder = give_get_option('recurring_send_expiration_reminders');

		return ( 'enabled' === $expiration_reminder ) ? true : false;
	}

	/**
	 * Retrieve expiration notices
	 *
	 * @return array Renewal notice periods
	 */
	public function get_expiration_notice_periods() {
		$periods = array(
			'+1day'    => __( 'One day before expiration', 'give-recurring' ),
			'+2days'   => __( 'Two days before expiration', 'give-recurring' ),
			'+3days'   => __( 'Three days before expiration', 'give-recurring' ),
			'+1week'   => __( 'One week before expiration', 'give-recurring' ),
			'+2weeks'  => __( 'Two weeks before expiration', 'give-recurring' ),
			'+1month'  => __( 'One month before expiration', 'give-recurring' ),
			'+2months' => __( 'Two months before expiration', 'give-recurring' ),
			'+3months' => __( 'Three months before expiration', 'give-recurring' ),
			'expired'  => __( 'At the time of expiration', 'give-recurring' ),
			'-1day'    => __( 'One day after expiration', 'give-recurring' ),
			'-2days'   => __( 'Two days after expiration', 'give-recurring' ),
			'-3days'   => __( 'Three days after expiration', 'give-recurring' ),
			'-1week'   => __( 'One week after expiration', 'give-recurring' ),
			'-2weeks'  => __( 'Two weeks after expiration', 'give-recurring' ),
			'-1month'  => __( 'One month after expiration', 'give-recurring' ),
			'-2months' => __( 'Two months after expiration', 'give-recurring' ),
			'-3months' => __( 'Three months after expiration', 'give-recurring' ),
		);

		return apply_filters( 'get_expiration_notice_periods', $periods );
	}

	/**
	 * Retrieve the expiration label for a notice
	 *
	 * @param int $notice_id
	 *
	 * @return string
	 */
	public function get_expiration_notice_period_label( $notice_id = 0 ) {

		$notice  = $this->get_expiration_notice( $notice_id );
		$periods = $this->get_expiration_notice_periods();
		$label   = $periods[ $notice['send_period'] ];

		return apply_filters( 'get_expiration_notice_period_label', $label, $notice_id );
	}

	/**
	 * Retrieve a expiration notice
	 *
	 * @param int $notice_id
	 *
	 * @return array|mixed|void Renewal notice details
	 */
	public function get_expiration_notice( $notice_id = 0 ) {

		$notices = $this->get_expiration_notices();

		$defaults = array(
			'subject'     => __( 'Your Subscription is About to Expire', 'give-recurring' ),
			'send_period' => '+1day',
			'message'     => 'Hello {name},

			Your subscription for {subscription_name} will expire on {expiration}.

			Click here to renew: {renewal_link}'

		);

		$notice = isset( $notices[ $notice_id ] ) ? $notices[ $notice_id ] : $notices[0];

		$notice = wp_parse_args( $notice, $defaults );

		return apply_filters( 'give_recurring_expiration_notice', $notice, $notice_id );

	}

	/**
	 * Retrieve expiration notice periods
	 *
	 * @return array Renewal notices defined in settings
	 */
	public function get_expiration_notices() {
		$notices = get_option( 'give_recurring_reminder_notices', array() );

		if ( empty( $notices ) ) {

			$message = 'Hello {name},

	Your subscription for {subscription_name} will expire on {expiration}.

	Click here to renew: {renewal_link}';

			$notices[0] = array(
				'send_period' => '+1day',
				'subject'     => __( 'Your Subscription is About to Expire', 'give-recurring' ),
				'message'     => $message
			);

		}

		return apply_filters( 'get_expiration_notices', $notices );
	}


	/**
	 * This is the actual process that takes place when the CRON job
	 * schedules renewal reminders.
	 *
	 * @param array                 $notices               The email notices array.
	 * @param Give_Recurring_Emails $give_recurring_emails Give_Recurring_Email object.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function reminder_process( $notices, $give_recurring_emails ) {

		/**
		 * This loop will loop through all the notices, both
		 * renewal and expiration.
		 */
		foreach ( $notices as $notice_id => $notice ) {

			/**
			 * If notice email is disabled, then continue.
			 */
			if ( 'disabled' === $notice['status'] ) {
				continue;
			}


			/**
			 * This file is responsible for sending out emails for expirations,
			 * so we skip if the 'notice_type' is set to 'renewal'.
			 */
			if ( 'renewal' === $notice['notice_type'] ) {
				continue;
			}


			/**
			 * Get all the subscriptions which will be renewed in the next
			 * `$notice['send_period']` time.
			 *
			 * This can be -1day, +1day, -1month, +1month, +2month, etc.
			 */
			$subscriptions = $this->get_expiring_subscriptions( $notice['send_period'] );


			/**
			 * If there are no such subscriptions, then check for the next
			 * notice.
			 */
			if ( ! $subscriptions ) {
				continue;
			}


			foreach ( $subscriptions as $subscription ) {

				/**
				 * Get the payment ID of the parent payment.
				 */
				$parent_payment_id = $subscription->parent_payment_id;


				/**
				 * Get the gateway of the payment.
				 */
				$gateway = give_get_payment_meta( $parent_payment_id, '_give_payment_gateway' );


				/**
				 * Don't send the email if the gateway is found
				 * in the exclusion list.
				 */
				if ( ! empty( $notice['gateway'] ) && in_array( $gateway, $notice['gateway'], true ) ) {
					continue;
				}


				/* Translate each subscription into a user_id and utilize
				 * the usermeta to store last renewal sent.
				 */
				$give_subscription = new Give_Subscription( $subscription->id );

				$sent_time = get_user_meta( $give_subscription->donor->user_id, sanitize_key( '_give_recurring_expiration_' . $subscription->id . '_sent_' . $notice['send_period'] ), true );

				if ( $sent_time ) {

					$renew_date = strtotime( $notice['send_period'], $sent_time );

					if ( time() < $renew_date ) {
						continue;
					}

					delete_user_meta( $give_subscription->donor->user_id, sanitize_key( '_give_recurring_expiration_' . $subscription->id . '_sent_' . $notice['send_period'] ) );

				}

				$give_recurring_emails->send_reminder( 'expiration', $subscription->id, $notice_id );
			}
		}
	}


	/**
	 * Send reminder emails
	 *
	 * @return void
	 */
	public function scheduled_expiration_reminders() {

		if ( ! $this->reminders_allowed() ) {
			return;
		}


		/**
		 * This will be used to setup the sending of the email.
		 */
		$give_recurring_emails = new Give_Recurring_Emails;


		/**
		 * Gets all the recurring email notices.
		 * This includes email notices for both
		 * renewal and expiration.
		 */
		$notices = $this->get_expiration_notices();


		/**
		 * This handles sending out emails to donors.
		 */
		$this->reminder_process( $notices, $give_recurring_emails );
	}


	/**
	 * Retrieve expiration notice periods
	 *
	 * @param string $period
	 *
	 * @return array|bool|mixed|null|object  Subscribers whose subscriptions are expiring within the defined period
	 */
	public function get_expiring_subscriptions( $period = '+1month' ) {

		$subs_db       = new Give_Subscriptions_DB();
		$subscriptions = $subs_db->get_expiring_subscriptions( $period );

		if ( ! empty( $subscriptions ) ) {
			return $subscriptions;
		}

		return false;
	}

	/**
	 * Retrieve renewal notice periods
	 *
	 * @return array Renewal notices defined in settings
	 */
	public function get_renewal_notices() {
		$notices = get_option( 'give_recurring_reminder_notices', array() );

		if ( empty( $notices ) ) {

			$message = 'Hello {name},

	Your subscription for {subscription_name} will renew on {expiration}.';

			$notices[0] = array(
				'send_period' => '+1day',
				'subject'     => __( 'Your Subscription is About to Renew', 'give-recurring' ),
				'message'     => $message,
			);

		}

		return apply_filters( 'get_renewal_notices', $notices );
	}
}

Give_Recurring_Expiration_Reminders::get_instance();