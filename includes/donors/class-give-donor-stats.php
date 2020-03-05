<?php
/**
 * Donor stats
 *
 * Note: Currently wr are working on this API. This is internal use only
 *
 * @package     Give
 * @subpackage  Classes/Donor/Stats
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.2.0
 */
class Give_Donor_Stats {
	/**
	 * Instance.
	 *
	 * @since  2.2.0
	 * @access private
	 * @var
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.2.0
	 * @access public
	 * @return Give_Donor_Stats
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 *  Get total donated amount
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function donated( $args = array() ) {
		global $wpdb;
		$donation_id_col = Give()->payment_meta->get_meta_type() . '_id';

		$donated_amount = 0;

		if ( empty( $args['donor'] ) ) {
			return $donated_amount;
		}

		$args['output'] = 'posts';
		$args['status'] = 'publish';
		$args['fields'] = 'ids';
		$args['number'] = - 1;

		$donation_query  = new Give_Payments_Query( $args );
		$donations       = $donation_query->get_payments();
		$donation_id_str = implode( '\',\'', $donations );

		$query = "SELECT {$donation_id_col} as id, meta_value as total
					FROM {$wpdb->donationmeta}
					WHERE meta_key='_give_payment_total'
					AND {$donation_id_col} IN ('{$donation_id_str}')";

		$donated_amounts = $wpdb->get_results( $query, ARRAY_A );

		if ( ! empty( $donated_amounts ) ) {
			foreach ( $donated_amounts as $donation ) {
				// Do not include anonymous donation in calculation.
				if ( give_is_anonymous_donation( $donation['id'] ) ) {
					continue;
				}

				$currency_code = give_get_payment_currency_code( $donation['id'] );

				/**
				 * Filter the donation amount
				 * Note: this filter documented in payments/functions.php:give_donation_amount()
				 *
				 * @since 2.1
				 */
				$formatted_amount = apply_filters(
					'give_donation_amount',
					give_format_amount( $donation['total'], array( 'currency' => $currency_code ) ),
					$donation['total'],
					$donation['id'],
					array(
						'type'     => 'stats',
						'currency' => false,
						'amount'   => false,
					)
				);

				$donated_amount += (float) give_maybe_sanitize_amount( $formatted_amount, array( 'currency' => $currency_code ) );
			}
		}

		return $donated_amount;
	}
}
