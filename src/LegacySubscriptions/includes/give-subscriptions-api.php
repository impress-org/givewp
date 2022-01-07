<?php
/**
 * Subscribers REST API
 *
 * @package     Give Recurring
 * @subpackage  Subscriber API Class
 * @copyright   Copyright (c) 2017
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Subscriptions_API
 *
 * Extends the Give_API to make the /subscriptions endpoint
 *
 * @class Give_Subscriptions_API
 * @since 1.4
 */
class Give_Subscriptions_API extends Give_API {

	/**
	 * User ID Performing the API Request
	 *
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * @var bool
	 */
	public $override = true;

	/**
	 * Adds to the allowed query vars list from Give Core for API access
	 *
	 * @access public
	 * @since  2.4.3
	 * @author Topher
	 *
	 * @param  array $vars Query vars.
	 *
	 * @return string[] $vars New query vars
	 */
	public function query_vars( $vars ) {

		$vars[] = 'status';

		return $vars;
	}

	/**
	 * Safely gets the status from the URL
	 *
	 * @access private
	 * @since  2.4.3
	 * @author Topher
	 */
	private function get_status_from_url() {

		// Get the query vars.
		global $wp_query;

		$status = '';

		$allowed = array(
			'active',
			'pending',
			'failing',
			'completed',
			'cancelled',
			'expired',
		);

		// Get status information from the input.
		$input_status = isset( $wp_query->query_vars['status'] ) ? $wp_query->query_vars['status'] : null;

		if ( in_array( $input_status, $allowed ) ) {
			$status = $input_status;
		}

		if ( null !== $input_status && '' === $status ) {
			$error['error'] = sprintf( __( '\'%s\' is not a valid status.', 'give-recurring' ), $input_status );

			return $error;
		} else {
			return $status;
		}
	}


	/**
	 * Give_Subscriptions_API constructor.
	 */
	public function __construct() {
		add_filter( 'give_api_valid_query_modes', array( $this, 'add_valid_subscriptions_query' ) );
		add_filter( 'give_api_output_data', array( $this, 'add_give_subscription_endpoint' ), 10, 3 );
	}

	/**
	 * Add 'subscriptions' api endpoint.
	 *
	 * @param $queries
	 *
	 * @return array
	 */
	public function add_valid_subscriptions_query( $queries ) {
		$queries[]      .= 'subscriptions';
		$this->override = false;

		return $queries;
	}

	/**
	 * Add Subscribers Endpoint
	 *
	 * @param $data
	 * @param $query_mode
	 * @param $api_object
	 *
	 * @return array $subscriptions
	 */
	public function add_give_subscription_endpoint( $data, $query_mode, $api_object ) {

		// Sanity check: don't mess with other API queries!
		if ( 'subscriptions' !== $query_mode ) {
			return $data;
		}

		// Get query vars.
		global $wp_query;

		// Get the status from input.
		$status = $this->get_status_from_url();

		if ( is_array( $status ) && array_key_exists( 'error', $status ) ) {
			$error = $status;

			return $error;
		}

		// Get the donor information from the input.
		$queried_c = isset( $wp_query->query_vars['donor'] ) ? sanitize_text_field( $wp_query->query_vars['customer'] ) : null;
		$donor     = new Give_Donor( $queried_c );

		if ( ! empty( $queried_c ) && ( ! $donor || ! $donor->id > 0 ) ) {
			$error['error'] = sprintf( __( 'No donor found for %s!', 'give-recurring' ), $queried_c );

			return $error;
		}

		$count         = 0;
		$response_data = array();
		if ( isset( $wp_query->query_vars['id'] ) && is_numeric( $wp_query->query_vars['id'] ) ) {

			$subscriptions = array(
				new Give_Subscription( $wp_query->query_vars['id'] ),
			);

		} else {
			$paged         = $this->get_paged();
			$per_page      = $this->per_page();
			$offset        = $per_page * ( $paged - 1 );
			$db            = new Give_Subscriptions_DB();
			$subscriptions = $db->get_subscriptions( array(
				'number'      => $per_page,
				'offset'      => $offset,
				'customer_id' => $donor->id,
				'status'      => $status,
			) );
		}

		if ( $subscriptions ) {

			$unset_info_keys = array(
				'product_id',
				'customer_id',
				'subs_db',
			);

			/** @var Give_Subscription $subscription */
			foreach ( $subscriptions as $subscription ) {
				// Subscription object to array.
				$response_data['subscriptions'][ $count ]['info'] = $subscription->to_array();
				$tmp_donor = (array) $response_data['subscriptions'][ $count ]['info']['donor'];

				// Remove `:protected` index key from array
				$donor = array();
				foreach ( $tmp_donor as $donor_key => $donor_data ){
					if( false !== strpos( $donor_key, '*') ) {
						continue;
					}

					$donor[$donor_key] = $donor_data;
				}
				$response_data['subscriptions'][ $count ]['info']['donor'] = $donor;

				// Remove legacy array keys.
				foreach ($unset_info_keys as $info_key ) {
					if ( isset( $response_data['subscriptions'][ $count ]['info'][$info_key] ) ) {
						unset( $response_data['subscriptions'][ $count ]['info'][$info_key] );
					}
				}

				// Format amount.
				$response_data['subscriptions'][ $count ]['info']['initial_amount'] = give_format_decimal( array(
					'donation_id' => $response_data['subscriptions'][ $count ]['info']['parent_payment_id'],
					'amount' => $response_data['subscriptions'][ $count ]['info']['initial_amount'],
					'dp' => true
				) );

				// Format amount.
				$response_data['subscriptions'][ $count ]['info']['recurring_amount'] = give_format_decimal( array(
					'donation_id' => $response_data['subscriptions'][ $count ]['info']['parent_payment_id'],
					'amount' => $response_data['subscriptions'][ $count ]['info']['recurring_amount'],
					'dp' => true
				) );

				// Format amount.
				$response_data['subscriptions'][ $count ]['info']['donor']['purchase_value'] = give_format_decimal( array(
					'donation_id' => $response_data['subscriptions'][ $count ]['info']['parent_payment_id'],
					'amount'      => $response_data['subscriptions'][ $count ]['info']['donor']['purchase_value'],
					'dp'          => true,
				) );

				$response_data['subscriptions'][ $count ]['info']['currency']   = give_get_payment_currency_code( $response_data['subscriptions'][ $count ]['info']['parent_payment_id'] );
				$response_data['subscriptions'][ $count ]['info']['form_title'] = get_the_title( $response_data['subscriptions'][ $count ]['info']['form_id'] );

				// Subscription Payments.
				$subscription_payments                                = $subscription->get_child_payments();
				$response_data['subscriptions'][ $count ]['payments'] = array();

				if ( ! empty( $subscription_payments ) ) :

					foreach ( $subscription_payments as $payment ) {

						array_push( $response_data['subscriptions'][ $count ]['payments'], array(
							'id'     => $payment->ID,
							'amount' => $payment->total,
							'date'   => date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) ),
							'status' => $payment->status_nicename,
						) );

					}

				endif;

				$count ++;

			}
		} elseif ( ! empty( $queried_c ) ) {

			$error['error'] = sprintf( __( 'No subscriptions found for %s!', 'give-recurring' ), $queried_c );

			return $error;

		} else {

			$error['error'] = __( 'No subscriptions found!', 'give-recurring' );

			return $error;

		}// End if().

		return $response_data;

	}
}
