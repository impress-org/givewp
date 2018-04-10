<?php
/**
 * Donors
 *
 * @package    Give
 * @subpackage Donors
 * @copyright  Copyright (c) 2018, WordImpress
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      2.1
 */

/**
 * Get the donor's Gravatar with a nice fallback.
 *
 * The fallback uses the donor's
 *
 * @since 2.1
 *
 * @param Give_Donor $donor
 * @param int        $size
 *
 * @return string HTML output.
 */
function give_get_donor_avatar( $donor, $size = 60 ) {
	ob_start();
	?>
	<div class="give-donor__image">
		<?php
		// Check if gravatar exists.
		if ( give_validate_gravatar( $donor->email ) ) {
			// Return avatar.
			echo get_avatar( $donor->email, $size );
		} else {
			// No gravatar = output initials.
			echo $donor->get_donor_initals();
		} ?>
	</div>
	<?php

	return apply_filters( 'give_get_donor_avatar', ob_get_clean() );

}

/**
 * Determine whether a Gravatar exists for a donor or not.
 *
 * @since 2.1
 *
 * @param string|int $id_or_email
 *
 * @return bool
 */
function give_validate_gravatar( $id_or_email ) {

	//id or email code borrowed from wp-includes/pluggable.php
	$email = '';
	if ( is_numeric( $id_or_email ) ) {
		$id   = (int) $id_or_email;
		$user = get_userdata( $id );
		if ( $user ) {
			$email = $user->user_email;
		}
	} elseif ( is_object( $id_or_email ) ) {
		// No avatar for pingbacks or trackbacks
		$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
		if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
			return false;
		}

		if ( ! empty( $id_or_email->user_id ) ) {
			$id   = (int) $id_or_email->user_id;
			$user = get_userdata( $id );
			if ( $user ) {
				$email = $user->user_email;
			}
		} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
			$email = $id_or_email->comment_author_email;
		}
	} else {
		$email = $id_or_email;
	}

	$hashkey = md5( strtolower( trim( $email ) ) );
	$uri     = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

	$data = wp_cache_get( $hashkey );
	if ( false === $data ) {
		$response = wp_remote_head( $uri );
		if ( is_wp_error( $response ) ) {
			$data = 'not200';
		} else {
			$data = $response['response']['code'];
		}
		wp_cache_set( $hashkey, $data, $group = '', $expire = 60 * 5 );

	}
	if ( $data == '200' ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Add a donor comment to a donation
 *
 * @param int    $donation_id The donation ID to store a note for.
 * @param int    $donor       The donor ID to store a note for.
 * @param string $note        The note to store.
 * @param int    $approve     Default approve status of comment.
 *
 * @since 2.1.0
 *
 * @return int The new note ID
 */
function give_insert_donor_donation_comment( $donation_id, $donor, $note, $approve = 0 ) {
	$comment_id = Give_Comment::add( $donation_id, $note, 'payment', array( 'comment_approved' => $approve ) );
	update_comment_meta( $comment_id, '_give_donor_id', $donor );

	return $comment_id;
}


/**
 * Retrieve all donor notes attached to a donation
 *
 * @param int    $donation_id The donation ID to retrieve notes for.
 * @param int    $donor_id    The donor ID to retrieve notes for.
 * @param string $search      Search for notes that contain a search term.
 *
 * @since 1.0
 *
 * @return array $notes Donation Notes
 */
function give_get_donor_payment_notes( $donation_id, $donor_id, $search = '' ) {
	return Give_Comment::get(
		$donation_id,
		$search,
		'payment',
		array(
			'meta_query' => array(
				array(
					'key'   => '_give_donor_id',
					'value' => $donor_id
				)
			)
		)
	);
}
