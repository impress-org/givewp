<?php
/**
 * Deprecated Functions
 *
 * @description: All functions that have been deprecated.
 *
 * @package     Give
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Checks if Guest checkout is enabled for a particular donation form
 *
 * @since 1.0
 * @deprecated 1.4.1
 * @global    $give_options
 *
 * @param int $form_id
 *
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function give_no_guest_checkout( $form_id ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.4.1', null, $backtrace );

	$ret = get_post_meta( $form_id, '_give_logged_in_only', true );

	return (bool) apply_filters( 'give_no_guest_checkout', $ret );
}

/**
 * Reduces earnings and donation stats when a donation is refunded
 *
 * @since 1.0
 *
 * @param $payment_id
 * @param $new_status
 * @param $old_status
 *
 * @return void
 */
function give_undo_donation_on_refund( $payment_id, $new_status, $old_status ) {

	$backtrace = debug_backtrace();
	_give_deprecated_function( 'give_undo_purchase_on_refund', '1.5', 'Give_Payment->refund()', $backtrace );

	$payment = new Give_Payment( $payment_id );
	$payment->refund();

}