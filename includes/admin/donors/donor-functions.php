<?php
/**
 * Donors
 *
 * @package    Give
 * @subpackage Admin/Donors
 * @copyright  Copyright (c) 2016, WordImpress
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a view for the single donor view.
 *
 * @param array $views An array of existing views.
 *
 * @since 1.0
 *
 * @return array        The altered list of views.
 */
function give_register_default_donor_views( $views ) {

	$default_views = array(
		'overview' => 'give_donor_view',
		'delete'   => 'give_donor_delete_view',
		'notes'    => 'give_donor_notes_view',
	);

	return array_merge( $views, $default_views );

}

add_filter( 'give_donor_views', 'give_register_default_donor_views', 1, 1 );

/**
 * Register a tab for the single donor view.
 *
 * @param array $tabs An array of existing tabs.
 *
 * @since 1.0
 *
 * @return array       The altered list of tabs
 */
function give_register_default_donor_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array(
			'dashicon' => 'dashicons-admin-users',
			'title' => __( 'Donor Profile', 'give' ),
		),
		'notes'    => array(
			'dashicon' => 'dashicons-admin-comments',
			'title' => __( 'Donor Notes', 'give' ),
		),
	);

	return array_merge( $tabs, $default_tabs );
}

add_filter( 'give_donor_tabs', 'give_register_default_donor_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom.
 *
 * @param array $tabs An array of existing tabs.
 *
 * @since 1.0
 *
 * @return array       The altered list of tabs, with 'delete' at the bottom.
 */
function give_register_delete_donor_tab( $tabs ) {

	$tabs['delete'] = array(
		'dashicon' => 'dashicons-trash',
		'title'    => __( 'Delete Donor', 'give' ),
	);

	return $tabs;
}

add_filter( 'give_donor_tabs', 'give_register_delete_donor_tab', PHP_INT_MAX, 1 );

/**
 * Connect and Reconnect Donor with User profile.
 *
 * @param object $donor      Donor Object.
 * @param array  $donor_data Donor Post Variables.
 * @param array  $address    Address Information.
 *
 * @since 1.8.14
 *
 * @return array
 */
function give_connect_user_donor_profile( $donor, $donor_data, $address ) {

	$donor_id         = $donor->id;
	$previous_user_id = $donor->user_id;

	/**
	 * Fires before editing a donor.
	 *
	 * @param int   $donor_id   The ID of the donor.
	 * @param array $donor_data The donor data.
	 * @param array $address    The donor's address.
	 *
	 * @since 1.0
	 */
	do_action( 'give_pre_edit_donor', $donor_id, $donor_data, $address );

	$output = array();

	if ( $donor->update( $donor_data ) ) {

		if ( ! empty( $donor->user_id ) && $donor->user_id > 0 ) {
			update_user_meta( $donor->user_id, '_give_user_address', $address );
		}

		// Update some donation meta if we need to.
		$payments_array = explode( ',', $donor->payment_ids );

		if ( $donor->user_id !== $previous_user_id ) {
			foreach ( $payments_array as $payment_id ) {
				give_update_payment_meta( $payment_id, '_give_payment_user_id', $donor->user_id );
			}
		}

		// Fetch disconnected user id, if exists.
		$disconnected_user_id = $donor->get_meta( '_give_disconnected_user_id', true );

		// Flag User and Donor Disconnection.
		delete_user_meta( $disconnected_user_id, '_give_is_donor_disconnected' );

		// Check whether the disconnected user id and the reconnected user id are same or not.
		// If both are same then delete user id store in donor meta.
		if( $donor_data['user_id'] === $disconnected_user_id ) {
			delete_user_meta( $disconnected_user_id, '_give_disconnected_donor_id' );
			$donor->delete_meta( '_give_disconnected_user_id' );
		}

		$output['success']       = true;
		$donor_data              = array_merge( $donor_data, $address );
		$output['customer_info'] = $donor_data;

	} else {

		$output['success'] = false;

	}

	/**
	 * Fires after editing a donor.
	 *
	 * @param int   $donor_id   The ID of the donor.
	 * @param array $donor_data The donor data.
	 *
	 * @since 1.0
	 */
	do_action( 'give_post_edit_donor', $donor_id, $donor_data );


	return $output;
}

/**
 * Delete Donor using Bulk Actions.
 *
 * @since 1.8.17
 *
 * @return bool
 */
function give_delete_donor( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die( __( 'You do not have permission to delete donors.', 'give' ), __( 'Error', 'give' ), array(
			'response' => 403,
		) );
	}

	$donor_ids = ( is_array( $args['donor_ids'] ) && count( $args['donor_ids'] ) > 0 ) ? $args['donor_ids'] : array();
	$nonce     = $args['_wpnonce'];

	// Verify Nonce for deleting bulk donors.
	if ( ! wp_verify_nonce( $nonce, 'delete-bulk-donors' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?', 'give' ), __( 'Error', 'give' ), array(
			'response' => 400,
		) );
	}

	$give_message = array();
	$redirect_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors' );

	if( count( $donor_ids ) > 0 ) {
		foreach ( $donor_ids as $donor_id ) {
			$donor = new Give_Donor( $donor_id );

			if ( $donor->id > 0 ) {
				$donation_ids = explode( ',', $donor->payment_ids );
				//$donor_deleted = Give()->donors->delete( $donor->id );
				$donor_deleted = false;
				if ( $donor_deleted ) {

					// Remove all donations, logs, etc.
					foreach ( $donation_ids as $donation_id ) {
						give_delete_donation( $donation_id );
					}

					$give_message = 'delete-donor';
					//$give_message['delete-donor']['count'] = 0;

				} else {
					$give_message = 'donor-delete-failed';
					//$give_message['donor-delete-failed']['count'] = 0;
					//$redirect_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&give-message=donor-delete-failed' );

				}

			} else {
				$give_message = 'invalid-donor-id';
				//$give_message['invalid-donor-id']['count']   = 0;
				//$give_message = 'invalid-donor-id';
				//$redirect_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&give-message=invalid-donor-id' );
			}
		}
	}
// $redirect_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&give-message=invalid-donor-id' );
	//echo "<pre>"; print_R($give_message);
	//add_query_arg( 'give-message', $give_message, $redirect_url );
	return $give_message;
	give_die();


}

add_filter( 'handle_bulk_actions-edit-post', 'my_bulk_action_handler', 10, 3 );

function my_bulk_action_handler( $redirect_to, $action, $post_ids ) {
	var_dump($action); give_die();
}

//add_action( 'give_delete_donor', 'give_delete_donor' );

function give_bulk_delete_donor() {
	$donor_ids = $_POST['donor_ids'];
	?>
	<td colspan="6" class="colspanchange">

		<fieldset class="inline-edit-col-left">
			<legend class="inline-edit-legend"><?php _e( 'BULK DELETE', 'give' ); ?></legend>
			<div class="inline-edit-col">
				<div id="bulk-title-div">
					<div id="bulk-titles">
						<?php
						if ( count( $donor_ids ) > 0 ) {
							foreach( $donor_ids as $donor_id ) {
								?>
								<div id="give-donor-<?php echo $donor_id; ?>">
									<a data-id="<?php echo $donor_id; ?>" class="give-skip-donor" title="Remove From Bulk Delete">X</a>
									<input type="hidden" name="donor[]" value="<?php echo $donor_id; ?>" />
									<?php echo give_get_donor_name_by( $donor_id, 'donor' ); ?>
								</div>
								<?php
							}
						} else {
							_e( 'No Donors', 'give' );
						}
						?>
					</div>
				</div>
		</fieldset>

		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<input id="give-delete-donor-confirm" type="checkbox" name="give-delete-donor-confirm"/>
					<?php _e( 'Are you sure you want to delete the selected donor(s)?', 'give' ); ?>
				</label>
				<label>
					<input id="give-delete-donor-records" type="checkbox" name="give-delete-donor-records"/>
					<?php _e( 'Delete all associated donations and records?', 'give' ); ?>
				</label>
			</div>
		</fieldset>

		<p class="submit inline-edit-save">
			<button type="button" class="button cancel alignleft">Cancel</button>
			<input type="button" name="bulk_delete" id="give-bulk-delete" class="button button-primary alignright" value="Delete">
			<br class="clear">
		</p>
	</td>

	<?php
}

//add_action( 'wp_ajax_give_bulk_delete_donor' ,'give_bulk_delete_donor' );
//add_action( 'wp_ajax_nopriv_give_bulk_delete_donor' ,'give_bulk_delete_donor' );