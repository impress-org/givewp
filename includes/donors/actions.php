<?php
/**
 * Insert donor comment to donation.
 *
 * @since 2.2.0
 *
 * @param int   $donation_id
 * @param array $donation_data
 */
function __give_insert_donor_donation_comment( $donation_id, $donation_data ) {
	$is_anonymous_donation = isset( $_POST['give_anonymous_donation'] )
		? absint( $_POST['give_anonymous_donation'] )
		: 0;

	if ( ! empty( $_POST['give_comment'] ) ) {
		$comment_meta = array( 'author_email' => $donation_data['user_info']['email'] );

		if ( ! give_has_upgrade_completed( 'v230_move_donation_note' ) ) {
			// Backward compatibility.
			$comment_meta = array( 'comment_author_email' => $donation_data['user_info']['email'] );
		}

		$comment_id = give_insert_donor_donation_comment(
			$donation_id,
			$donation_data['user_info']['donor_id'],
			trim( $_POST['give_comment'] ), // We are sanitizing comment in Give_comment:add
			$comment_meta
		);
	}

	give_update_meta( $donation_id, '_give_anonymous_donation', $is_anonymous_donation );
}

add_action( 'give_insert_payment', '__give_insert_donor_donation_comment', 10, 2 );


/**
 * Validate donor comment
 *
 * @since 2.2.0
 */
function __give_validate_donor_comment() {
	// Check wp_check_comment_data_max_lengths for comment length validation.
	if ( ! empty( $_POST['give_comment'] ) ) {
		$max_lengths = wp_get_comment_fields_max_lengths();
		$comment     = give_clean( $_POST['give_comment'] );

		if ( mb_strlen( $comment, '8bit' ) > $max_lengths['comment_content'] ) {
			give_set_error( 'comment_content_column_length', __( 'Your comment is too long.', 'give' ) );
		}
	}
}
add_action( 'give_checkout_error_checks', '__give_validate_donor_comment', 10, 1 );


/**
 * Update donor comment status when donation status update
 *
 * @since 2.2.0
 *
 * @param $donation_id
 * @param $status
 */
function __give_update_donor_donation_comment_status( $donation_id, $status ) {
	$approve = absint( 'publish' === $status );

	/* @var WP_Comment $note */
	$donor_comment = give_get_donor_donation_comment( $donation_id, give_get_payment_donor_id( $donation_id ) );

	if ( $donor_comment instanceof WP_Comment ) {
		wp_set_comment_status( $donor_comment->comment_ID, (string) $approve );
	}
}

add_action( 'give_update_payment_status', '__give_update_donor_donation_comment_status', 10, 2 );

/**
 * Remove donor comment when donation delete
 *
 * @since 2.2.0
 *
 * @param $donation_id
 */
function __give_remove_donor_donation_comment( $donation_id ) {
	/* @var WP_Comment $note */
	$donor_comment = give_get_donor_donation_comment( $donation_id, give_get_payment_donor_id( $donation_id ) );

	if ( $donor_comment instanceof WP_Comment ) {
		wp_delete_comment( $donor_comment->comment_ID );
	}
}

add_action( 'give_payment_deleted', '__give_remove_donor_donation_comment', 10 );
