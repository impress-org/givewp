<?php
/**
 * Admin Payment Actions
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * Process the payment details edit
 *
 * @since  1.0
 * @access private
 *
 * @param array $data Donation data.
 *
 * @return      void
 */
function give_update_payment_details( $data ) {

	if ( ! current_user_can( 'edit_give_payments', $data['give_payment_id'] ) ) {
		wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	check_admin_referer( 'give_update_payment_details_nonce' );

	// Retrieve the payment ID.
	$payment_id = absint( $data['give_payment_id'] );

	/* @var Give_Payment $payment */
	$payment = new Give_Payment( $payment_id );

	// Retrieve existing payment meta.
	$meta      = $payment->get_meta();
	$user_info = $payment->user_info;

	$status = $data['give-payment-status'];
	$date   = sanitize_text_field( $data['give-payment-date'] );
	$hour   = sanitize_text_field( $data['give-payment-time-hour'] );

	// Restrict to our high and low.
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute = sanitize_text_field( $data['give-payment-time-min'] );

	// Restrict to our high and low.
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	$address = array_map( 'trim', $data['give-payment-address'][0] );

	$curr_total = give_sanitize_amount( $payment->total );
	$new_total  = give_sanitize_amount( $data['give-payment-total'] );
	$date       = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_customer_id = sanitize_text_field( $data['give-current-customer'] );
	$new_customer_id  = sanitize_text_field( $data['customer-id'] );

	/**
	 * Fires before updating edited donation.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id The ID of the payment.
	 */
	do_action( 'give_update_edited_purchase', $payment_id );

	$payment->date = $date;
	$updated       = $payment->save();

	if ( 0 === $updated ) {
		wp_die( esc_html__( 'Error Updating Donation.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 400 ) );
	}

	$customer_changed = false;

	if ( isset( $data['give-new-customer'] ) && $data['give-new-customer'] == '1' ) {

		$email = isset( $data['give-new-customer-email'] ) ? sanitize_text_field( $data['give-new-customer-email'] ) : '';
		$names = isset( $data['give-new-customer-name'] ) ? sanitize_text_field( $data['give-new-customer-name'] ) : '';

		if ( empty( $email ) || empty( $names ) ) {
			wp_die( esc_html__( 'New Customers require a name and email address.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 400 ) );
		}

		$customer = new Give_Customer( $email );
		if ( empty( $customer->id ) ) {
			$customer_data = array( 'name' => $names, 'email' => $email );
			$user_id       = email_exists( $email );
			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				// Failed to crete the new donor, assume the previous donor.
				$customer_changed = false;
				$customer         = new Give_Customer( $curr_customer_id );
				give_set_error( 'give-payment-new-customer-fail', esc_html__( 'Error creating new donor.', 'give' ) );
			}
		}

		$new_customer_id = $customer->id;

		$previous_customer = new Give_Customer( $curr_customer_id );

		$customer_changed = true;

	} elseif ( $curr_customer_id !== $new_customer_id ) {

		$customer = new Give_Customer( $new_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

		$previous_customer = new Give_Customer( $curr_customer_id );

		$customer_changed = true;

	} else {

		$customer = new Give_Customer( $curr_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

	}

	// Setup first and last name from input values.
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';
	if ( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	if ( $customer_changed ) {

		// Remove the stats and payment from the previous customer and attach it to the new customer.
		$previous_customer->remove_payment( $payment_id, false );
		$customer->attach_payment( $payment_id, false );

		if ( 'publish' == $status ) {

			// Reduce previous user donation count and amount.
			$previous_customer->decrease_purchase_count();
			$previous_customer->decrease_value( $curr_total );

			// If donation was completed adjust stats of new customers.
			$customer->increase_purchase_count();
			$customer->increase_value( $new_total );
		}

		$payment->customer_id = $customer->id;
	} else {

		if ( 'publish' === $status ) {
			// Update user donation stat.
			$customer->update_donation_value( $curr_total, $new_total );
		}
	}

	// Set new meta values.
	$payment->user_id    = $customer->user_id;
	$payment->email      = $customer->email;
	$payment->first_name = $first_name;
	$payment->last_name  = $last_name;
	$payment->address    = $address;
	$payment->total      = $new_total;

	// Check for payment notes.
	if ( ! empty( $data['give-payment-note'] ) ) {

		$note = wp_kses( $data['give-payment-note'], array() );
		give_insert_payment_note( $payment_id, $note );

	}

	// Set new status.
	$payment->status = $status;

	// Adjust total store earnings if the payment total has been changed.
	if ( $new_total !== $curr_total && 'publish' == $status ) {

		if ( $new_total > $curr_total ) {
			// Increase if our new total is higher.
			$difference = $new_total - $curr_total;
			give_increase_total_earnings( $difference );

		} elseif ( $curr_total > $new_total ) {
			// Decrease if our new total is lower.
			$difference = $curr_total - $new_total;
			give_decrease_total_earnings( $difference );

		}
	}

	$payment->save();

	// Get new give form ID.
	$new_form_id     = absint( $data['forms'] );
	$current_form_id = absint( $payment->get_meta( '_give_payment_form_id' ) );

	// We are adding payment transfer code in last to remove any conflict with above functionality.
	// For example: above code will automatically handle form stat (increase/decrease) when payment status changes.
	// Check if user want to transfer current payment to new give form id.
	if ( $new_form_id != $current_form_id ) {

		// Get new give form title.
		$new_form_title = get_the_title( $new_form_id );

		// Update new give form data in payment data.
		$payment_meta               = $payment->get_meta();
		$payment_meta['form_title'] = $new_form_title;
		$payment_meta['form_id']    = $new_form_id;

		// Update price id post meta data for set donation form.
		if ( ! give_has_variable_prices( $new_form_id ) ) {
			$payment_meta['price_id'] = '';
		}

		// Update payment give form meta data.
		$payment->update_meta( '_give_payment_form_id', $new_form_id );
		$payment->update_meta( '_give_payment_form_title', $new_form_title );
		$payment->update_meta( '_give_payment_meta', $payment_meta );

		// Update price id payment metadata.
		if ( ! give_has_variable_prices( $new_form_id ) ) {
			$payment->update_meta( '_give_payment_price_id', '' );
		}

		// If donation was completed, adjust stats of forms.
		if ( 'publish' == $status ) {

			// Decrease sale of old give form. For other payment status.
			$current_form = new Give_Donate_Form( $current_form_id );
			$current_form->decrease_sales();
			$current_form->decrease_earnings( $curr_total );

			// Increase sale of new give form.
			$new_form = new Give_Donate_Form( $new_form_id );
			$new_form->increase_sales();
			$new_form->increase_earnings( $new_total );
		}

		// Re setup payment to update new meta value in object.
		$payment->update_payment_setup( $payment->ID );
	}

	// Update price id if current form is variable form.
	if ( ! empty( $data['give-variable-price'] ) && give_has_variable_prices( $payment->form_id ) ) {

		// Get payment meta data.
		$payment_meta = $payment->get_meta();

		// Set payment id to empty string if variable price id is negative ( i.e. custom amount feature enabled ).
		$data['give-variable-price'] = ( 'custom' === $data['give-variable-price'] ) ? 'custom' : ( 0 < $data['give-variable-price'] ) ? $data['give-variable-price'] : '';

		// Update payment meta data.
		$payment_meta['price_id'] = $data['give-variable-price'];

		// Update payment give form meta data.
		$payment->update_meta( '_give_payment_price_id', $data['give-variable-price'] );
		$payment->update_meta( '_give_payment_meta', $payment_meta );

		// Re setup payment to update new meta value in object.
		$payment->update_payment_setup( $payment->ID );
	}

	/**
	 * Fires after updating edited donation.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id The ID of the payment.
	 */
	do_action( 'give_updated_edited_purchase', $payment_id );

	wp_safe_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&give-message=payment-updated&id=' . $payment_id ) );
	exit;
}

add_action( 'give_update_payment_details', 'give_update_payment_details' );

/**
 * Trigger a Donation Deletion
 *
 * @since 1.0
 *
 * @param array $data Arguments passed
 *
 * @return void
 */
function give_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'give_donation_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'edit_give_payments', $payment_id ) ) {
			wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		give_delete_purchase( $payment_id );
		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&give-message=donation_deleted' ) );
		give_die();
	}
}

add_action( 'give_delete_payment', 'give_trigger_purchase_delete' );

/**
 * AJAX Store Donation Note
 */
function give_ajax_store_payment_note() {

	$payment_id = absint( $_POST['payment_id'] );
	$note       = wp_kses( $_POST['note'], array() );

	if ( ! current_user_can( 'edit_give_payments', $payment_id ) ) {
		wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	if ( empty( $payment_id ) ) {
		die( '-1' );
	}

	if ( empty( $note ) ) {
		die( '-1' );
	}

	$note_id = give_insert_payment_note( $payment_id, $note );
	die( give_get_payment_note_html( $note_id ) );
}

add_action( 'wp_ajax_give_insert_payment_note', 'give_ajax_store_payment_note' );

/**
 * Triggers a donation note deletion without ajax
 *
 * @since 1.0
 *
 * @param array $data Arguments passed
 *
 * @return void
 */
function give_trigger_payment_note_deletion( $data ) {

	if ( ! wp_verify_nonce( $data['_wpnonce'], 'give_delete_payment_note_' . $data['note_id'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_give_payments', $data['payment_id'] ) ) {
		wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	$edit_order_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&give-message=donation-note-deleted&id=' . absint( $data['payment_id'] ) );

	give_delete_payment_note( $data['note_id'], $data['payment_id'] );

	wp_redirect( $edit_order_url );
}

add_action( 'give_delete_payment_note', 'give_trigger_payment_note_deletion' );

/**
 * Delete a payment note deletion with ajax
 *
 * @since 1.0
 *
 * @return void
 */
function give_ajax_delete_payment_note() {

	if ( ! current_user_can( 'edit_give_payments', $_POST['payment_id'] ) ) {
		wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	if ( give_delete_payment_note( $_POST['note_id'], $_POST['payment_id'] ) ) {
		die( '1' );
	} else {
		die( '-1' );
	}

}

add_action( 'wp_ajax_give_delete_payment_note', 'give_ajax_delete_payment_note' );
