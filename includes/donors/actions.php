<?php
/**
 * Insert donation comment
 *
 * @since 2.1.0
 *
 * @param int   $donation_id
 * @param array $donation_data
 *
 * @return bool
 */
function __give_insert_donor_comment( $donation_id, $donation_data ) {
	if ( empty( $_POST['give_comment'] ) ) {
		return false;
	}

	give_insert_donor_donation_comment(
		$donation_id,
		$donation_data['user_info']['id'],
		give_clean( $_POST['give_comment'] )
	);
}

add_action( 'give_insert_payment', '__give_insert_donor_comment', 10, 2 );

