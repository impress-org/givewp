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
 */
function give_get_donor_avatar( $donor ) {


	?>
	<div class="give-donor__image">

		<?php
		// Check if gravatar exists.
		if ( give_validate_gravatar( $donor->email ) ) {

			echo get_avatar( $donor->email );

		} else {
			// No gravatar = output initials.
			echo $donor->get_donor_initals( $donor );
		}

		?>

	</div>

	<?php

}

/**
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
