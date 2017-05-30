<?php
/**
 * Add backward compatibility to payment meta while saving.
 *
 * @since 2.0
 *
 * @param bool   $status
 * @param int    $id
 * @param string $meta_key
 * @param mixed  $meta_value
 *
 * @return mixed
 */
function _give_bc_update_payment_meta20( $status, $id, $meta_key, $meta_value ) {
	$payment_meta_keys = array(
		'_give_payment_meta',
		'_give_payment_customer_id',
		'_give_payment_user_email',
		'_give_payment_user_ip',
	);

	// Bailout.
	if (
		! $status ||
		'give_payment' !== get_post_type( $id ) ||
		! in_array( $meta_key, $payment_meta_keys )
	) {
		return $status;
	}

	switch ( $meta_key ) {
		case '_give_payment_meta':
			// Date payment meta.
			if ( ! empty( $meta_value['date'] ) ) {
				give_update_meta( $id, '_give_payment_date', $meta_value['date'] );
			}

			// Currency payment meta.
			if ( ! empty( $meta_value['currency'] ) ) {
				give_update_meta( $id, '_give_payment_currency', $meta_value['currency'] );
			}

			// Donor address payment meta.
			if ( ! empty( $meta_value['user_info']['address'] ) ) {

				// Donor first name.
				if ( ! empty( $meta_value['user_info']['first_name'] ) ) {
					give_update_meta( $id, '_give_donor_billing_first_name', $meta_value['user_info']['first_name'] );
				}

				// Donor last name.
				if ( ! empty( $meta_value['user_info']['last_name'] ) ) {
					give_update_meta( $id, '_give_donor_billing_last_name', $meta_value['user_info']['last_name'] );
				}

				// Address1.
				if ( ! empty( $meta_value['user_info']['address']['line1'] ) ) {
					give_update_meta( $id, '_give_donor_billing_address1', $meta_value['user_info']['address']['line1'] );
				}

				// Address2.
				if ( ! empty( $meta_value['user_info']['address']['line2'] ) ) {
					give_update_meta( $id, '_give_donor_billing_address2', $meta_value['user_info']['address']['line2'] );
				}

				// City.
				if ( ! empty( $meta_value['user_info']['address']['city'] ) ) {
					give_update_meta( $id, '_give_donor_billing_city', $meta_value['user_info']['address']['city'] );
				}

				// Zip.
				if ( ! empty( $meta_value['user_info']['address']['zip'] ) ) {
					give_update_meta( $id, '_give_donor_billing_zip', $meta_value['user_info']['address']['zip'] );
				}

				// State.
				if ( ! empty( $meta_value['user_info']['address']['state'] ) ) {
					give_update_meta( $id, '_give_donor_billing_state', $meta_value['user_info']['address']['state'] );
				}

				// Country.
				if ( ! empty( $meta_value['user_info']['address']['country'] ) ) {
					give_update_meta( $id, '_give_donor_billing_country', $meta_value['user_info']['address']['country'] );
				}
			}

			break;

		case '_give_payment_customer_id':
			give_update_meta( $id, '_give_payment_donor_id', $meta_value );
			break;

		case '_give_payment_user_email':
			give_update_meta( $id, '_give_payment_donor_email', $meta_value );
			break;

		case '_give_payment_user_ip':
			give_update_meta( $id, '_give_payment_donor_ip', $meta_value );
			break;
	}

	return $status;
}

// add_filter( 'give_update_meta', '_give_bc_update_payment_meta20', 10, 4 );


/**
 * Add backward compatibility to payment meta while fetching data.
 *
 * @since 2.0
 *
 * @param mixed  $meta_value
 * @param int    $id
 * @param string $meta_key
 *
 * @return mixed
 */
function _give_bc_get_payment_meta20( $meta_value, $id, $meta_key ) {
	$payment_old_meta_keys = array(
		'_give_payment_meta',
		'_give_payment_customer_id',
		'_give_payment_user_email',
		'_give_payment_user_ip',
	);

	$payment_new_meta_keys = array(
		'_give_payment_donor_id',
		'_give_payment_donor_email',
		'_give_payment_donor_ip',
	);

	// Bailout.
	if (
		'give_payment' !== get_post_type( $id ) ||
		! in_array( $meta_key, $payment_old_meta_keys ) ||
		( in_array( $meta_key, $payment_new_meta_keys ) && ! empty( $meta_value ) )
	) {
		return $meta_value;
	}

	switch ( $meta_key ) {

		// Handle new meta keys.
		case '_give_payment_donor_id':
			$meta_value = get_post_meta( $id, '_give_payment_customer_id', true );
			break;

		case '_give_payment_donor_email':
			$meta_value = get_post_meta( $id, '_give_payment_user_email', true );
			break;

		case '_give_payment_donor_ip':
			$meta_value = get_post_meta( $id, '_give_payment_user_ip', true );
			break;


		// Handle old meta keys.
		case '_give_payment_meta':
			// Date payment meta.
			if( ! empty( $meta_value['date'] ) ) {
				$meta_value['date'] = give_get_meta( $id, '_give_payment_date', true, $meta_value['date'] );
			}

			// Currency payment meta.
			if( ! empty( $meta_value['currency'] ) ) {
				$meta_value['currency'] = give_get_meta( $id, '_give_payment_currency', true, $meta_value['currency'] );
			}

			// Decode donor data.
			$donor_data = isset( $meta_value['user_info'] ) ? maybe_unserialize( $meta_value['user_info'] ) : array();

			// Donor address payment meta.
			if ( ! empty( $donor_data ) ) {
				// Donor first name.
				$donor_data['first_name'] = give_get_meta( $id, '_give_donor_billing_first_name', true, isset( $donor_data['first_name'] ) ? $donor_data['first_name'] : '' );

				// Donor last name.
				$donor_data['last_name'] = give_get_meta( $id, '_give_donor_billing_last_name', true, isset( $donor_data['last_name'] ) ? $donor_data['last_name'] : '' );

				if( ! empty( $donor_data['address'] ) ) {
					// Address1.
					$donor_data['address']['line1'] = give_get_meta( $id, '_give_donor_billing_address1', true, isset( $donor_data['address']['line1'] ) ? $donor_data['address']['line1'] : '' );


					// Address2.
					$donor_data['address']['line2'] = give_get_meta( $id, '_give_donor_billing_address1', true, isset( $donor_data['address']['line2'] ) ? $donor_data['address']['line2'] : '' );


					// City.
					$donor_data['address']['city'] = give_get_meta( $id, '_give_donor_billing_city', true, isset( $donor_data['address']['city'] ) ? $donor_data['address']['city'] : '' );


					// Zip.
					$donor_data['address']['zip'] = give_get_meta( $id, '_give_donor_billing_zip', true, isset( $donor_data['address']['zip'] ) ? $donor_data['address']['zip'] : '' );


					// State.
					$donor_data['address']['state'] = give_get_meta( $id, '_give_donor_billing_state', true, isset( $donor_data['address']['state'] ) ? $donor_data['address']['state'] : '' );


					// Country.
					$donor_data['address']['country'] = give_get_meta( $id, '_give_donor_billing_country', true, isset( $donor_data['address']['country'] ) ? $donor_data['address']['country'] : '' );
				}

				$meta_value['user_info'] = is_serialized( $meta_value['user_info'] ) ? serialize( $donor_data ) : $donor_data;
			}

			break;

		case '_give_payment_customer_id':
			$meta_value = give_get_meta( $id, '_give_payment_donor_id', true, $meta_value );
			break;

		case '_give_payment_user_email':
			$meta_value = give_get_meta( $id, '_give_payment_donor_email', true, $meta_value );
			break;

		case '_give_payment_user_ip':
			$meta_value = give_get_meta( $id, '_give_payment_donor_ip', true, $meta_value );
			break;
	}

	return $meta_value;
}

add_filter( 'give_get_meta', '_give_bc_get_payment_meta20', 10, 3 );