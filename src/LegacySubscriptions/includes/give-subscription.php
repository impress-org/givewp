<?php
/**
 * Give Recurring Subscription
 *
 * @package     Give
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Subscription
 *
 * @since 2.19.0 - migrated from give-recurring
 * @since 1.0
 */
class Give_Subscription {

	/**
	 * @var Give_Subscriptions_DB
	 */
	private $subs_db;

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var int
	 */
	public $donor_id = 0;

	/**
	 * @var string
	 */
	public $period = '';

	/**
	 * @var int
	 */
	public $frequency = 1;

	/**
	 * @var string
	 */
	public $initial_amount = '';

	/**
	 * @var string
	 */
	public $recurring_amount = '';

	/**
	 * @var float
	 */
	public $recurring_fee_amount = 0;

	/**
	 * @var int
	 */
	public $bill_times = 0;

	/**
	 * @var string
	 */
	public $transaction_id = '';

	/**
	 * @var int
	 */
	public $parent_payment_id = 0;

	/**
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * @var string
	 */
	public $created = '0000-00-00 00:00:00';

	/**
	 * @var string
	 */
	public $expiration = '0000-00-00 00:00:00';

	/**
	 * @var string
	 */
	public $status = 'pending';

	/**
	 * @var string
	 */
	public $profile_id = '';

	/**
	 * @var string
	 */
	public $gateway = '';

	/**
	 * @var Give_Donor
	 */
	public $donor;

	/**
	 * Give_Subscription constructor.
	 *
	 * @param int  $_id_or_object Subscription ID or Object
	 * @param bool $_by_profile_id
	 */
	function __construct( $_id_or_object = 0, $_by_profile_id = false ) {

		$this->subs_db = new Give_Subscriptions_DB();

		if ( $_by_profile_id ) {

			$_sub = $this->subs_db->get_by( 'profile_id', $_id_or_object );

			if ( empty( $_sub ) ) {
				return false;
			}

			$_id_or_object = $_sub;

		}

		return $this->setup_subscription( $_id_or_object );
	}

	/**
     * Setup the subscription object.
     *
     * @since 2.19.3 - cast bill_times to integer
     *
     * @param  int  $id_or_object
     *
     * @return Give_Subscription|bool
     */
	private function setup_subscription( $id_or_object = 0 ) {

		if ( empty( $id_or_object ) ) {
			return false;
		}
		if ( is_numeric( $id_or_object ) ) {

			$sub = $this->subs_db->get( $id_or_object );

		} elseif ( is_object( $id_or_object ) ) {

			$sub = $id_or_object;

		}

		if ( empty( $sub ) ) {
			return false;
		}

		foreach ( $sub as $key => $value ) {
            // Backwards compatibility:
            // Ensure product_id get sent to new form_id.
            if ('product_id' === $key) {
                $this->form_id = $value;
            }

            if ('customer_id' === $key) {
                $this->donor_id = $value;
            }

            if ('bill_times' === $key) {
                $value = (int)$value;
            }

            $this->$key = $value;
        }

		$this->donor   = new Give_Donor( $this->donor_id );
		$this->gateway = give_get_payment_gateway( $this->parent_payment_id );

		do_action( 'give_recurring_setup_subscription', $this );

		return $this;
	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property.
	 *
	 * @param $key
	 *
	 * @return mixed|WP_Error
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			return new WP_Error( 'give-subscription-invalid-property', sprintf( __( 'Can\'t get property %s', 'give' ), $key ) );

		}

	}

	/**
	 * Creates a subscription.
	 *
	 * @since  1.0
	 *
	 * @param  array $data Array of attributes for a subscription
	 *
	 * @return mixed  false if data isn't passed and class not instantiated for creation
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'customer_id'          => 0,
			'period'               => '',
			'frequency'            => 1,
			'initial_amount'       => '',
			'recurring_amount'     => '',
			'recurring_fee_amount' => 0,
			'bill_times'           => 0,
			'parent_payment_id'    => 0,
			'form_id'              => 0,
			'created'              => '',
			'expiration'           => '',
			'status'               => '',
			'profile_id'           => '',
		);

		$args = wp_parse_args( $data, $defaults );

		if ( $args['expiration'] && strtotime( 'NOW', current_time( 'timestamp' ) ) > strtotime( $args['expiration'], current_time( 'timestamp' ) ) ) {

			if ( 'active' == $args['status'] ) {

				// Force an active subscription to expired if expiration date is in the past
				$args['status'] = 'expired';

			}
		}

		do_action( 'give_subscription_pre_create', $args );

		// @TODO: DB column should be updated to 'form_id' in future and remove this backwards compatibility.
		if ( ! empty( $args['form_id'] ) ) {
			$args['product_id'] = $args['form_id'];
		}
		// @TODO: DB column should be updated to 'customer_id' in future and remove this backwards compatibility.
		if ( ! empty( $args['donor_id'] ) ) {
			$args['customer_id'] = $args['donor_id'];
		}

		$id = $this->subs_db->create( $args );

		do_action( 'give_subscription_post_create', $id, $args );

		// If Payment status is renewal, then update purchase count and amount of donor.
		$payment = new Give_Payment( $args['parent_payment_id'] );
		if ( ( 'give_subscription' === $payment->post_status ) && ! empty( $args['customer_id'] ) ) {
			$donor = new Give_Donor( $args['customer_id'] );
			$donor->increase_purchase_count();
			$donor->increase_value( $args['recurring_amount'] );
		}

		return $this->setup_subscription( $id );

	}

	/**
	 * Update.
	 *
	 * Updates a subscription.
	 *
	 * @param array $args Array of fields to update
	 *
	 * @return bool
	 */
	public function update( $args ) {

		if ( isset( $args['status'] ) && strtolower( $this->status ) !== strtolower( $args['status'] ) ) {
			$this->add_note( sprintf( __( 'Status changed from %s to %s', 'give' ), $this->status, $args['status'] ) );
		}

		$ret = $this->subs_db->update( $this->id, $args );

		do_action( 'give_recurring_update_subscription', $this->id, $args, $this );

		return $ret;

	}

	/**
	 * Delete the subscription.
	 *
	 * @return bool
	 */
	public function delete() {
		do_action( 'give_recurring_before_delete_subscription', $this );
		$deleted = $this->subs_db->delete( $this->id );
		do_action( 'give_recurring_after_delete_subscription', $deleted, $this );

		return $deleted;
	}

	/**
	 * Get Original Payment ID.
	 *
	 * @return int
	 */
	public function get_original_payment_id() {
		return $this->parent_payment_id;
	}

	/**
	 * Get Child Payments.
	 *
	 * Retrieves subscription renewal payments for a subscription.
	 *
	 * @return array
	 */
	public function get_child_payments() {
		$args = array(
			'post_parent'    => (int) $this->parent_payment_id,
			'posts_per_page' => '9999',
			'post_status'    => 'any',
			'post_type'      => 'give_payment',
		);

		$cache_key = Give_Cache::get_key( 'child_payments', $args, false );
		$payments = Give_Recurring_Cache::get_db_query( $cache_key );

		if( is_null( $payments ) ) {
			$payments = get_posts( $args );
			Give_Recurring_Cache::set_db_query( $cache_key, $payments );
		}

		return $payments;
	}

	/**
	 * Get the Initial Payment.
	 *
	 * Retrieves the first payment object for the subscription.
	 *
	 * @return array|bool
	 */
	public function get_initial_payment() {

		$initial_payment = get_posts( array(
			'include'        => (int) $this->parent_payment_id,
			'posts_per_page' => '1',
			'post_status'    => 'any',
			'post_type'      => 'give_payment',
		) );

		if ( isset( $initial_payment[0] ) ) {
			return $initial_payment[0];
		}

		return false;

	}


	/**
	 * Get Total Payments.
	 *
	 * Returns the total number of times a subscription has been paid including the initial payment (that's the +1).
	 *
	 * @return int
	 */
	public function get_total_payments() {
		return count( $this->get_child_payments() ) + 1;
	}

	/**
	 * Get the last payment made.
	 *
	 * Returns the subscriptions last payment made regardless of whether it was a renewal or initial payment.
	 *
	 * @since 1.8
	 *
	 * @return WP_Post object
	 */
	public function get_last_payment() {

		// If renewals, return the latest renewal.
		$renewals = $this->get_child_payments();
		if ( count( $renewals ) !== 0 ) {
			return $renewals[0];
		}

		$initial_payment = get_posts( array(
			'include'        => (int) $this->parent_payment_id,
			'posts_per_page' => '1',
			'post_status'    => 'any',
			'post_type'      => 'give_payment',
		) );

		// If no renewals return parent payment.
		return isset( $initial_payment[0] ) ? $initial_payment[0] : false;

	}

	/**
	 * Get Lifetime Value.
	 *
	 * @return int $amount
	 */
	public function get_lifetime_value() {

		$amount = 0.00;

		$parent_payment        = give_get_payment_by( 'id', $this->parent_payment_id );
		$parent_payment_status = give_get_payment_status( $parent_payment );
		$ignored_statuses      = array( 'refunded', 'pending', 'abandoned', 'failed' );

		if ( false === in_array( $parent_payment_status, $ignored_statuses ) ) {
			$amount = give_donation_amount( $this->parent_payment_id );
		}

		$children = $this->get_child_payments();

		if ( $children ) {

			foreach ( $children as $child ) {
				$child_payment_status = give_get_payment_status( $child );
				if ( 'refunded' === $child_payment_status ) {
					continue;
				}

				$amount += give_donation_amount( $child->ID );
			}
		}

		return $amount;

	}

	/**
	 * Add Payment.
	 *
	 * Records a new payment on the subscription.
	 *
	 * @since 2.21.3 add support for anonymous donations
	 * @since 1.12.7 Set donor first and last name in new donation
	 *
	 * @param array $args Array of values for the payment, including amount and transaction ID.
	 *
	 * @return bool
	 */
	public function add_payment( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'amount'         => '',
			'transaction_id' => '',
			'gateway'        => '',
			'post_date'      => '',
		) );

		// Check if the payment exists.
		if ( $this->payment_exists( $args['transaction_id'] ) ) {
			return false;
		}

		$payment = new Give_Payment();
		$parent  = new Give_Payment( $this->parent_payment_id );

		// Sanitize donation amount.
		$args['amount'] = $this->mayBeSanitizeWebhookResponseDonationAmount( $args['amount'], $parent->currency );


		$payment->parent_payment = $this->parent_payment_id;
		$payment->total          = $args['amount'];
		$payment->form_title     = $parent->form_title;
		$payment->form_id        = $parent->form_id;
		$payment->customer_id    = $parent->customer_id;
		$payment->address        = $parent->address;
		$payment->first_name     = $parent->user_info['first_name'];
		$payment->last_name      = $parent->user_info['last_name'];
		$payment->user_info      = $parent->user_info;
		$payment->user_id        = $parent->user_id;
		$payment->email          = $parent->email;
		$payment->currency       = $parent->currency;
		$payment->status         = 'give_subscription';
		$payment->transaction_id = $args['transaction_id'];
		$payment->key            = $parent->key;
		$payment->mode           = $parent->mode;

		$donor    = new Give_Donor( $payment->customer_id );
		$price_id = give_get_price_id( $parent->form_id, $args['amount'] );

		/**
		 * Get Correct Donation Level ID for renewal amount.
		 *
		 * @param  integer  $price_id  Price ID.
		 * @param  float  $amount  Renewal amount
		 * @param  \Give_Payment  $payment  Renewal Payment
		 *
		 * @return integer $price_id
		 * @since 1.8
		 *
		 */
		$price_id = apply_filters( 'give_recurring_renewal_price_id', $price_id, $args['amount'], $parent );

		// Set price id.
		$payment->price_id = $price_id;

		if ( empty( $args['gateway'] ) ) {
			$payment->gateway = $parent->gateway;
		} else {
			$payment->gateway = $args['gateway'];
		}

		// If post_date is set (by synchronizer for past payments for example) then pass it along.
		if ( ! empty( $args['post_date'] ) ) {
			$payment->date = $args['post_date'];
		}

		// Increase the earnings for the form in the subscription.
		give_increase_earnings( $parent->form_id, $args['amount'] );
		// Increase the donation count for this form as well.
		give_increase_donation_count( $parent->form_id );

		$payment->add_donation( $parent->form_id, array( 'price' => $args['amount'], 'price_id' => $price_id ) );
		$payment->save();
		$payment->update_meta( 'subscription_id', $this->id );
		$donor->increase_purchase_count( 1 );
		$donor->increase_value( $args['amount'] );

        if ($parent->get_meta('_give_anonymous_donation')) {
            $payment->update_meta('_give_anonymous_donation', 1);
        }

		// Add give recurring subscription notification
		do_action( 'give_recurring_add_subscription_payment', $payment, $this );
		do_action( 'give_recurring_record_payment', $payment, $this->parent_payment_id, $args['amount'], $args['transaction_id'] );

		return true;
	}

	/**
	 * Get Transaction ID.
	 *
	 * Retrieves the transaction ID from the subscription.
	 *
	 * @since  1.2
	 * @return bool
	 */
	public function get_transaction_id() {

		if ( empty( $this->transaction_id ) ) {

			$txn_id = give_get_payment_transaction_id( $this->parent_payment_id );

			if ( ! empty( $txn_id ) && (int) $this->parent_payment_id !== (int) $txn_id ) {
				$this->set_transaction_id( $txn_id );
			}
		}

		return $this->transaction_id;

	}

	/**
	 * Stores the transaction ID for the subscription donation.
	 *
	 * @since  1.2
	 *
	 * @param string $txn_id
	 *
	 * @return bool
	 */
	public function set_transaction_id( $txn_id = '' ) {
		$this->update( array(
			'transaction_id' => $txn_id,
		) );
		give_set_payment_transaction_id( $this->parent_payment_id, $txn_id );
		$this->transaction_id = $txn_id;
	}

	/**
	 * Renew Payment.
	 *
	 * This method is responsible for renewing a subscription (not adding payments).
	 * It checks the expiration date, whether the subscription is active, run hooks, sets notes, and updates the
	 * subscription status as necessary. If the subscription has reached the total number of bill times the
	 * subscription will be completed.
	 *
	 * @since       1.0
	 * @return bool
	 */
	public function renew() {

		$expires = $this->get_expiration_time();

		// Determine what date to use as the start for the new expiration calculation.
		if ( $expires > current_time( 'timestamp' ) && $this->is_active() ) {
			$base_date = $expires;
		} else {
			$base_date = current_time( 'timestamp' );
		}

		$last_day   = cal_days_in_month( CAL_GREGORIAN, date( 'n', $base_date ), date( 'Y', $base_date ) );
		$expiration = date( 'Y-m-d H:i:s', strtotime( '+1 ' . $this->period . ' 23:59:59', $base_date ) );

		if ( date( 'j', $base_date ) == $last_day && 'day' != $this->period ) {
			$expiration = date( 'Y-m-d H:i:s', strtotime( $expiration . ' +2 days' ) );
		}

		$expiration = apply_filters( 'give_subscription_renewal_expiration', $expiration, $this->id, $this );

		do_action( 'give_subscription_pre_renew', $this->id, $expiration, $this );

		$status       = 'active';
		$times_billed = $this->get_total_payments();

		// Complete subscription if applicable.
		if ( $this->bill_times > 0 && $times_billed >= $this->bill_times ) {
			$this->complete();
			$status = 'completed';
		}

		$args = array(
			'expiration' => $expiration,
			'status'     => $status,
		);

		$this->update( $args );

		do_action( 'give_subscription_post_renew', $this->id, $expiration, $this );
		do_action( 'give_recurring_set_subscription_status', $this->id, $status, $this );

	}

	/**
	 * Subscription Complete.
	 *
	 * Subscription is completed when the number of payments matches the billing_times field.
	 *
	 * @return void
	 */
	public function complete() {

		$args = array(
			'status' => 'completed',
		);

		// Prevent duplicate update.
		if( ! $this->can_update_status('completed') ) {
			return;
		}

		if ( $this->subs_db->update( $this->id, $args ) ) {

			do_action( 'give_subscription_completed', $this->id, $this );

		}

	}

	/**
	 * Subscription Expire.
	 *
	 * Marks a subscription as expired. Subscription is completed when the billing times is reached.
	 *
	 * @since  1.1.2
	 * @return void
	 */
	public function expire() {

		$args = array(
			'status' => 'expired',
		);

		// Prevent duplicate update.
		if( ! $this->can_update_status('expired') ) {
			return;
		}

		if ( $this->subs_db->update( $this->id, $args ) ) {

			do_action( 'give_subscription_expired', $this->id, $this );

		}

		$this->status = 'expired';

	}

	/**
	 * Marks a subscription as failing.
	 *
	 * @since  1.1.2
	 * @return void
	 */
	public function failing() {

		$args = array(
			'status' => 'failing',
		);

		// Prevent duplicate update.
		if( ! $this->can_update_status('failing') ) {
			return;
		}

		if ( $this->subs_db->update( $this->id, $args ) ) {
			do_action( 'give_subscription_failing', $this->id, $this );
		}

		$this->status = 'failing';

	}

	/**
	 * Subscription Cancelled.
	 *
	 * Marks a subscription as cancelled.
	 *
	 * @return void
	 */
	public function cancel() {

		$args = array(
			'status' => 'cancelled',
		);

		// Prevent duplicate update.
		if( ! $this->can_update_status('cancelled') ) {
			return;
		}

		if ( $this->subs_db->update( $this->id, $args ) ) {

			if ( is_user_logged_in() ) {

				$userdata = get_userdata( get_current_user_id() );
				$user     = $userdata->user_login;

			} else {

				$user = __( 'gateway', 'give' );

			}

			$note = sprintf( __( 'Subscription #%1$d cancelled by %2$s', 'give' ), $this->id, $user );
			$this->donor->add_note( $note );
			$this->status = 'cancelled';

			// Add give subscription cancelled notification
			do_action( 'give_subscription_cancelled', $this->id, $this );

		}

	}

	/**
	 * Can Cancel.
	 *
	 * This method is filtered by payment gateways in order to return true on subscriptions
	 * that can be cancelled with a profile ID through the merchant processor.
	 *
	 * @return mixed
	 */
	public function can_cancel() {
		return apply_filters( 'give_subscription_can_cancel', false, $this );
	}

	/**
	 * Can Sync.
	 *
	 * This method is filtered by payment gateways in order to return true on subscriptions
	 * that can sync through the merchant processor.
	 *
	 * @return mixed
	 */
	public function can_sync() {
		return apply_filters( 'give_subscription_can_sync', false, $this );
	}

	/**
	 * Get Cancel URL.
	 *
	 * @return mixed
	 */
	public function get_cancel_url() {

		$url = wp_nonce_url( add_query_arg( array(
			'give_action' => 'cancel_subscription',
			'sub_id'      => $this->id,
		) ), "give-recurring-cancel-{$this->id}" );

		return apply_filters( 'give_subscription_cancel_url', esc_url($url), $this );
	}


	/**
	 * Can Update.
	 *
	 * @since  1.1.2
	 * @return mixed
	 */
	public function can_update() {
		return apply_filters( 'give_subscription_can_update', false, $this );
	}

	/**
	 * Can Update Subscription.
	 *
	 * @since  1.8
	 * @return mixed
	 */
	public function can_update_subscription() {
		return apply_filters( 'give_subscription_can_update_subscription', false, $this );
	}

	/**
	 * Get Update URL.
	 *
	 * Retrieves the URL to update subscription.
	 *
	 * @since  1.1.2
	 * @return string $url
	 */
	public function get_update_url() {

		$url = esc_url(add_query_arg( array(
			'action'          => 'update',
			'subscription_id' => $this->id,
		) ) );

		return apply_filters( 'give_subscription_update_url', $url, $this );
	}

	/**
	 * Get Edit Subscription URL
	 *
	 * @since 1.8
	 * @return string
	 */
	public function get_edit_subscription_url() {

		$url = esc_url(add_query_arg( array(
			'action'          => 'edit_subscription',
			'subscription_id' => $this->id,
		), give_get_subscriptions_page_uri() ));

		return apply_filters( 'give_subscription_edit_subscription_url', $url, $this );
	}

	/**
	 * Is Active.
	 *
	 * @return bool $ret Whether the subscription is active or not.
	 */
	public function is_active() {

		$ret = false;

		if ( ! $this->is_expired() && ( $this->status == 'active' || $this->status == 'cancelled' ) ) {
			$ret = true;
		}

		return apply_filters( 'give_subscription_is_active', $ret, $this->id, $this );

	}

	/**
	 * Is Complete.
	 *
	 * @return bool $ret Whether the subscription is complete or not.
	 */
	public function is_complete() {

		$ret = false;

		if ( 'completed' === $this->status ) {
			$ret = true;
		}

		return apply_filters( 'give_subscription_is_complete', $ret, $this->id, $this );

	}


	/**
	 * Is Expired.
	 *
	 * @return bool|string
	 */
	public function is_expired() {

		$ret = false;

		if ( 'expired' === $this->status ) {
			$ret = true;
		}

		return apply_filters( 'give_subscription_is_expired', $ret, $this->id, $this );

	}

	/**
	 * Retrieves the expiration date.
	 *
	 * @return string
	 */
	public function get_expiration() {
		return $this->expiration;
	}

	/**
	 * Get Expiration Time.
	 *
	 * Retrieves the expiration date in a timestamp.
	 *
	 * @return int
	 */
	public function get_expiration_time() {
		return strtotime( $this->expiration, current_time( 'timestamp' ) );
	}

	/**
	 * Retrieves the subscription status.
	 *
	 * @return int
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Get Subscription Progress.
	 *
	 * Returns the subscription progress compared to `bill_times` such as "1/3" or "1/ Ongoing".
	 *
	 * @return int
	 */
	public function get_subscription_progress() {
		return sprintf(
			'%1$s / %2$s',
			$this->get_total_payments(),
			0 === intval( $this->bill_times ) ? __( 'Ongoing', 'give' ) : $this->bill_times
		);
	}

	/**
	 * Get Subscription End Date.
	 *
	 * @return int
	 */
	public function get_subscription_end_time() {

		$bill_times = intval( $this->bill_times );

		// Date out = the end of the subscription.
		// Subtract 1 due to initial donation being counted.
		$date_out = '+' . ( $bill_times - 1 ) . ' ' . $this->period;

		return strtotime( $date_out, strtotime( $this->created ) );

	}

	/**
	 * Get the Subscription Renewal Date.
	 *
	 * @param bool $localized Flag to return date in localized format or not
	 *
	 * @return string
	 */
	public function get_renewal_date( $localized = true ) {

		$expires   = $this->get_expiration_time();
		$frequency = ! empty( $this->frequency ) ? intval( $this->frequency ) : 1;

		// If renewal date is already in the future it's set so return it.
		if ( $expires > current_time( 'timestamp' ) && $this->is_active() ) {
			return $localized
				? date_i18n( give_date_format(), strtotime( $this->expiration ) )
				: date( 'Y-m-d H:i:s', strtotime( $this->expiration ) );
		}

		$last_payment = $this->get_last_payment();

		// The renewal date is in the past, recalculate it based off last payment made on subscription.
		// Fallback to current time if last payment returns nothing to prevent PHP notice and 1970 date.
		$last_payment_timestamp = isset( $last_payment->post_date ) ? strtotime( $last_payment->post_date ) : current_time( 'timestamp' );
		$renewal_timestamp = strtotime( '+ ' . $frequency . $this->period . ' 23:59:59', $last_payment_timestamp );

		return $localized
			? date_i18n( give_date_format(), $renewal_timestamp )
			: date( 'Y-m-d H:i:s', $renewal_timestamp );

	}


	/**
	 * Is Parent Payment.
	 *
	 * @since 1.2
	 *
	 * @param int $donation_id Donation ID.
	 *
	 * @return bool
	 */
	public function is_parent_payment( $donation_id ) {
		return give_recurring_is_parent_donation( $donation_id );
	}

	/**
	 * Payment Exists.
	 *
	 * @param string $txn_id transaction ID.
	 *
	 * @return bool
	 */
	public function payment_exists( $txn_id = '' ) {
		global $wpdb;

		if ( empty( $txn_id ) ) {
			return false;
		}

		$txn_id = esc_sql( $txn_id );

		$donation_meta_table_name = Give()->payment_meta->table_name;
		$donation_id_col_name     = Give()->payment_meta->get_meta_type() . '_id';

		$donation = $wpdb->get_var(
			"
				SELECT {$donation_id_col_name}
				FROM {$donation_meta_table_name}
				WHERE meta_key = '_give_payment_transaction_id'
				AND meta_value = '{$txn_id}'
				LIMIT 1
				"
		);

		if ( $donation != null ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the parsed notes for a subscription as an array
	 *
	 * @since  1.4
	 *
	 * @param  integer $length The number of notes to get
	 * @param  integer $paged  What note to start at
	 *
	 * @return array           The notes requested
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		$length = is_numeric( $length ) ? $length : 20;
		$offset = is_numeric( $paged ) && $paged != 1 ? ( ( absint( $paged ) - 1 ) * $length ) : 0;

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		$desired_notes = array_slice( $notes_array, $offset, $length );

		return $desired_notes;

	}

	/**
	 * Get the total number of notes we have after parsing
	 *
	 * @since  1.4
	 * @return int The number of notes for the subscription
	 */
	public function get_notes_count() {

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		return count( $notes_array );

	}


	/**
	 * Add a note for the subscription
	 *
	 * @since  1.4
	 *
	 * @param string $note The note to add
	 *
	 * @return string|boolean The new note if added successfully, false otherwise.
	 */
	public function add_note( $note = '' ) {

		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		$notes = $this->get_raw_notes();

		if ( empty( $notes ) ) {
			$notes = '';
		}

		$note_string = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' - ' . $note;
		$new_note    = apply_filters( 'give_subscription_add_note_string', $note_string );
		$notes       .= "\n\n" . $new_note;

		do_action( 'give_subscription_pre_add_note', $new_note, $this->id );

		$updated = $this->update( array( 'notes' => $notes ) );

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}

		do_action( 'give_subscription_post_add_note', $this->notes, $new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $new_note;

	}

	/**
	 * Get the notes column for the subscription.
	 *
	 * @since  1.4
	 * @return string The Notes for the subscription, non-parsed
	 */
	private function get_raw_notes() {

		$all_notes = $this->subs_db->get_column( 'notes', $this->id );

		return (string) $all_notes;

	}

	/**
	 * Convert object to array
	 *
	 * @since 1.4
	 *
	 * @return array
	 */
	public function to_array() {

		$array = array();
		foreach ( get_object_vars( $this ) as $prop => $var ) {

			if ( is_object( $var ) && is_callable( array( $var, 'to_array' ) ) ) {

				$array[ get_class( $var ) ] = $var->to_array();

			} else {

				$array[ $prop ] = $var;

			}
		}

		return $array;
	}

	/**
	 * Check if we can update subscription status or not
	 *
	 * It will help to prevent duplicate updates
	 *
	 * @param string $status
	 *
	 * @return bool
	 * @since 1.9.8
	 *
	 */
	private function can_update_status( $status = '' ) {
		return $status && ( $status !== $this->subs_db->get_column( 'status', $this->id ) );
	}

	/**
	 * Return sanitized donation amount which comes from webhook and formatted with standard formatting setting. (number of decimal: "2", decimal separator: "." ).
	 *
	 * @param  float  $donationAmount
	 * @param  string  $currencyCode
	 *
	 * @return float
	 * @since 1.10.5
	 */
	private function mayBeSanitizeWebhookResponseDonationAmount( $donationAmount, $currencyCode ) {
		// Is processing webhook for any payment gateway.
		if ( empty( $_GET['give-listener'] ) ) {
			return $donationAmount;
		}

		$donationAmountStr = (string) $donationAmount;
		$numberOfDecimal   = give_get_price_decimals( $currencyCode );
		$thousandSeparator = give_get_price_thousand_separator( $currencyCode );
		$decimalSeparator  = give_get_price_decimal_separator( $currencyCode );
		$amountPart        = explode( '.', $donationAmountStr );

		// Sanitize donation amount only if
		// 1. number of decimal is set to zero for give currency.
		// 2. "." is thousand separator.
		// 3. amount formatted with ".".
		// 4. number of decimal for amount is two. like 10.25 or 40.00
		if (
			! $numberOfDecimal &&
			'.' === $thousandSeparator &&
			false !== strpos( $donationAmountStr, $thousandSeparator ) &&
			false === strpos( $donationAmountStr, $decimalSeparator ) &&
			2 === count( $amountPart ) &&
			2 === strlen( $amountPart[1] )
		) {
			$donationAmount = number_format( (float) $donationAmount, 10 );
		}

		return $donationAmount;
	}
}
