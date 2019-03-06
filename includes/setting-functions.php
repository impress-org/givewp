<?php
/**
 * Helps get a single option from the give_get_settings() array.
 *
 * @since  0.1.0
 *
 * @param  string      $key     Options array key
 * @param  string|bool $default The default option if the option isn't set
 *
 * @return mixed        Option value
 */
function give_get_option( $key = '', $default = false ) {
	$give_options = give_get_settings();
	$value        = ! empty( $give_options[ $key ] ) ? $give_options[ $key ] : $default;
	$value        = apply_filters( 'give_get_option', $value, $key, $default );

	return apply_filters( "give_get_option_{$key}", $value, $key, $default );
}


/**
 * Update an option
 *
 * Updates an give setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the give_options array.
 *
 * @since 1.0
 *
 * @param string          $key   The Key to update
 * @param string|bool|int $value The value to set the key to
 *
 * @return boolean True if updated, false if not.
 */
function give_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = give_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings.
	$options = give_get_settings();

	// Let's developers alter that value coming in.
	$value = apply_filters( 'give_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'give_settings', $options, false );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $give_options;
		$give_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an option
 *
 * Removes an give setting value in both the db and the global variable.
 *
 * @since 1.0
 *
 * @global       $give_options
 *
 * @param string $key The Key to delete
 *
 * @return boolean True if updated, false if not.
 */
function give_delete_option( $key = '' ) {

	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'give_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'give_settings', $options, false );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $give_options;
		$give_options = $options;
	}

	return $did_update;
}


/**
 * Get Settings
 *
 * Retrieves all Give plugin settings
 *
 * @since 1.0
 * @return array Give settings
 */
function give_get_settings() {
	return Give_Cache_Setting::get_settings();
}

/**
 * Check if radio(enabled/disabled) and checkbox(on) is active or not.
 *
 * @since  1.8
 *
 * @param  mixed  $value
 * @param  string $compare_with
 *
 * @return bool
 */
function give_is_setting_enabled( $value, $compare_with = null ) {
	if ( ! is_null( $compare_with ) ) {

		if ( is_array( $compare_with ) ) {
			// Output.
			return in_array( $value, $compare_with );
		}

		// Output.
		return ( $value === $compare_with );
	}

	// Backward compatibility: From version 1.8 most of setting is modified to enabled/disabled
	// Output.
	return ( in_array( $value, array( 'enabled', 'on', 'yes' ) ) ? true : false );
}

/**
 * Verify admin setting nonce
 *
 * @since  2.4.0
 * @access public
 *
 * @return bool
 */
function give_is_saving_settings() {
	if (
		empty( $_REQUEST['_give-save-settings'] )
		|| ! wp_verify_nonce( $_REQUEST['_give-save-settings'], 'give-save-settings' )
	) {
		return false;
	}

	return true;
}


/**
 * Give Settings Array Insert.
 *
 * Allows other Add-ons and plugins to insert Give settings at a desired position.
 *
 * @since      1.3.5
 *
 * @param $array
 * @param $position |int|string Expects an array key or 'id' of the settings field to appear after
 * @param $insert   |array a valid array of options to insert
 *
 * @return array
 */
function give_settings_array_insert( $array, $position, $insert ) {
	if ( is_int( $position ) ) {
		array_splice( $array, $position, 0, $insert );
	} else {

		foreach ( $array as $index => $subarray ) {
			if ( isset( $subarray['id'] ) && $subarray['id'] == $position ) {
				$pos = $index;
			}
		}

		if ( ! isset( $pos ) ) {
			return $array;
		}

		$array = array_merge(
			array_slice( $array, 0, $pos ),
			$insert,
			array_slice( $array, $pos )
		);
	}

	return $array;
}
