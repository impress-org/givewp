<?php
/**
 * Get donor address from donor meta instead of user meta
 *
 * @since 2.0
 *
 * @param $meta_value
 * @param $user_id
 * @param $meta_key
 * @param $single
 *
 * @return string|array
 */
function __give_v20_bc_user_address( $meta_value, $user_id, $meta_key, $single ) {
	if (
		give_has_upgrade_completed( 'v20_upgrades_user_address' ) &&
		'_give_user_address' === $meta_key
	) {
		$meta_value = give_get_donor_address( $user_id, array( 'by_user_id' => true ) );

		if ( $single ) {
			$meta_value = array( $meta_value );
		}
	}

	return $meta_value;
}

add_filter( 'get_user_metadata', '__give_v20_bc_user_address', 10, 4 );
