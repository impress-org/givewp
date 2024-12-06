<?php
/**
 * Donors
 *
 * @package     Give
 * @subpackage  Admin/Donors
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processes a donor edit.
 *
 * @since 3.7.0 Add support to the "phone" field
 * @since      1.0
 *
 * @param array $args The $_POST array being passed.
 *
 * @return array|bool $output Response messages
 * @throws Exception
 */
function give_edit_donor( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die(
			esc_html__( 'You do not have permission to edit this donor.', 'give' ),
			esc_html__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	if ( empty( $args ) ) {
		return false;
	}

	// Sanitize Data.
	$args = give_clean( $args );

	$args = wp_parse_args(
		$args,
		array(
			'give_anonymous_donor' => 0,
		)
	);

	// Verify Nonce.
	if ( ! wp_verify_nonce( $args['_wpnonce'], 'edit-donor' ) ) {
		wp_die(
			esc_html__( 'Cheatin&#8217; uh?', 'give' ),
			esc_html__( 'Error', 'give' ),
			array(
				'response' => 400,
			)
		);
	}

	$donor_info = $args['donor_info'];
	$donor_id   = intval( $donor_info['id'] );

	$donor = new Give_Donor( $donor_id );

	// Bailout, if donor id doesn't exists.
	if ( empty( $donor->id ) ) {
		return false;
	}

	$defaults = array(
		'title'   => '',
		'name'    => '',
		'user_id' => 0,
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'state'   => '',
		'country' => '',
	);

	$donor_info = wp_parse_args( $donor_info, $defaults );

	if ( (int) $donor_info['user_id'] !== (int) $donor->user_id ) {

		// Make sure we don't already have this user attached to a donor.
		if ( ! empty( $donor_info['user_id'] ) && false !== Give()->donors->get_donor_by( 'user_id', $donor_info['user_id'] ) ) {
			give_set_error(
				'give-invalid-donor-user_id',
				sprintf(
					/* translators: %d User ID */
					__( 'The User ID #%d is already associated with a different donor.', 'give' ),
					$donor_info['user_id']
				)
			);
		}

		// Make sure it's actually a user.
		$user = get_user_by( 'id', $donor_info['user_id'] );
		if ( ! empty( $donor_info['user_id'] ) && false === $user ) {
			give_set_error(
				'give-invalid-user_id',
				sprintf(
					/* translators: %d User ID */
					__( 'The User ID #%d does not exist. Please assign an existing user.', 'give' ),
					$donor_info['user_id']
				)
			);
		}
	}

	// Bailout, if errors are present.
	if ( give_get_errors() ) {
		return false;
	}

	$donor->update_meta( '_give_anonymous_donor', absint( $args['give_anonymous_donor'] ) );

	// Save company name in when admin update donor company name from dashboard.
	$donor->update_meta( '_give_donor_company', sanitize_text_field( $args['give_donor_company'] ) );

    /**
     * Fires after using the submitted data to update the donor metadata.
     *
     * @param array $args     The sanitized data submitted.
     * @param int   $donor_id The donor ID.
     *
     * @since 3.7.0
     */
    do_action('give_admin_donor_details_updating', $args, $donor->id);

	// If First name of donor is empty, then fetch the current first name of donor.
	if ( empty( $donor_info['first_name'] ) ) {
		$donor_info['first_name'] = $donor->get_first_name();
	}

	// Sanitize the inputs.
	$donor_data               = array();
	$donor_data['name']       = trim( "{$donor_info['first_name']} {$donor_info['last_name']}" );
	$donor_data['first_name'] = $donor_info['first_name'];
	$donor_data['last_name']  = $donor_info['last_name'];
	$donor_data['title']      = $donor_info['title'];
	$donor_data['user_id']    = $donor_info['user_id'];

	$donor_data = apply_filters( 'give_edit_donor_info', $donor_data, $donor_id );

	/**
	 * Filter the address
	 *
	 * @todo unnecessary filter because we are not storing donor address to user.
	 *
	 * @since 1.0
	 */
	$address = apply_filters( 'give_edit_donor_address', array(), $donor_id );

	$donor_data = give_clean( $donor_data );
	$address    = give_clean( $address );

	$output = give_connect_user_donor_profile( $donor, $donor_data, $address );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo wp_json_encode( $output );
		wp_die();
	}

	if ( $output['success'] ) {
		wp_safe_redirect(
            esc_url_raw(
                 add_query_arg(
                     array(
                         'post_type'       => 'give_forms',
                         'page'            => 'give-donors',
                         'view'            => 'overview',
                         'id'              => $donor_id,
                         'give-messages[]' => 'profile-updated',
                     ),
                     admin_url( 'edit.php' )
                 )
            )
		);
	}

	exit;

}

add_action( 'give_edit-donor', 'give_edit_donor', 10, 1 );

/**
 * Save a donor note.
 *
 * @param array $args The $_POST array being passed.
 *
 * @since 1.0
 *
 * @return int The Note ID that was saved, or 0 if nothing was saved.
 */
function give_donor_save_note( $args ) {

	$donor_view_role = apply_filters( 'give_view_donors_role', 'view_give_reports' );

	if ( ! is_admin() || ! current_user_can( $donor_view_role ) ) {
		wp_die(
			__( 'You do not have permission to edit this donor.', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	if ( empty( $args ) ) {
		return false;
	}

	$donor_note = trim( give_clean( $args['donor_note'] ) );
	$donor_id   = (int) $args['customer_id'];
	$nonce      = $args['add_donor_note_nonce'];

	if ( ! wp_verify_nonce( $nonce, 'add-donor-note' ) ) {
		wp_die(
			__( 'Cheatin&#8217; uh?', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 400,
			)
		);
	}

	if ( empty( $donor_note ) ) {
		give_set_error( 'empty-donor-note', __( 'A note is required.', 'give' ) );
	}

	if ( give_get_errors() ) {
		return false;
	}

	$donor    = new Give_Donor( $donor_id );
	$new_note = $donor->add_note( $donor_note );

	/**
	 * Fires before inserting donor note.
	 *
	 * @param int    $donor_id The ID of the donor.
	 * @param string $new_note Note content.
	 *
	 * @since 1.0
	 */
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
 * Disconnect a user ID from a donor
 *
 * @param array $args Array of arguments.
 *
 * @since 1.0
 *
 * @return bool|array If the disconnect was successful.
 */
function give_disconnect_donor_user_id( $args ) {

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die(
			__( 'You do not have permission to edit this donor.', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	if ( empty( $args ) ) {
		return false;
	}

	$donor_id = (int) $args['customer_id'];

	$nonce = $args['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'edit-donor' ) ) {
		wp_die(
			__( 'Cheatin&#8217; uh?', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 400,
			)
		);
	}

	$donor = new Give_Donor( $donor_id );
	if ( empty( $donor->id ) ) {
		return false;
	}

	$user_id = $donor->user_id;

	/**
	 * Fires before disconnecting user ID from a donor.
	 *
	 * @param int $donor_id The ID of the donor.
	 * @param int $user_id  The ID of the user.
	 *
	 * @since 1.0
	 */
	do_action( 'give_pre_donor_disconnect_user_id', $donor_id, $user_id );

	$output     = array();
	$donor_args = array(
		'user_id' => 0,
	);

	$redirect_url     = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' ) . $donor_id;
	$is_donor_updated = $donor->update( $donor_args );

	if ( $is_donor_updated ) {

		// Set meta for disconnected donor id and user id for future reference if needed.
		update_user_meta( $user_id, '_give_disconnected_donor_id', $donor->id );
		$donor->update_meta( '_give_disconnected_user_id', $user_id );

		$redirect_url = add_query_arg(
			'give-messages[]',
			'disconnect-user',
			$redirect_url
		);

		$output['success'] = true;

	} else {
		$output['success'] = false;
		give_set_error( 'give-disconnect-user-fail', __( 'Failed to disconnect user from donor.', 'give' ) );
	}

	$output['redirect'] = esc_url_raw( $redirect_url );

	/**
	 * Fires after disconnecting user ID from a donor.
	 *
	 * @param int $donor_id The ID of the donor.
	 *
	 * @since 1.0
	 */
	do_action( 'give_post_donor_disconnect_user_id', $donor_id );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;

}

add_action( 'give_disconnect-userid', 'give_disconnect_donor_user_id', 10, 1 );

/**
 * Add an email address to the donor from within the admin and log a donor note.
 *
 * @param array $args Array of arguments: nonce, donor id, and email address.
 *
 * @since 1.7
 *
 * @return mixed If DOING_AJAX echos out JSON, otherwise returns array of success (bool) and message (string).
 */
function give_add_donor_email( $args ) {

	$donor_id        = '';
	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );

	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die(
			__( 'You do not have permission to edit this donor.', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	$output = array();
	if ( empty( $args ) || empty( $args['email'] ) || empty( $args['customer_id'] ) ) {
		$output['success'] = false;
		if ( empty( $args['email'] ) ) {
			$output['message'] = __( 'Email address is required.', 'give' );
		} elseif ( empty( $args['customer_id'] ) ) {
			$output['message'] = __( 'Donor ID is required.', 'give' );
		} else {
			$output['message'] = __( 'An error has occurred. Please try again.', 'give' );
		}
	} elseif ( ! wp_verify_nonce( $args['_wpnonce'], 'give_add_donor_email' ) ) {
		$output = array(
			'success' => false,
			'message' => __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ),
		);
	} elseif ( ! is_email( $args['email'] ) ) {
		$output = array(
			'success' => false,
			'message' => __( 'Invalid email.', 'give' ),
		);
	} else {
		$email    = sanitize_email( $args['email'] );
		$donor_id = (int) $args['customer_id'];
		$primary  = 'true' === $args['primary'] ? true : false;
		$donor    = new Give_Donor( $donor_id );
		if ( false === $donor->add_email( $email, $primary ) ) {
			if ( in_array( $email, $donor->emails ) ) {
				$output = array(
					'success' => false,
					'message' => __( 'Email already associated with this donor.', 'give' ),
				);
			} else {
				$output = array(
					'success' => false,
					'message' => __( 'Email address is already associated with another donor.', 'give' ),
				);
			}
		} else {
			$redirect = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor_id . '&give-messages[]=email-added' );
			$output   = array(
				'success'  => true,
				'message'  => __( 'Email successfully added to donor.', 'give' ),
				'redirect' => $redirect,
			);

			$user       = wp_get_current_user();
			$user_login = ! empty( $user->user_login ) ? $user->user_login : __( 'System', 'give' );
			$donor_note = sprintf( __( 'Email address %1$s added by %2$s', 'give' ), $email, $user_login );
			$donor->add_note( $donor_note );

			if ( $primary ) {
				$donor_note = sprintf( __( 'Email address %1$s set as primary by %2$s', 'give' ), $email, $user_login );
				$donor->add_note( $donor_note );
			}
		}
	} // End if().

	do_action( 'give_post_add_donor_email', $donor_id, $args );

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		header( 'Content-Type: application/json' );
		echo json_encode( $output );
		wp_die();
	}

	return $output;
}

add_action( 'give_add_donor_email', 'give_add_donor_email', 10, 1 );


/**
 * Remove an email address to the donor from within the admin and log a donor note and redirect back to the donor interface for feedback.
 *
 * @since  1.7
 *
 * @return bool|null
 */
function give_remove_donor_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}
	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}
	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];
	if ( ! wp_verify_nonce( $nonce, 'give-remove-donor-email' ) ) {
		wp_die(
			__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	$donor = new Give_Donor( $_GET['id'] );
	if ( $donor->remove_email( $_GET['email'] ) ) {
		$url        = add_query_arg( 'give-messages[]', 'email-removed', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) );
		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : __( 'System', 'give' );
		$donor_note = sprintf( __( 'Email address %1$s removed by %2$s', 'give' ), $_GET['email'], $user_login );
		$donor->add_note( $donor_note );
	} else {
		$url = add_query_arg( 'give-messages[]', 'email-remove-failed', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) );
	}

	wp_safe_redirect( esc_url_raw( $url ) );
	exit;
}

add_action( 'give_remove_donor_email', 'give_remove_donor_email', 10 );


/**
 * Set an email address as the primary for a donor from within the admin and log a donor note
 * and redirect back to the donor interface for feedback
 *
 * @since  1.7
 *
 * @return bool|null
 */
function give_set_donor_primary_email() {
	if ( empty( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		return false;
	}

	if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
		return false;
	}

	if ( empty( $_GET['_wpnonce'] ) ) {
		return false;
	}

	$nonce = $_GET['_wpnonce'];

	if ( ! wp_verify_nonce( $nonce, 'give-set-donor-primary-email' ) ) {
		wp_die(
			__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ),
			__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	$donor = new Give_Donor( $_GET['id'] );

	if ( $donor->set_primary_email( $_GET['email'] ) ) {
		$url        = add_query_arg( 'give-messages[]', 'primary-email-updated', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) );
		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : __( 'System', 'give' );
		$donor_note = sprintf( __( 'Email address %1$s set as primary by %2$s', 'give' ), $_GET['email'], $user_login );

		$donor->add_note( $donor_note );
	} else {
		$url = add_query_arg( 'give-messages[]', 'primary-email-failed', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor->id ) );
	}

	wp_safe_redirect( esc_url_raw( $url ) );
	exit;
}

add_action( 'give_set_donor_primary_email', 'give_set_donor_primary_email', 10 );


/**
 * This function will process the donor deletion.
 *
 * @param array $args Donor Deletion Arguments.
 *
 * @since 2.2
 */
function give_process_donor_deletion( $args ) {
	// Bailout.
	if ( ! isset( $args['give-donor-delete-confirm'] ) ) {
		return;
	}

	$donor_edit_role = apply_filters( 'give_edit_donors_role', 'edit_give_payments' );

	// Verify user capabilities to proceed for deleting donor.
	if ( ! is_admin() || ! current_user_can( $donor_edit_role ) ) {
		wp_die(
			esc_html__( 'You do not have permission to delete donors.', 'give' ),
			esc_html__( 'Error', 'give' ),
			array(
				'response' => 403,
			)
		);
	}

	$nonce_action = '';
	if ( 'delete_bulk_donor' === $args['give_action'] ) {
		$nonce_action = 'bulk-donors';
	} elseif ( 'delete_donor' === $args['give_action'] ) {
		$nonce_action = 'give-delete-donor';
	}

	// Verify Nonce for deleting bulk donors.
	give_validate_nonce( $args['_wpnonce'], $nonce_action );

	$redirect_args            = array();
	$donor_ids                = ( isset( $args['donor'] ) && is_array( $args['donor'] ) ) ? $args['donor'] : array( $args['donor_id'] );
	$redirect_args['order']   = ! empty( $args['order'] ) ? $args['order'] : 'DESC';
	$redirect_args['orderby'] = ! empty( $args['orderby'] ) ? strtolower( $args['orderby'] ) : 'id';
	$redirect_args['s']       = ! empty( $args['s'] ) ? $args['s'] : '';
	$delete_donor             = ! empty( $args['give-donor-delete-confirm'] ) ? give_is_setting_enabled( $args['give-donor-delete-confirm'] ) : false;
	$delete_donation          = ! empty( $args['give-donor-delete-records'] ) ? give_is_setting_enabled( $args['give-donor-delete-records'] ) : false;

	if ( count( $donor_ids ) > 0 ) {

		// Loop through the selected donors to delete.
		foreach ( $donor_ids as $donor_id ) {

			$donor = new Give_Donor( $donor_id );

			// Proceed only if valid donor id is provided.
			if ( $donor->id > 0 ) {

				/**
				 * Fires before deleting donor.
				 *
				 * @param int  $donor_id     The ID of the donor.
				 * @param bool $delete_donor Confirm Donor Deletion.
				 * @param bool $delete_donation  Confirm Donor related donations deletion.
				 *
				 * @since 1.0
				 */
				do_action( 'give_pre_delete_donor', $donor->id, $delete_donor, $delete_donation );

				// Proceed only, if user confirmed whether they need to delete the donor.
				if ( $delete_donor ) {

					// Delete donor and linked donations.
					$donor_delete_status = give_delete_donor_and_related_donation(
						$donor,
						array(
							'delete_donation' => $delete_donation,
						)
					);

					if ( 1 === $donor_delete_status ) {
						$redirect_args['give-messages[]'] = 'donor-deleted';
					} elseif ( 2 === $donor_delete_status ) {
						$redirect_args['give-messages[]'] = 'donor-donations-deleted';
					}
				} else {
					$redirect_args['give-messages[]'] = 'confirm-delete-donor';
				}
			} else {
				$redirect_args['give-messages[]'] = 'invalid-donor-id';
			} // End if().
		} // End foreach().
	} else {
		$redirect_args['give-messages[]'] = 'no-donor-found';
	} // End if().

	$redirect_url = add_query_arg(
		$redirect_args,
		admin_url( 'edit.php?post_type=give_forms&page=give-donors' )
	);

	wp_safe_redirect( esc_url_raw( $redirect_url ) );
	give_die();

}
add_action( 'give_delete_donor', 'give_process_donor_deletion' );
add_action( 'give_delete_bulk_donor', 'give_process_donor_deletion' );
