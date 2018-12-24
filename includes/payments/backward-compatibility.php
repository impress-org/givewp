<?php
/**
 * Split _give_payment_meta to new Give core meta_keys.
 *
 * @since 2.0
 *
 * @param       $object_id
 * @param array $meta_value
 *
 * @return void
 */
function _give_20_bc_split_and_save_give_payment_meta( $object_id, $meta_value ) {
	// Bailout
	if ( empty( $meta_value ) ) {
		return;
	} elseif ( ! is_array( $meta_value ) ) {
		$meta_value = array();
	}

	remove_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10 );

	// Date payment meta.
	if ( ! empty( $meta_value['date'] ) ) {
		give_update_meta( $object_id, '_give_payment_date', $meta_value['date'] );
	}

	// Currency payment meta.
	if ( ! empty( $meta_value['currency'] ) ) {
		give_update_meta( $object_id, '_give_payment_currency', $meta_value['currency'] );
	}

	// User information.
	if ( ! empty( $meta_value['user_info'] ) ) {
		// Donor first name.
		if ( ! empty( $meta_value['user_info']['first_name'] ) ) {
			give_update_meta( $object_id, '_give_donor_billing_first_name', $meta_value['user_info']['first_name'] );
		}

		// Donor last name.
		if ( ! empty( $meta_value['user_info']['last_name'] ) ) {
			give_update_meta( $object_id, '_give_donor_billing_last_name', $meta_value['user_info']['last_name'] );
		}

		// Donor address payment meta.
		if ( ! empty( $meta_value['user_info']['address'] ) ) {

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
	}// End if().

	add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );
}

/**
 * Add backward compatibility to get meta value of _give_payment_meta meta key.
 *
 * @since 2.0
 *
 * @param       $object_id
 * @param array $meta_value
 *
 * @return array
 */
function _give_20_bc_give_payment_meta_value( $object_id, $meta_value ) {
	$cache_key = "_give_payment_meta_{$object_id}";
	$cache     = Give_Cache::get_db_query( $cache_key );

	if ( ! is_null( $cache ) ) {
		return $cache;
	}

	// Set default value to array.
	if ( ! is_array( $meta_value ) ) {
		$meta_value = array();
	}

	// Donation key.
	$meta_value['key'] = give_get_meta( $object_id, '_give_payment_purchase_key', true );

	// Donation form.
	$meta_value['form_title'] = give_get_meta( $object_id, '_give_payment_form_title', true );

	// Donor email.
	$meta_value['email'] = give_get_meta( $object_id, '_give_payment_donor_email', true );
	$meta_value['email'] = ! empty( $meta_value['email'] ) ?
		$meta_value['email'] :
		Give()->donors->get_column( 'email', give_get_payment_donor_id( $object_id ) );

	// Form id.
	$meta_value['form_id'] = give_get_meta( $object_id, '_give_payment_form_id', true );

	// Price id.
	$meta_value['price_id'] = give_get_meta( $object_id, '_give_payment_price_id', true );

	// Date.
	$meta_value['date'] = give_get_meta( $object_id, '_give_payment_date', true );
	$meta_value['date'] = ! empty( $meta_value['date'] ) ?
		$meta_value['date'] :
		get_post_field( 'post_date', $object_id );

	// Currency.
	$meta_value['currency'] = give_get_meta( $object_id, '_give_payment_currency', true );

	// Decode donor data.
	$donor_names = give_get_donor_name_by( give_get_meta( $object_id, '_give_payment_donor_id', true ), 'donor' );
	$donor_names = explode( ' ', $donor_names, 2 );

	// Donor first name.
	$donor_data['first_name'] = give_get_meta( $object_id, '_give_donor_billing_first_name', true );
	$donor_data['first_name'] = ! empty( $donor_data['first_name'] ) ?
		$donor_data['first_name'] :
		$donor_names[0];

	// Donor last name.
	$donor_data['last_name'] = give_get_meta( $object_id, '_give_donor_billing_last_name', true );
	$donor_data['last_name'] = ! empty( $donor_data['last_name'] ) ?
		$donor_data['last_name'] :
		( isset( $donor_names[1] ) ? $donor_names[1] : '' );

	// Donor email.
	$donor_data['email'] = $meta_value['email'];

	// User ID.
	$donor_data['id'] = give_get_payment_user_id( $object_id );

	$donor_data['address'] = false;

	// Address1.
	if ( $address1 = give_get_meta( $object_id, '_give_donor_billing_address1', true ) ) {
		$donor_data['address']['line1'] = $address1;
	}

	// Address2.
	if ( $address2 = give_get_meta( $object_id, '_give_donor_billing_address2', true ) ) {
		$donor_data['address']['line2'] = $address2;
	}

	// City.
	if ( $city = give_get_meta( $object_id, '_give_donor_billing_city', true ) ) {
		$donor_data['address']['city'] = $city;
	}

	// Zip.
	if ( $zip = give_get_meta( $object_id, '_give_donor_billing_zip', true ) ) {
		$donor_data['address']['zip'] = $zip;
	}

	// State.
	if ( $state = give_get_meta( $object_id, '_give_donor_billing_state', true ) ) {
		$donor_data['address']['state'] = $state;
	}

	// Country.
	if ( $country = give_get_meta( $object_id, '_give_donor_billing_country', true ) ) {
		$donor_data['address']['country'] = $country;
	}

	$meta_value['user_info'] = maybe_unserialize( $donor_data );

	Give_Cache::set_db_query( $cache_key, $meta_value );

	return $meta_value;
}

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
	if( 'give_payment' !== get_post_type( $object_id ) ) {
		return $check;
	}

	// Bailout.
	if (
		! in_array( $meta_key, array(
			'_give_payment_meta',
			'_give_payment_user_email',
			'_give_payment_customer_id',
			'give_payment_user_ip',
		) )
	) {
		return $check;
	}

	if ( '_give_payment_meta' === $meta_key ) {
		_give_20_bc_split_and_save_give_payment_meta( $object_id, $meta_value );
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
	global $wpdb;

	// Early exit.
	if( 'give_payment' !== get_post_type( $object_id ) ) {
		return $check;
	}
	// Deprecated meta keys.
	$old_meta_keys = array(
		'_give_payment_customer_id',
		'_give_payment_user_email',
		'_give_payment_user_ip',
	);

	// Add _give_payment_meta to backward compatibility
	if ( ! give_has_upgrade_completed( 'v20_upgrades_payment_metadata' ) ) {
		$old_meta_keys[] = '_give_payment_meta';
	}

	// Bailout.
	if ( ! in_array( $meta_key, $old_meta_keys ) ) {
		return $check;
	}

	$cache_key = "{$meta_key}_{$object_id}";
	$check     = Give_Cache::get_db_query( $cache_key );

	if ( is_null( $check ) ) {
		switch ( $meta_key ) {

			// Handle old meta keys.
			case '_give_payment_meta':
				remove_filter( 'get_post_metadata', '_give_20_bc_get_old_payment_meta' );

				// if ( $meta_value = give_get_meta( $object_id, '_give_payment_meta' ) ) {
				$meta_value = ! empty( $meta_value ) ?
					current( $meta_value ) :
					(array) maybe_unserialize(
						$wpdb->get_var(
							$wpdb->prepare(
								"
								SELECT meta_value
								FROM $wpdb->postmeta
								WHERE post_id=%d
								AND meta_key=%s
								",
								$object_id,
								'_give_payment_meta'
							)
						)
					);
				$check      = _give_20_bc_give_payment_meta_value( $object_id, $meta_value );
				// }

				add_filter( 'get_post_metadata', '_give_20_bc_get_old_payment_meta', 10, 5 );

				break;

			case '_give_payment_customer_id':
				if ( $donor_id = give_get_meta( $object_id, '_give_payment_donor_id', $single ) ) {
					$check = $donor_id;
				}
				break;

			case '_give_payment_user_email':
				if ( $donor_email = give_get_meta( $object_id, '_give_payment_donor_email', $single ) ) {
					$check = $donor_email;
				}
				break;

			case '_give_payment_user_ip':
				if ( $donor_ip = give_get_meta( $object_id, '_give_payment_donor_ip', $single ) ) {
					$check = $donor_ip;
				}
				break;
		}// End switch().

		Give_Cache::set_db_query( $cache_key, $check );
	}

	// Put result in an array on zero index.
	if ( ! is_null( $check ) ) {
		$check = array( $check );
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

	// Early exit.
	if( 'give_payment' !== get_post_type( $object_id ) ) {
		return $check;
	}

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
		! in_array( $meta_key, $new_meta_keys ) ||
		metadata_exists( 'post', $object_id, $meta_key )
	) {
		add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );

		return $check;
	}

	add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );

	$cache_key = "{$meta_key}_{$object_id}";
	$check    = Give_Cache::get_db_query( $cache_key );

	if ( is_null( $check ) ) {
		switch ( $meta_key ) {

			// Handle new meta keys.
			case '_give_payment_donor_id':
				$check = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s",
						$object_id,
						'_give_payment_customer_id'
					)
				);
				break;

			case '_give_payment_donor_email':
				$check = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s",
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
				$donation_meta = Give_Cache::get_db_query( "_give_payment_meta_{$object_id}" );

				if ( is_null( $donation_meta ) ) {
					$donation_meta = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_key=%s",
							$object_id,
							'_give_payment_meta'
						)
					);
					$donation_meta = maybe_unserialize( $donation_meta );
					$donation_meta = ! is_array( $donation_meta ) ? array() : $donation_meta;
					Give_Cache::set_db_query( "_give_payment_meta_{$object_id}", $donation_meta );
				}

				// Get results.
				if ( empty( $donation_meta ) ) {
					$check = '';
				} elseif ( in_array( $meta_key, array( '_give_payment_date', '_give_payment_currency' ) ) ) {
					$payment_meta_key = str_replace( '_give_payment_', '', $meta_key );

					if ( isset( $donation_meta[ $payment_meta_key ] ) ) {
						$check = $donation_meta[ $payment_meta_key ];
					}
				} else {
					$payment_meta_key = str_replace( '_give_donor_billing_', '', $meta_key );

					switch ( $payment_meta_key ) {
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

						case 'first_name':
							if ( isset( $donation_meta['user_info']['first_name'] ) ) {
								$check = $donation_meta['user_info']['first_name'];
							}
							break;

						case 'last_name':
							if ( isset( $donation_meta['user_info']['last_name'] ) ) {
								$check = $donation_meta['user_info']['last_name'];
							}
							break;

						default:
							if ( isset( $donation_meta['user_info']['address'][ $payment_meta_key ] ) ) {
								$check = $donation_meta['user_info']['address'][ $payment_meta_key ];
							}
					}
				}

				break;
		}// End switch().

		// Set cache.
		Give_Cache::set_db_query( $cache_key, $check );
	}

	// Put result in an array on zero index.
	if ( ! $single ) {
		$check = array( $check );
	}


	return $check;
}

// Apply filter only if upgrade does not complete yet.
if ( ! give_has_upgrade_completed( 'v20_upgrades_payment_metadata' ) ) {
	add_filter( 'get_post_metadata', '_give_20_bc_get_new_payment_meta', 10, 5 );
}


/**
 * Add support for old payment meta keys.
 *
 * @since 2.0
 *
 * @param WP_Query $query
 *
 * @return void
 */
function _give_20_bc_support_deprecated_meta_key_query( $query ) {
	$new_meta_keys = array(
		'_give_payment_customer_id' => '_give_payment_donor_id',
		'_give_payment_user_email'  => '_give_payment_donor_email',
		// '_give_payment_user_ip'     => '_give_payment_donor_ip',
	);

	$deprecated_meta_keys = array_flip( $new_meta_keys );

	// Set meta keys.
	$meta_keys = array();


	// Bailout.
	if ( ! empty( $query->query_vars['meta_key'] ) ) {
		if ( in_array( $query->query_vars['meta_key'], $new_meta_keys ) ) {
			$meta_keys = $deprecated_meta_keys;
		} elseif ( in_array( $query->query_vars['meta_key'], $deprecated_meta_keys ) ) {
			$meta_keys = $new_meta_keys;
		}

		if ( ! empty( $meta_keys ) ) {
			// Set meta_query
			$query->set(
				'meta_query',
				array(
					'relation' => 'OR',
					array(
						'key'   => $query->query_vars['meta_key'],
						'value' => $query->query_vars['meta_value'],
					),
					array(
						'key'   => $meta_keys[ $query->query_vars['meta_key'] ],
						'value' => $query->query_vars['meta_value'],
					),
				)
			);

			// Unset single meta query.
			unset( $query->query_vars['meta_key'] );
			unset( $query->query_vars['meta_value'] );
		}
	} elseif (
		! empty( $query->query_vars['meta_query'] ) &&
		( 1 === count( $query->query_vars['meta_query'] ) )
	) {
		$meta_query = current( $query->query_vars['meta_query'] );

		if ( empty( $meta_query[0]['key'] ) ) {
			return;
		}

		if ( in_array( $meta_query[0]['key'], $new_meta_keys ) ) {
			$meta_keys = $deprecated_meta_keys;
		} elseif ( in_array( $meta_query[0]['key'], $deprecated_meta_keys ) ) {
			$meta_keys = $new_meta_keys;
		} else {
			return;
		}

		if ( ! empty( $meta_keys ) ) {
			// Set meta_query
			$query->set(
				'meta_query',
				array(
					'relation' => 'OR',
					array(
						'key'   => $query->query_vars['meta_query'][0]['key'],
						'value' => $query->query_vars['meta_query'][0]['value'],
					),
					array(
						'key'   => $meta_keys[ $query->query_vars['meta_query'][0]['key'] ],
						'value' => $query->query_vars['meta_query'][0]['value'],
					),
				)
			);
		}
	}
}

// Apply filter only if upgrade does not complete.
if ( ! give_has_upgrade_completed( 'v20_upgrades_payment_metadata' ) ) {
	add_action( 'pre_get_posts', '_give_20_bc_support_deprecated_meta_key_query' );
}

/**
 * Save payment backward compatibility.
 * Note: some addon still can use user_info in set payment meta
 *       we will use this info to set first name, last name and address of donor
 *
 * @since 2.0
 *
 * @param Give_Payment $payment
 * @param string       $key
 */
function _give_20_bc_payment_save( $payment, $key ) {
	switch ( $key ) {
		case 'user_info':
			if ( empty( $payment->user_info ) ) {
				// Bailout.
				break;
			} elseif ( is_string( $payment->user_info ) ) {
				// Check if value serialize.
				$payment->user_info = maybe_unserialize( $payment->user_info );
			}


			// Save first name.
			if ( isset( $payment->user_info['first_name'] ) ) {
				$payment->update_meta( '_give_donor_billing_first_name', $payment->user_info['first_name'] );
			}


			// Save last name.
			if ( isset( $payment->user_info['last_name'] ) ) {
				$payment->update_meta( '_give_donor_billing_last_name', $payment->user_info['last_name'] );
			}


			// Save address.
			if ( ! empty( $payment->user_info['address'] ) ) {
				foreach ( $payment->user_info['address'] as $address_name => $address ) {
					switch ( $address_name ) {
						case 'line1':
							$payment->update_meta( '_give_donor_billing_address1', $address );
							break;

						case 'line2':
							$payment->update_meta( '_give_donor_billing_address2', $address );
							break;

						default:
							$payment->update_meta( "_give_donor_billing_{$address_name}", $address );
					}
				}
			}

			break;
	}
}


// Apply filter only if upgrade complete.
if ( give_has_upgrade_completed( 'v20_upgrades_payment_metadata' ) ) {
	add_action( 'give_payment_save', '_give_20_bc_payment_save', 10, 2 );
}


/**
 * Delete pre upgrade cache for donations.
 *
 * @since 2.0
 *
 * @param $check
 * @param $object_id
 *
 * @return mixed
 */
function __give_20_bc_flush_cache( $check, $object_id ) {
	if ( 'give_payment' === get_post_type( $object_id ) ) {
		Give_Cache::delete_group( $object_id, 'give-donations' );
	}

	return $check;
}

// Apply only if upgrade does not complete.
if( ! give_has_upgrade_completed( 'v20_move_metadata_into_new_table' ) ) {
	add_action( 'update_post_metadata', '__give_20_bc_flush_cache', 9999, 2 );
	add_action( 'add_post_metadata', '__give_20_bc_flush_cache', 9999, 2 );
}
