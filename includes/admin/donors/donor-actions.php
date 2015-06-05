<?php
/**
 * Donor Actions
 *
 * @package     Give
 * @subpackage  Admin/Donors
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes a custom edit
 *
 * @since  2.3
 * @param  array $args The $_POST array being passeed
 * @return array $output Response messages
 */
function give_edit_donor( $args ) {
	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'give' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$donor_info = $args['donorinfo'];
	$donor_id   = (int)$args['donorinfo']['id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-donor' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'give' ) );
	}

	$donor = new Give_Donor( $donor_id );
	if ( empty( $donor->id ) ) {
		return false;
	}

	$defaults = array(
		'name'    => '',
		'email'   => '',
		'user_id' => 0
	);

	$donor_info = wp_parse_args( $donor_info, $defaults );

	if ( ! is_email( $donor_info['email'] ) ) {
		give_set_error( 'give-invalid-email', __( 'Please enter a valid email address.', 'give' ) );
	}

	if ( (int) $donor_info['user_id'] != (int) $donor->user_id ) {

		// Make sure we don't already have this user attached to a donor
		if ( ! empty( $donor_info['user_id'] ) && false !== Give()->donors->get_donor_by( 'user_id', $donor_info['user_id'] ) ) {
			give_set_error( 'give-invalid-donor-user_id', sprintf( __( 'The User ID %d is already associated with a different donor.', 'give' ), $donor_info['user_id'] ) );
		}

		// Make sure it's actually a user
		$user = get_user_by( 'id', $donor_info['user_id'] );
		if ( ! empty( $donor_info['user_id'] ) && false === $user ) {
			give_set_error( 'give-invalid-user_id', sprintf( __( 'The User ID %d does not exist. Please assign an existing user.', 'give' ), $donor_info['user_id'] ) );
		}

	}

	// Record this for later
	$previous_user_id  = $donor->user_id;

	if ( give_get_errors() ) {
		return;
	}

	// Setup the donor address, if present
	$address = array();
	if ( intval( $donor_info['user_id'] ) > 0 ) {

		$current_address = get_user_meta( $donor_info['user_id'], '_give_user_address', true );

		if ( false === $current_address ) {
			$address['line1']   = isset( $donor_info['line1'] )   ? $donor_info['line1']   : '';
			$address['line2']   = isset( $donor_info['line2'] )   ? $donor_info['line2']   : '';
			$address['city']    = isset( $donor_info['city'] )    ? $donor_info['city']    : '';
			$address['country'] = isset( $donor_info['country'] ) ? $donor_info['country'] : '';
			$address['zip']     = isset( $donor_info['zip'] )     ? $donor_info['zip']     : '';
			$address['state']   = isset( $donor_info['state'] )   ? $donor_info['state']   : '';
		} else {
			$current_address    = wp_parse_args( $current_address, array( 'line1', 'line2', 'city', 'zip', 'state', 'country' ) );
			$address['line1']   = isset( $donor_info['line1'] )   ? $donor_info['line1']   : $current_address['line1']  ;
			$address['line2']   = isset( $donor_info['line2'] )   ? $donor_info['line2']   : $current_address['line2']  ;
			$address['city']    = isset( $donor_info['city'] )    ? $donor_info['city']    : $current_address['city']   ;
			$address['country'] = isset( $donor_info['country'] ) ? $donor_info['country'] : $current_address['country'];
			$address['zip']     = isset( $donor_info['zip'] )     ? $donor_info['zip']     : $current_address['zip']    ;
			$address['state']   = isset( $donor_info['state'] )   ? $donor_info['state']   : $current_address['state']  ;
		}

	}

	// Sanitize the inputs
	$donor_data            = array();
	$donor_data['name']    = strip_tags( stripslashes( $donor_info['name'] ) );
	$donor_data['email']   = $donor_info['email'];
	$donor_data['user_id'] = $donor_info['user_id'];

	$donor_data = apply_filters( 'give_edit_donor_info', $donor_data, $donor_id );
	$address       = apply_filters( 'give_edit_donor_address', $address, $donor_id );

	$donor_data = array_map( 'sanitize_text_field', $donor_data );
	$address       = array_map( 'sanitize_text_field', $address );

	do_action( 'give_pre_edit_donor', $donor_id, $donor_data, $address );

	$output         = array();
	$previous_email = $donor->email;

	if ( $donor->update( $donor_data ) ) {

		if ( ! empty( $donor->user_id ) && $donor->user_id > 0 ) {
			update_user_meta( $donor->user_id, '_give_user_address', $address );
		}

		// Update some payment meta if we need to
		$payments_array = explode( ',', $donor->payment_ids );

		if ( $donor->email != $previous_email ) {
			foreach ( $payments_array as $payment_id ) {
				give_update_payment_meta( $payment_id, 'email', $donor->email );
			}
		}

		if ( $donor->user_id != $previous_user_id ) {
			foreach ( $payments_array as $payment_id ) {
				give_update_payment_meta( $payment_id, '_give_payment_user_id', $donor->user_id );
			}
		}

		$output['success']       = true;
		$donor_data           = array_merge( $donor_data, $address );
		$output['donor_info'] = $donor_data;

	} else {

		$output['success'] = false;

	}

	do_action( 'give_post_edit_donor', $donor_id, $donor_data );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'give_edit-donor', 'give_edit_donor', 10, 1 );

/**
 * Save a donor note being added
 *
 * @since  2.3
 * @param  array $args The $_POST array being passeed
 * @return int         The Note ID that was saved, or 0 if nothing was saved
 */
function give_donor_save_note( $args ) {

	$donor_view_role = apply_filters( 'give_view_donors_role', 'view_shop_reports' );

	if ( ! is_admin() || ! current_user_can( $donor_view_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'give' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$donor_note = trim( sanitize_text_field( $args['donor_note'] ) );
	$donor_id   = (int)$args['donor_id'];
	$nonce         = $args['add_donor_note_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-donor-note' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'give' ) );
	}

	if ( empty( $donor_note ) ) {
		give_set_error( 'empty-donor-note', __( 'A note is required', 'give' ) );
	}

	if ( give_get_errors() ) {
		return;
	}

	$donor = new Give_Donor( $donor_id );
	$new_note = $donor->add_note( $donor_note );

	do_action( 'give_pre_insert_donor_note', $donor_id, $new_note );

	if ( ! empty( $new_note ) && ! empty( $donor->id ) ) {

		ob_start();
		?>
		<div class="donor-note-wrapper dashboard-comment-wrap comment-item">
			<span class="note-content-wrap">
				<?php echo stripslashes( $new_note ); ?>
			</span>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $output;
			exit;
		}

		return $new_note;

	}

	return false;

}
add_action( 'give_add-donor-note', 'give_donor_save_note', 10, 1 );

/**
 * Delete a donor
 *
 * @since  2.3
 * @param  array $args The $_POST array being passeed
 * @return int         Wether it was a successful deletion
 */
function give_donor_delete( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to delete this donor.', 'give' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$donor_id   = (int)$args['donor_id'];
	$confirm       = ! empty( $args['give-donor-delete-confirm'] ) ? true : false;
	$remove_data   = ! empty( $args['give-donor-delete-records'] ) ? true : false;
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'delete-donor' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'give' ) );
	}

	if ( ! $confirm ) {
		give_set_error( 'donor-delete-no-confirm', __( 'Please confirm you want to delete this donor', 'give' ) );
	}

	if ( give_get_errors() ) {
		wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor_id ) );
		exit;
	}

	$donor = new Give_Donor( $donor_id );

	do_action( 'give_pre_delete_donor', $donor_id, $confirm, $remove_data );

	$success = false;

	if ( $donor->id > 0 ) {

		$payments_array = explode( ',', $donor->payment_ids );
		$success        = Give()->donors->delete( $donor->id );

		if ( $success ) {

			if ( $remove_data ) {

				// Remove all payments, logs, etc
				foreach ( $payments_array as $payment_id ) {
					give_delete_purchase( $payment_id, false, true );
				}

			} else {

				// Just set the payments to donor_id of 0
				foreach ( $payments_array as $payment_id ) {
					give_update_payment_meta( $payment_id, '_give_payment_donor_id', 0 );
				}

			}

			$redirect = admin_url( 'edit.php?post_type=give_forms&page=give-donors&give-message=donor-deleted' );

		} else {

			give_set_error( 'give-donor-delete-failed', __( 'Error deleting donor', 'give' ) );
			$redirect = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=delete&id=' . $donor_id );

		}

	} else {

		give_set_error( 'give-donor-delete-invalid-id', __( 'Invalid Donor ID', 'give' ) );
		$redirect = admin_url( 'edit.php?post_type=give_forms&page=give-donors' );

	}

	wp_redirect( $redirect );
	exit;

}
add_action( 'give_delete-donor', 'give_donor_delete', 10, 1 );

/**
 * Disconnect a user ID from a donor
 *
 * @since  2.3
 * @param  array $args Array of arguements
 * @return bool        If the disconnect was sucessful
 */
function give_disconnect_donor_user_id( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_shop_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to edit this donor.', 'give' ) );
	}

	if ( empty( $args ) ) {
		return;
	}

	$donor_id   = (int)$args['donor_id'];
	$nonce         = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-donor' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'give' ) );
	}

	$donor = new Give_Donor( $donor_id );
	if ( empty( $donor->id ) ) {
		return false;
	}

	do_action( 'give_pre_donor_disconnect_user_id', $donor_id, $donor->user_id );

	$donor_args = array( 'user_id' => 0 );

	if ( $donor->update( $donor_args ) ) {
		global $wpdb;

		if ( ! empty( $donor->payment_ids ) ) {
			$wpdb->query( "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = '_give_payment_user_id' AND post_id IN ( $donor->payment_ids )" );
		}

		$output['success'] = true;

	} else {

		$output['success'] = false;
		give_set_error( 'give-disconnect-user-fail', __( 'Failed to disconnect user from donor', 'give' ) );
	}

	do_action( 'give_post_donor_disconnect_user_id', $donor_id );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}
add_action( 'give_disconnect-userid', 'give_disconnect_donor_user_id', 10, 1 );
