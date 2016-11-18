<?php
/**
 * Gateway Filters
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.7
 */

/**
 * Set notice for offline donation.
 *
 * @since 1.7
 *
 * @param string $notice
 * @param int    $id
 *
 * @return string
 */
function __give_offline_donation_receipt_status_notice( $notice, $id ) {
	$payment = new Give_Payment( $id );

	if ( 'offline' !== $payment->gateway ) {
		return $notice;
	}

	return give_output_error( 'Payment Pending: Please follow the instructions below to complete your donation.', false, 'warning' );
}

add_filter( 'give_receipt_status_notice', '__give_offline_donation_receipt_status_notice', 10, 2 );