<?php
/**
 * Form Related Admin Actions
 *
 * @package     Give
 * @subpackage  Admin/Form/Actions
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.17
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Sync form data with donations
 * Note: for internal use only
 *
 * @since 1.8.17
 *
 * @param $post_ID
 * @param $post_after
 * @param $post_before
 *
 * @todo need unit test
 */
function give_sync_form_data_with_donation( $post_ID, $post_after, $post_before ) {
	// Bailout.
	if ( 'give_forms' !== get_post_type( $post_ID ) ) {
		return;
	}

	$post_before = get_object_vars( $post_before );
	$post_after  = get_object_vars( $post_after );

	$changed_vars = array_diff( $post_after, $post_before );

	// unset( $changed_vars['post_modified'], $changed_vars['post_modified_gmt'] );

	// Bailout.
	if ( empty( $changed_vars ) ) {
		return;
	}

	$payments = new Give_Payments_Query( array( 'give_forms' => $post_ID, 'number' => - 1, 'fields' => 'ids' ) );
	$payments = $payments->get_payments();

	// Bailout.
	if ( ! empty( $payment ) ) {
		return;
	}

	// Update form title with async taks.
	if ( ! empty( $changed_vars['post_title'] ) ) {
		Give_Cron::add_async_event(
			'give_sync_form_title_with_donations',
			array(
				'form_id'       => $post_ID,
				'donations_ids' => wp_list_pluck( $payments, 'ID' ),
			)
		);
	}
}

add_action( 'post_updated', 'give_sync_form_data_with_donation', 999, 3 );

