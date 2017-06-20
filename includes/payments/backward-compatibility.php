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

	// Bailout.
	if (
		'give_payment' !== get_post_type( $object_id ) ||
		! in_array( $meta_key, $old_meta_keys )
	) {
		return $check;
	}

	switch ( $meta_key ) {

		// Handle old meta keys.
		case '_give_payment_meta':
			// Donation key.
			if ( $donation_key = give_get_meta( $object_id, '_give_payment_purchase_key', true ) ) {
				$meta_value['key'] = $donation_key;
			}

			// Donation form.
			if ( $donation_form = give_get_meta( $object_id, '_give_payment_form_title', true ) ) {
				$meta_value['form_title'] = $donation_form;
			}

			// Donor email.
			if ( $donor_email = give_get_meta( $object_id, '_give_payment_donor_email', true ) ) {
				$meta_value['email'] = $donor_email;
			}

			// Form id.
			if ( $form_id = give_get_meta( $object_id, '_give_payment_form_id', true ) ) {
				$meta_value['form_id'] = $form_id;
			}

			// Price id.
			if ( $price_id = give_get_meta( $object_id, '_give_payment_price_id', true ) ) {
				$meta_value['date'] = $price_id;
			}

			// Date.
			if ( $donation_date = give_get_meta( $object_id, '_give_payment_date', true ) ) {
				$meta_value['date'] = $donation_date;
			}

			// Currency.
			if ( $donation_currency = give_get_meta( $object_id, '_give_payment_currency', true ) ) {
				$meta_value['currency'] = $donation_currency;
			}

			// Decode donor data.
			$donor_data = isset( $meta_value['user_info'] ) ? maybe_unserialize( $meta_value['user_info'] ) : array();

			// Donor address.
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
			if ( $donor_id = give_get_meta( $object_id, '_give_payment_donor_id', true ) ) {
				$check = $donor_id;
			}
			break;

		case '_give_payment_user_email':
			if ( $donor_email = give_get_meta( $object_id, '_give_payment_donor_email', true ) ) {
				$check = $donor_email;
			}
			break;

		case '_give_payment_user_ip':
			if ( $donor_ip = give_get_meta( $object_id, '_give_payment_donor_ip', true ) ) {
				$check = $donor_ip;
			}
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
	global $wpdb;
	$new_meta_keys = array(
		'_give_payment_donor_id',
		'_give_payment_donor_email',
		'_give_payment_donor_ip',
		'_give_donor_billing_first_name',
		'_give_donor_billing_last_name',
		'_give_donor_billing_address1',
		'_give_donor_billing_address2',
		'_give_donor_billing_city',
		'_give_donor_billing_zip',
		'_give_donor_billing_state',
		'_give_donor_billing_country',
		'_give_payment_date',
		'_give_payment_currency',
	);

	// metadata_exists fx will cause of firing get_post_metadata filter again so remove it to prevent infinite loop.
	remove_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta' );

	// Bailout.
	if (
		'give_payment' !== get_post_type( $object_id ) ||
		! in_array( $meta_key, $new_meta_keys ) ||
		metadata_exists( 'post', $object_id, $meta_key )
	) {
		add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );

		return $check;
	}

	add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );

	switch ( $meta_key ) {

		// Handle new meta keys.
		case '_give_payment_donor_id':
			$check = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%s AND meta_key=%s",
					$object_id,
					'_give_payment_customer_id'
				)
			);
			break;

		case '_give_payment_donor_email':
			$check = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%s AND meta_key=%s",
					$object_id,
					'_give_payment_user_email'
				)
			);
			break;

		case '_give_payment_donor_ip':
			$check = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%s AND meta_key=%s",
					$object_id,
					'_give_payment_user_ip'
				)
			);
			break;

		case '_give_donor_billing_first_name':
		case '_give_donor_billing_last_name':
		case '_give_donor_billing_address1':
		case '_give_donor_billing_address2':
		case '_give_donor_billing_city':
		case '_give_donor_billing_zip':
		case '_give_donor_billing_state':
		case '_give_donor_billing_country':
		case '_give_payment_date':
		case '_give_payment_currency':
			$donation_meta = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%s AND meta_key=%s",
					$object_id,
					'_give_payment_meta'
				)
			);
			$donation_meta = maybe_unserialize( $donation_meta );

			if ( in_array( $meta_key, array( '_give_payment_date', '_give_payment_currency' ) ) ) {
				$meta_key = str_replace( '_give_payment_', '', $meta_key );
				if ( isset( $donation_meta[ $meta_key ] ) ) {
					$check = $donation_meta[ $meta_key ];
				}
			} else {
				$meta_key = str_replace( '_give_donor_billing_', '', $meta_key );

				switch ( $meta_key ) {
					case 'address1':
						if ( isset( $donation_meta['user_info']['address']['line1'] ) ) {
							$check = $donation_meta['user_info']['address']['line1'];
						}
						break;

					case 'address2':
						if ( isset( $donation_meta['user_info']['address']['line2'] ) ) {
							$check = $donation_meta['user_info']['address']['line2'];
						}
						break;

					default:
						if ( isset( $donation_meta['user_info']['address'][ $meta_key ] ) ) {
							$check = $donation_meta['user_info']['address'][ $meta_key ];
						}
				}
			}

			break;
	}

	return $check;
}

add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );