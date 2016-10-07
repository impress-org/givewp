<?php
/**
 * Front-end Filters
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add backward compatibility for option who has disable_ as name prefix.
 *
 * @since  1.8
 * @param  mixed  $value
 * @param  string $key
 *
 * @return string
 */
function give_set_option_with_disable_prefix( $value, $key ) {
	$give_setting = give_get_settings();

	// Bailout.
	if( false === strpos( $key, 'disable_' ) ) {
		return $value;
	}

	$new_key = str_replace( 'disable_', 'enable_', $key );

	// Bailout.
	if( array_key_exists( $new_key, $give_setting ) ) {
		return $value;
	}

	return ( give_is_setting_enabled( $value ) ? '' : 'on' );
}
add_filter( 'give_get_option', 'give_set_option_with_disable_prefix', 10, 2 );


/**
 * Add backward compatibility for settings who has disable_ as name prefix.
 *
 * @since  1.8
 * @param  array $settings
 *
 * @return array
 */
function give_set_settings_with_disable_prefix( $settings ) {
	// Bailout.
	if( empty( $settings ) ) {
		return $settings;
	}
	
	foreach ( $settings as $key => $value ) {
		// Bailout.
		if( false === strpos( $key, 'enable_' ) ){
			continue;
		}

		// Set old setting key.
		$settings[ str_replace( 'enable_', 'disable_',$key ) ] = ( give_is_setting_enabled( $value ) ? '' : 'on' );
	}

	return $settings;
}
add_filter( 'give_get_settings', 'give_set_settings_with_disable_prefix' );