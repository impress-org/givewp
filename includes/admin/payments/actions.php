<?php
/**
 * Admin Payment Actions
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, GiveWP
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
		wp_die( __( 'You do not have permission to edit payments.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	check_admin_referer( 'give_update_payment_details_nonce' );

	// Retrieve the payment ID.
	$payment_id = absint( $data['give_payment_id'] );

	/* @var Give_Payment $payment */
	$payment = new Give_Payment( $payment_id );

	$status = $data['give-payment-status'];
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

	$address = give_clean( $data['give-payment-address'][0] );

	$curr_total = $payment->total;
	$new_total  = give_maybe_sanitize_amount( ( ! empty( $data['give-payment-total'] ) ? $data['give-payment-total'] : 0 ) );
	$date       = date( 'Y-m-d', strtotime( give_clean( $data['give-payment-date'] ) ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_donor_id = sanitize_text_field( $data['give-current-donor'] );
	$new_donor_id  = sanitize_text_field( $data['donor-id'] );

	/**
	 * Fires before updating edited donation.
	 *
	 * @since 1.0
	 * @since 1.8.9 Changes hook name give_update_edited_purchase -> give_update_edited_donation
	 *
	 * @param int $payment_id The ID of the payment.
	 */
	do_action( 'give_update_edited_donation', $payment_id );

	$payment->date = $date;
	$payment->anonymous = isset( $data['give_anonymous_donation'] ) ? absint( $data['give_anonymous_donation'] ) : 0;


	$updated       = $payment->save();

	if ( 0 === $updated ) {
		wp_die( __( 'Error Updating Donation.', 'give' ), __( 'Error', 'give' ), array( 'response' => 400 ) );
	}

	$donor_changed = false;

	if ( isset( $data['give-new-donor'] ) && $data['give-new-donor'] == '1' ) {

		$email      = ! empty( $data['give-new-donor-email'] ) ? sanitize_text_field( $data['give-new-donor-email'] ) : '';
		$first_name = ! empty( $data['give-new-donor-first-name'] ) ? sanitize_text_field( $data['give-new-donor-first-name'] ) : '';
		$last_name  = ! empty( $data['give-new-donor-last-name'] ) ? sanitize_text_field( $data['give-new-donor-last-name'] ) : '';
		$names      = strip_tags( wp_unslash( trim( "{$first_name} {$last_name}" ) ) );

		if ( empty( $email ) || empty( $first_name ) ) {
			wp_die( __( 'New Donor requires first name and email address.', 'give' ), __( 'Error', 'give' ), array( 'response' => 400 ) );
		}

		$donor = new Give_Donor( $email );
		if ( empty( $donor->id ) ) {
			$donor_data = array( 'name' => $names, 'email' => $email );
			$user_id       = email_exists( $email );
			if ( false !== $user_id ) {
				$donor_data['user_id'] = $user_id;
			}

			if ( ! $donor->create( $donor_data ) ) {
				// Failed to create the new donor, assume the previous donor.
				$donor_changed = false;
				$donor         = new Give_Donor( $curr_donor_id );
				give_set_error( 'give-payment-new-donor-fail', __( 'Error creating new donor.', 'give' ) );
			}
		}

		// Create and Update Donor First Name and Last Name in Meta Fields.
		$donor->update_meta( '_give_donor_first_name', $first_name );
		$donor->update_meta( '_give_donor_last_name', $last_name );

		$new_donor_id = $donor->id;

		$previous_donor = new Give_Donor( $curr_donor_id );

		$donor_changed = true;

	} elseif ( $curr_donor_id !== $new_donor_id ) {

		$donor = new Give_Donor( $new_donor_id );
		$email    = $donor->email;
		$names    = $donor->name;

		$previous_donor = new Give_Donor( $curr_donor_id );

		$donor_changed = true;

	} else {
		$donor = new Give_Donor( $curr_donor_id );
		$email    = $donor->email;
		$names    = $donor->name;
	}

	if ( $donor_changed ) {

		// Setup first and last name from input values.
		$first_name = $donor->get_first_name();
		$last_name  = $donor->get_last_name();

		$payment->first_name = $first_name;
		$payment->last_name  = $last_name;

		// Remove the stats and payment from the previous donor and attach it to the new donor.
		$previous_donor->remove_payment( $payment_id, false );
		$donor->attach_payment( $payment_id, false );

		if ( 'publish' == $status ) {

			// Reduce previous user donation count and amount.
			$previous_donor->decrease_donation_count();
			$previous_donor->decrease_value( $curr_total );

			// If donation was completed adjust stats of new donors.
			$donor->increase_purchase_count();
			$donor->increase_value( $new_total );
		}

		$payment->customer_id = $donor->id;
	} else {

		if ( 'publish' === $status ) {
			// Update user donation stat.
			$donor->update_donation_value( $curr_total, $new_total );
		}
	}

	// Set new meta values.
	$payment->user_id    = $donor->user_id;
	$payment->email      = $donor->email;
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

			// Increase form earnings.
			give_increase_earnings( $payment->form_id, $difference, $payment->ID );
		} elseif ( $curr_total > $new_total ) {
			// Decrease if our new total is lower.
			$difference = $curr_total - $new_total;
			give_decrease_total_earnings( $difference );

			// Decrease form earnings.
			give_decrease_form_earnings( $payment->form_id, $difference, $payment->ID );
		}
	}

	$payment->save();

	// Get new give form ID.
	$new_form_id     = absint( $data['give-payment-form-select'] );
	$current_form_id = absint( $payment->get_meta( '_give_payment_form_id' ) );

	// We are adding payment transfer code in last to remove any conflict with above functionality.
	// For example: above code will automatically handle form stat (increase/decrease) when payment status changes.
	// Check if user want to transfer current payment to new give form id.
	if ( $new_form_id && $new_form_id != $current_form_id ) {

		// Get new give form title.
		$new_form_title = get_the_title( $new_form_id );

		// Update payment give form meta data.
		$payment->update_meta( '_give_payment_form_id', $new_form_id );
		$payment->update_meta( '_give_payment_form_title', $new_form_title );

		// Update price id payment metadata.
		if ( ! give_has_variable_prices( $new_form_id ) ) {
			$payment->update_meta( '_give_payment_price_id', '' );
		}

		// If donation was completed, adjust stats of forms.
		if ( 'publish' == $status ) {

			// Decrease sale of old give form. For other payment status.
			$current_form = new Give_Donate_Form( $current_form_id );
			$current_form->decrease_sales();
			$current_form->decrease_earnings( $curr_total, $payment->ID );

			// Increase sale of new give form.
			$new_form = new Give_Donate_Form( $new_form_id );
			$new_form->increase_sales();
			$new_form->increase_earnings( $new_total, $payment->ID );
		}

		// Re setup payment to update new meta value in object.
		$payment->update_payment_setup( $payment->ID );

		// Update form id in payment logs.
		Give()->async_process->data( array(
			'data' => array( $new_form_id, $payment_id ),
			'hook' => 'give_update_log_form_id',
		) )->dispatch();
	}

	// Update price id if current form is variable form.
	/* @var Give_Donate_Form $form */
	$form = new Give_Donate_Form( $payment->form_id );

	if ( isset( $data['give-variable-price'] ) && $form->has_variable_prices() ) {

		// Get payment meta data.
		$payment_meta = $payment->get_meta();

		$price_info = array();
		$price_id = '';

		// Get price info
		if( 0 <= $data['give-variable-price'] ) {
			foreach ( $form->prices as $variable_price ) {
				if( $new_total === give_maybe_sanitize_amount( $variable_price['_give_amount'] ) ) {
					$price_info = $variable_price;
					break;
				}
			}
		}

		// Set price id.
		if( ! empty( $price_info ) ) {
			$price_id = $data['give-variable-price'];

			if( $data['give-variable-price'] !== $price_info['_give_id']['level_id'] ) {
				// Set price id to amount match.
				$price_id = $price_info['_give_id']['level_id'];
			}

		} elseif( $form->is_custom_price_mode() ){
			$price_id = 'custom';
		}

		// Update payment meta data.
		$payment_meta['price_id'] = $price_id;

		// Update payment give form meta data.
		$payment->update_meta( '_give_payment_price_id', $price_id );
		$payment->update_meta( '_give_payment_meta', $payment_meta );

		// Re setup payment to update new meta value in object.
		$payment->update_payment_setup( $payment->ID );
	}

	$comment_id                   = isset( $data['give_comment_id'] ) ? absint( $data['give_comment_id'] ) : 0;
	$has_anonymous_setting_field = give_is_anonymous_donation_field_enabled( $payment->form_id );

	if ( $has_anonymous_setting_field ) {
		give_update_meta( $payment->ID, '_give_anonymous_donation', $payment->anonymous );
	}

	// Update comment.
	if ( give_is_donor_comment_field_enabled( $payment->form_id ) ) {
		// We are access comment directly from $_POST because comment formatting remove because of give_clean in give_post_actions.
		$data['give_comment'] = trim( $_POST['give_comment'] );

		if ( empty( $data['give_comment'] ) ) {
			// Delete comment if empty
			Give_Comment::delete( $comment_id, $payment_id, 'payment' );
			$comment_id = 0;

		} else {
			$comment_args = array(
				'comment_author_email' => $payment->email
			);

			if ( $comment_id ) {
				$comment_args['comment_ID'] = $comment_id;
			}

			$comment_id = give_insert_donor_donation_comment(
				$payment->ID,
				$payment->donor_id,
				$data['give_comment'],
				$comment_args
			);
		}
	}

	// Check if payment status is not completed then update the goal progress for donation form.
	if ( 'publish' !== $status ) {
		give_update_goal_progress( $form->ID );
	}

	/**
	 * Fires after updating edited donation.
	 *
	 * @since 1.0
	 * @since 1.8.9 Changes hook name give_updated_edited_purchase -> give_updated_edited_donation
	 *
	 * @param int $payment_id The ID of the payment.
	 */
	do_action( 'give_updated_edited_donation', $payment_id );

	wp_safe_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&give-messages[]=payment-updated&id=' . $payment_id ) );
	exit;
}

add_action( 'give_update_payment_details', 'give_update_payment_details' );

/**
 * Trigger a Donation Deletion.
 *
 * @since 1.0
 *
 * @param array $data Arguments passed.
 *
 * @return void
 */
function give_trigger_donation_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'give_donation_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'edit_give_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit payments.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		give_delete_donation( $payment_id );
		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&give-messages[]=donation-deleted' ) );
		give_die();
	}
}

add_action( 'give_delete_payment', 'give_trigger_donation_delete' );

/**
 * AJAX Store Donation Note
 */
function give_ajax_store_payment_note() {
	$payment_id = absint( $_POST['payment_id'] );
	$note       = wp_kses( $_POST['note'], array() );
	$note_type  = give_clean( $_POST['type'] );

	if ( ! current_user_can( 'edit_give_payments', $payment_id ) ) {
		wp_die( __( 'You do not have permission to edit payments.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	if ( empty( $payment_id ) || empty( $note ) ) {
		die( '-1' );
	}

	if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
		// Backward compatibility.
		$note_id = give_insert_payment_note( $payment_id, $note );
	} else {
		$note_id = Give()->comment->db->add(
			array(
				'comment_parent'  => $payment_id,
				'user_id'         => get_current_user_id(),
				'comment_content' => $note,
				'comment_type'    => 'donation',
			)
		);
	}

	if( $note_id && $note_type ) {

		if( ! give_has_upgrade_completed('v230_move_donor_note' ) ) {
			add_comment_meta( $note_id, 'note_type', $note_type, true );
		} else{
			Give()->comment->db_meta->update_meta( $note_id, 'note_type', $note_type );
		}

		/**
		 * Fire the action
		 *
		 * @since 2.3.0
		 */
		do_action( 'give_donor-note_email_notification', $note_id, $payment_id );
	}

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
		wp_die( __( 'You do not have permission to edit payments.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	$edit_order_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&give-messages[]=donation-note-deleted&id=' . absint( $data['payment_id'] ) );

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
		wp_die( __( 'You do not have permission to edit payments.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	if ( give_delete_payment_note( $_POST['note_id'], $_POST['payment_id'] ) ) {
		die( '1' );
	} else {
		die( '-1' );
	}

}

add_action( 'wp_ajax_give_delete_payment_note', 'give_ajax_delete_payment_note' );
