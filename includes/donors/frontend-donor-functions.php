<?php
/**
 * Donors
 *
 * @package    Give
 * @subpackage Donors
 * @copyright  Copyright (c) 2018, WordImpress
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      2.2.0
 */

/**
 * Get the donor's Gravatar with a nice fallback.
 *
 * The fallback uses the donor's
 *
 * @since 2.2.0
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
 * @since 2.2.0
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
 * @param int    $donation_id  The donation ID to store a note for.
 * @param int    $donor        The donor ID to store a note for.
 * @param string $note         The note to store.
 * @param array  $comment_args Comment arguments.
 *
 * @since 2.2.0
 *
 * @return int The new note ID
 */
function give_insert_donor_donation_comment( $donation_id, $donor, $note, $comment_args = array() ) {
	$comment_args = wp_parse_args(
		$comment_args,
		array(
			'comment_approved' => 0,
			'comment_parent'   => give_get_payment_form_id( $donation_id )
		)
	);

	$comment_id = Give_Comment::add( $donation_id, $note, 'payment', $comment_args );

	update_comment_meta( $comment_id, '_give_donor_id', $donor );

	return $comment_id;
}


/**
 * Retrieve all donor comment attached to a donation
 *
 * Note: currently donor can only add one comment per donation
 *
 * @param int    $donation_id The donation ID to retrieve comment for.
 * @param int    $donor_id    The donor ID to retrieve comment for.
 * @param string $search      Search for comment that contain a search term.
 *
 * @since 2.2.0
 *
 * @return WP_Comment|array
 */
function give_get_donor_donation_comment( $donation_id, $donor_id, $search = '' ) {
	$comments = Give_Comment::get(
		$donation_id,
		'payment',
		array(
			'number'     => 1,
			'meta_query' => array(
				array(
					'key'   => '_give_donor_id',
					'value' => $donor_id
				)
			)
		),
		$search
	);

	return ( ! empty( $comments ) ? current( $comments ) : array() );
}

/**
 * Retrieve donor comment id attached to a donation
 *
 * Note: currently donor can only add one comment per donation
 *
 * @param int    $donation_id The donation ID to retrieve comment for.
 * @param int    $donor_id    The donor ID to retrieve comment for.
 * @param string $search      Search for comment that contain a search term.
 *
 * @since 2.2.0
 *
 * @return int
 */
function give_get_donor_donation_comment_id( $donation_id, $donor_id, $search = '' ) {
	/* @var WP_Comment|array $comment */
	$comment    = give_get_donor_donation_comment( $donation_id, $donor_id, $search );
	$comment_id = $comment instanceof WP_Comment ? $comment->comment_ID : 0;

	return $comment_id;
}

/**
 * Retrieve all donor comment attached to a donation
 *
 * Note: currently donor can only add one comment per donation
 *
 * @param int    $donor_id The donor ID to retrieve comment for.
 * @param array  $comment_args
 * @param string $search   Search for comment that contain a search term.
 *
 * @since 2.2.0
 *
 * @return array
 */
function give_get_donor_donation_comments( $donor_id, $comment_args = array(), $search = '' ) {
	$comments = Give_Comment::get(
		$donor_id,
		'payment',
		$comment_args,
		$search
	);

	return ( ! empty( $comments ) ? $comments : array() );
}


/**
 * Gets the donor donation comment HTML
 *
 * @param WP_Comment|int $comment    The comment object or ID.
 * @param int            $payment_id The payment ID the note is connected to.
 *
 * @since 2.2.0
 *
 * @return string
 */
function give_get_donor_donation_comment_html( $comment, $payment_id = 0 ) {

	if ( is_numeric( $comment ) ) {
		$comment = get_comment( $comment );
	}

	$date_format = give_date_format() . ', ' . get_option( 'time_format' );

	$comment_html = sprintf(
		'<div class="give-payment-note" id="give-payment-note-%s"><p><strong>%s</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">%s</span><br/>%s</p></div>',
		$comment->comment_ID,
		get_comment_author( $comment->comment_ID ),
		date_i18n( $date_format, strtotime( $comment->comment_date ) ),
		$comment->comment_content
	);

	return $comment_html;

}


/**
 * Get donor latest comment
 *
 * @since 2.2.0
 *
 * @param int $donor_id
 * @param int $form_id
 *
 * @return WP_Comment/array
 */
function give_get_donor_latest_comment( $donor_id, $form_id = 0 ) {
	$comment_args = array(
		'post_id'    => 0,
		'orderby'    => 'comment_ID',
		'order'      => 'DESC',
		'number'     => 1,
		'meta_query' => array(
			'related' => 'AND',
			array(
				'key'   => '_give_donor_id',
				'value' => $donor_id
			),
			array(
				'key'   => '_give_anonymous_donation',
				'value' => 0
			)
		)
	);

	// Get donor donation comment for specific form.
	if ( $form_id ) {
		$comment_args['parent'] = $form_id;
	}

	$comment = current( give_get_donor_donation_comments( $donor_id, $comment_args ) );

	return $comment;
}
