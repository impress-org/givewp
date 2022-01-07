<?php
/**
 * Give Recurring Subscriber
 *
 * @since       1.0
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @package     Give
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Subscriber
 *
 * Includes methods for setting users as donors, setting their status, expiration, etc.
 */
class Give_Recurring_Subscriber extends Give_Donor {

	/**
	 * Subscriber DB
	 *
	 * @var Give_Subscriptions_DB
	 */
	private $subs_db;

	/**
	 * Give_Recurring_Subscriber constructor.
	 *
	 * @param bool $_id_or_email
	 * @param bool $by_user_id
	 */
	function __construct( $_id_or_email = false, $by_user_id = false ) {
		parent::__construct( $_id_or_email, $by_user_id );
		$this->subs_db = new Give_Subscriptions_DB();
	}

	/**
	 * Has donation form subscription.
	 *
	 * @param int $form_id
	 *
	 * @return bool
	 */
	public function has_subscription( $form_id = 0 ) {

		// Check all subscriptions by default.
		if ( empty( $form_id ) ) {
			$subs = $this->get_subscriptions();
		} else {
			// If filtering by form.
			$subs = $this->get_subscriptions( $form_id );
		}

		$ret = ! empty( $subs );

		return apply_filters( 'give_recurring_has_subscription', $ret, $form_id, $this );
	}

	/**
	 * Has active subscription.
	 *
	 * @param int $form_id Optional for filtering by form ID.
	 *
	 * @return bool
	 */
	public function has_active_subscription( $form_id = 0 ) {

		$ret = false;

		// Check all subscriptions by default.
		if ( empty( $form_id ) ) {
			$subs = $this->get_subscriptions();
		} else {
			// If filtering by form.
			$subs = $this->get_subscriptions( $form_id );
		}

		if ( $subs ) {
			foreach ( $subs as $sub ) {
				if ( $sub->is_active() ) {
					$ret = true;
				}

			}
		}

		return apply_filters( 'give_recurring_has_active_subscription', $ret, $this );

	}

	/**
	 * Add Subscription.
	 *
	 * @param array $args
	 *
	 * @return bool|object Give_Subscription
	 */
	public function add_subscription( $args = [] ) {

		$args = wp_parse_args( $args, $this->subs_db->get_column_defaults() );

		if ( empty( $args['form_id'] ) ) {
			return false;
		}

		if ( ! empty( $this->user_id ) ) {
			$this->set_as_subscriber();
		}

		$args['donor_id'] = $this->id;

		$subscription = new Give_Subscription();

		return $subscription->create( $args );

	}

	/**
	 * Add Payment
	 *
	 * @since 1.0
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function add_payment( $args = [] ) {

		$args = wp_parse_args( $args, [
			'subscription_id' => 0,
			'amount'          => '0.00',
			'transaction_id'  => '',
		] );

		if ( empty( $args['subscription_id'] ) ) {
			return false;
		}

		$subscription = new Give_Subscription( $args['subscription_id'] );

		if ( empty( $subscription ) ) {
			return false;
		}

		unset( $args['subscription_id'] );

		return $subscription->add_payment( $args );

	}

	/**
	 * Get Subscription
	 *
	 * @param int $subscription_id
	 *
	 * @return object|bool
	 */
	public function get_subscription( $subscription_id = 0 ) {

		$sub = new Give_Subscription( $subscription_id );

		if ( (int) $sub->donor_id !== (int) $this->id ) {
			return false;
		}

		return $sub;
	}

	/**
	 * Get Subscription by Profile ID
	 *
	 * @param string $profile_id
	 *
	 * @return bool|Give_Subscription
	 */
	public function get_subscription_by_profile_id( $profile_id = '' ) {

		if ( empty( $profile_id ) ) {
			return false;
		}

		$sub = new Give_Subscription( $profile_id, true );

		if ( (int) $sub->donor_id !== (int) $this->id ) {
			return false;
		}

		return $sub;

	}

	/**
	 * Get Subscriptions.
	 *
	 * Retrieves an array of subscriptions for a the donor.
	 * Optional form ID and status(es) can be supplied.
	 *
	 * @param int   $form_id
	 * @param array $args Array of arguments
	 *
	 * @return Give_Subscription[]
	 */
	public function get_subscriptions( $form_id = 0, $args = [] ) {

		if ( ! $this->id > 0 ) {
			return [];
		}

		// Backward compatibility
		if ( ! empty( $args ) && is_numeric( current( array_keys( $args ) ) ) ) {
			$args = [
				'status' => $args,
			];
		}

		$args = wp_parse_args(
			$args,
			[
				'number' => - 1,
				'status' => give_recurring_get_subscription_statuses_key(),
			]
		);

		if ( ! empty( $form_id ) ) {
			$args['form_id'] = $form_id;
		}

		$args['donor_id'] = $this->id;

		return $this->subs_db->get_subscriptions( $args );
	}

	/**
	 * Set as Subscriber
	 *
	 * Set a user as a subscriber
	 *
	 * @return void
	 */
	public function set_as_subscriber() {

		$user = new WP_User( $this->user_id );

		if ( $user ) {
			$user->add_role( 'give_subscriber' );
			do_action( 'give_recurring_set_as_subscriber', $this->user_id );
		}

	}

	/**
	 * Get New Expiration
	 *
	 * Calculate a new expiration date
	 *
	 * @param int    $form_id Donation Form ID.
	 * @param null   $price_id Price ID.
	 * @param int    $frequency Frequency/Interval Count.
	 * @param string $period Period/Interval. Default empty.
	 *
	 * @return bool|string
	 */
	public function get_new_expiration( $form_id = 0, $price_id = null, $frequency = 1, $period = '' ) {

		// If $period is empty, then try fetching it with possible scenarios.
		if ( empty( $period ) ) {
			if ( isset( $_POST['give-recurring-period-donors-choice'] ) ) {
				$period = give_clean( $_POST['give-recurring-period-donors-choice'] );
			} else if ( give_has_variable_prices( $form_id ) ) {
				$period = Give_Recurring::get_period( $form_id, $price_id );
			} else {
				$period = Give_Recurring::get_period( $form_id );
			}
		}

		// Calculate the quarter as times 3 months
		if ( $period === 'quarter' ) {
			$frequency *= 3;
			$period    = 'month';
		}

		return date( 'Y-m-d H:i:s', strtotime( '+ ' . $frequency . $period . ' 23:59:59' ) );
	}

	/**
	 * Get Recurring Customer ID
	 *
	 * Get a recurring customer ID
	 *
	 * @since       1.0
	 *
	 * @param  $gateway      string The gateway to get the customer ID for
	 *
	 * @return string
	 */
	public function get_recurring_donor_id( $gateway ) {

		$recurring_ids = $this->get_recurring_donor_ids();

		if ( is_array( $recurring_ids ) ) {
			if ( false === $gateway || ! array_key_exists( $gateway, $recurring_ids ) ) {
				$gateway = reset( $recurring_ids );
			}

			$donor_id = $recurring_ids[ $gateway ];
		} else {
			$donor_id = empty( $recurring_ids ) ? false : $recurring_ids;
		}

		return apply_filters( 'give_recurring_get_donor_id', $donor_id, $this );

	}

	/**
	 * Store a recurring customer ID in array
	 *
	 * Sets a customer ID per gateway as needed; for instance, Stripe you create a customer and then subscribe them to a plan. The customer ID is stored here.
	 *
	 * @since      1.0
	 *
	 * @param  $gateway      string The gateway to set the customer ID for
	 * @param  $recurring_id string The recurring profile ID to set
	 *
	 * @return bool
	 */
	public function set_recurring_donor_id( $gateway, $recurring_id = '' ) {

		// We require a gateway identifier to be included, if it's not, return false.
		if ( false === $gateway ) {
			return false;
		}

		$recurring_id  = apply_filters( 'give_recurring_set_donor_id', $recurring_id, $this->user_id );
		$recurring_ids = $this->get_recurring_donor_ids();

		if ( ! is_array( $recurring_ids ) ) {

			$existing      = $recurring_ids;
			$recurring_ids = [];

			// If the first three characters match, we know the existing ID belongs to this gateway
			if ( substr( $recurring_id, 0, 3 ) === substr( $existing, 0, 3 ) ) {

				$recurring_ids[ $gateway ] = $existing;

			}

		}

		$recurring_ids[ $gateway ] = $recurring_id;

		// Update donor meta.
		return $this->update_meta( '_give_recurring_id', $recurring_ids );

	}

	/**
	 * Retrieve the recurring gateway specific IDs for the donor.
	 *
	 * @since  1.2
	 *
	 * @return mixed The profile IDs
	 */
	public function get_recurring_donor_ids() {

		$ids = $this->get_meta( '_give_recurring_id' );

		// Backwards compatibility for user meta.
		if ( empty( $ids ) ) {
			$ids = get_user_meta( $this->user_id, '_give_recurring_id', true );
		}

		return apply_filters( 'give_recurring_donor_ids', $ids, $this );
	}


	/**
	 * Return subscriber object.
	 *
	 * @since 1.10.1
	 *
	 * @return Give_Recurring_Subscriber|null
	 */
	public static function getSubscriber() {
		// Get subscription.
		$current_user_id = get_current_user_id();

		// Get by user id.
		if ( ! empty( $current_user_id ) ) {
			return new static( $current_user_id, true );
		}

		// Get by email access.
		if ( Give()->email_access->token_exists ) {
			return new static( Give()->email_access->token_email, false );
		}

		// Get by email.
		if ( Give()->session->get_session_expiration() ) {
			$subscriber_email = maybe_unserialize( Give()->session->get( 'give_purchase' ) );
			$subscriber_email = isset( $subscriber_email['user_email'] ) ? $subscriber_email['user_email'] : '';

			return new static( $subscriber_email, false );
		}

		return null;
	}

	/**
	 * Return boolean to determine access
	 *
	 * @since 1.10.1
	 *
	 * a. Check if a user is logged in
	 * b. Does an email-access token exist?
	 */
	public static function canAccessView() {
		return is_user_logged_in() || ( give_is_setting_enabled( give_get_option( 'email_access' ) ) && Give()->email_access->token_exists );
	}

	/**
	 * Return boolean to determine subscription belongs to donor or not
	 *
	 * @param Give_Subscription         $subscription
	 * @param Give_Recurring_Subscriber $subscriber
	 *
	 * @return boolean
	 */
	public static function doesSubscriptionBelongsTo( $subscription, $subscriber = null ) {
		$subscriber = $subscriber ?: self::getSubscriber();

		return $subscription->donor_id === $subscriber->id;
	}

	/**
	 * Get subscriber access type.
	 *
	 * @since 1.10.1
	 * @return string
	 */
	public static function getAccessType() {
		if ( is_user_logged_in() ) {
			return 'wpUser';
		}

		if ( Give()->email_access->token_exists && give_is_setting_enabled( give_get_option( 'email_access' ) ) ) {
			return 'EmailAccess';
		}

		if ( false !== Give()->session->get_session_expiration() ) {
			return 'DonorSession';
		}

		return '';
	}

}
