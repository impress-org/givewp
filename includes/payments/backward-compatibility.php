<?php
/**
 * Add backward compatibility old meta while saving.
 *  1. _give_payment_meta (split into multiple single meta keys)
 *  2. _give_payment_user_email (renamed to _give_payment_donor_email)
 *  3. _give_payment_customer_id (renamed to _give_payment_donor_id)
 *  4. give_payment_user_ip (renamed to give_payment_donor_ip)
 *
 * @since 2.0
 *
 * @param null|bool $check      Whether to allow updating metadata for the given type.
 * @param int       $object_id  Object ID.
 * @param string    $meta_key   Meta key.
 * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
 * @param mixed     $prev_value Optional. If specified, only update existing
 *                              metadata entries with the specified value.
 *                              Otherwise, update all entries.
 *
 * @return mixed
 */
function _give_20_bc_saving_old_payment_meta( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
	// Bailout.
	if ( 'give_payment' !== get_post_type( $object_id ) || empty( $meta_value ) ) {
		return $check;
	}

	if ( '_give_payment_meta' === $meta_key ) {
		// Date payment meta.
		if ( ! empty( $meta_value['date'] ) ) {
			give_update_meta( $object_id, '_give_payment_date', $meta_value['date'] );
		}

		// Currency payment meta.
		if ( ! empty( $meta_value['currency'] ) ) {
			give_update_meta( $object_id, '_give_payment_currency', $meta_value['currency'] );
		}

		// Donor address payment meta.
		if ( ! empty( $meta_value['user_info']['address'] ) ) {

			// Donor first name.
			if ( ! empty( $meta_value['user_info']['first_name'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_first_name', $meta_value['user_info']['first_name'] );
			}

			// Donor last name.
			if ( ! empty( $meta_value['user_info']['last_name'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_last_name', $meta_value['user_info']['last_name'] );
			}

			// Address1.
			if ( ! empty( $meta_value['user_info']['address']['line1'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_address1', $meta_value['user_info']['address']['line1'] );
			}

			// Address2.
			if ( ! empty( $meta_value['user_info']['address']['line2'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_address2', $meta_value['user_info']['address']['line2'] );
			}

			// City.
			if ( ! empty( $meta_value['user_info']['address']['city'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_city', $meta_value['user_info']['address']['city'] );
			}

			// Zip.
			if ( ! empty( $meta_value['user_info']['address']['zip'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_zip', $meta_value['user_info']['address']['zip'] );
			}

			// State.
			if ( ! empty( $meta_value['user_info']['address']['state'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_state', $meta_value['user_info']['address']['state'] );
			}

			// Country.
			if ( ! empty( $meta_value['user_info']['address']['country'] ) ) {
				give_update_meta( $object_id, '_give_donor_billing_country', $meta_value['user_info']['address']['country'] );
			}
		}
	} elseif ( '_give_payment_user_email' === $meta_key ) {
		give_update_meta( $object_id, '_give_payment_donor_email', $meta_value );
		$check = true;
	} elseif ( '_give_payment_customer_id' === $meta_key ) {
		give_update_meta( $object_id, '_give_payment_donor_id', $meta_value );
		$check = true;
	} elseif ( 'give_payment_user_ip' === $meta_key ) {
		give_update_meta( $object_id, '_give_payment_donor_ip', $meta_value );
		$check = true;
	}


	return $check;
}

add_filter( 'update_post_metadata', '_give_20_bc_saving_old_payment_meta', 10, 5 );


/**
 * Add backward compatibility to get old payment meta.
 *
 * @since 2.0
 *
 * @param $check
 * @param $object_id
 * @param $meta_key
 * @param $single
 *
 * @return mixed
 */
function _give_20_bc_get_old_payment_meta( $check, $object_id, $meta_key, $single ) {
	$old_meta_keys = array(
		'_give_payment_meta',
		'_give_payment_customer_id',
		'_give_payment_user_email',
		'_give_payment_user_ip',
	);

	// metadata_exists fx will cause of firing get_post_metadata filter again so remove it to infinite prevent loop.
	remove_filter( 'get_post_metadata', '_give_20_bc_get_old_payment_meta' );

	// Bailout.
	if (
		'give_payment' !== get_post_type( $object_id ) ||
		! in_array( $meta_key, $old_meta_keys ) ||
		metadata_exists( 'post', $object_id, $meta_key )
	) {
		return $check;
	}

	add_filter( 'get_post_metadata', '_give_20_bc_get_old_payment_meta', 10, 5 );


	switch ( $meta_key ) {

		// Handle old meta keys.
		case '_give_payment_meta':
			// Date payment meta.
			if ( ! empty( $meta_value['date'] ) ) {
				$meta_value['date'] = give_get_meta( $object_id, '_give_payment_date', true, $meta_value['date'] );
			}

			// Currency payment meta.
			if ( ! empty( $meta_value['currency'] ) ) {
				$meta_value['currency'] = give_get_meta( $object_id, '_give_payment_currency', true, $meta_value['currency'] );
			}

			// Decode donor data.
			$donor_data = isset( $meta_value['user_info'] ) ? maybe_unserialize( $meta_value['user_info'] ) : array();

			// Donor address payment meta.
			if ( ! empty( $donor_data ) ) {
				// Donor first name.
				$donor_data['first_name'] = give_get_meta( $object_id, '_give_donor_billing_first_name', true, isset( $donor_data['first_name'] ) ? $donor_data['first_name'] : '' );

				// Donor last name.
				$donor_data['last_name'] = give_get_meta( $object_id, '_give_donor_billing_last_name', true, isset( $donor_data['last_name'] ) ? $donor_data['last_name'] : '' );

				if ( ! empty( $donor_data['address'] ) ) {
					// Address1.
					$donor_data['address']['line1'] = give_get_meta( $object_id, '_give_donor_billing_address1', true, isset( $donor_data['address']['line1'] ) ? $donor_data['address']['line1'] : '' );


					// Address2.
					$donor_data['address']['line2'] = give_get_meta( $object_id, '_give_donor_billing_address1', true, isset( $donor_data['address']['line2'] ) ? $donor_data['address']['line2'] : '' );


					// City.
					$donor_data['address']['city'] = give_get_meta( $object_id, '_give_donor_billing_city', true, isset( $donor_data['address']['city'] ) ? $donor_data['address']['city'] : '' );


					// Zip.
					$donor_data['address']['zip'] = give_get_meta( $object_id, '_give_donor_billing_zip', true, isset( $donor_data['address']['zip'] ) ? $donor_data['address']['zip'] : '' );


					// State.
					$donor_data['address']['state'] = give_get_meta( $object_id, '_give_donor_billing_state', true, isset( $donor_data['address']['state'] ) ? $donor_data['address']['state'] : '' );


					// Country.
					$donor_data['address']['country'] = give_get_meta( $object_id, '_give_donor_billing_country', true, isset( $donor_data['address']['country'] ) ? $donor_data['address']['country'] : '' );
				}

				$meta_value['user_info'] = is_serialized( $meta_value['user_info'] ) ? serialize( $donor_data ) : $donor_data;
			}

			break;

		case '_give_payment_customer_id':
			$check = give_get_meta( $object_id, '_give_payment_donor_id', true );
			break;

		case '_give_payment_user_email':
			$check = give_get_meta( $object_id, '_give_payment_donor_email', true );
			break;

		case '_give_payment_user_ip':
			$check = give_get_meta( $object_id, '_give_payment_donor_ip', true );
			break;
	}

	return $check;
}

add_filter( 'get_post_metadata', '_give_20_bc_get_old_payment_meta', 10, 5 );


/**
 * Add backward compatibility to get new payment meta.
 *
 * @since 2.0
 *
 * @param $check
 * @param $object_id
 * @param $meta_key
 * @param $single
 *
 * @return mixed
 */
function _give_20_bc_get_new_payment_meta( $check, $object_id, $meta_key, $single ) {
	$new_meta_keys = array(
		'_give_payment_donor_id',
		'_give_payment_donor_email',
		'_give_payment_donor_ip',
	);

	// metadata_exists fx will cause of firing get_post_metadata filter again so remove it to prevent infinite loop.
	remove_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta' );

	// Bailout.
	if (
		'give_payment' !== get_post_type( $object_id ) ||
		! in_array( $meta_key, $new_meta_keys ) ||
		metadata_exists( 'post', $object_id, $meta_key )
	) {
		return $check;
	}

	add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );

	switch ( $meta_key ) {

		// Handle new meta keys.
		case '_give_payment_donor_id':
			$check = get_post_meta( $object_id, '_give_payment_customer_id', true );
			break;

		case '_give_payment_donor_email':
			$check = get_post_meta( $object_id, '_give_payment_user_email', true );
			break;

		case '_give_payment_donor_ip':
			$check = get_post_meta( $object_id, '_give_payment_user_ip', true );
			break;
	}

	return $check;
}

add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );