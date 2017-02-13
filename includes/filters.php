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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add backward compatibility for settings who has disable_ as name prefix.
 * TODO: Remove this backward compatibility when do not need.
 *
 * @since  1.8
 *
 * @param  array  $old_settings Array of settings.
 * @param  array  $settings     Array of settings.
 *
 * @return void
 */
function give_set_settings_with_disable_prefix( $old_settings, $settings ) {
	// Get old setting names.
	$old_settings   = array_flip( give_v18_renamed_core_settings() );
	$update_setting = false;

	foreach ( $settings as $key => $value ) {

		// Check 1. Check if new option is really updated or not.
		// Check 2. Continue if key is not renamed.
		if ( ! isset( $old_settings[ $key ] ) ) {
			continue;
		}

		// Set old setting.
		$settings[ $old_settings[ $key ] ] = 'on';

		// Do not need to set old setting if new setting is not set.
		if (
			( give_is_setting_enabled( $value ) && ( false !== strpos( $old_settings[ $key ], 'disable_' ) ) )
			|| ( ! give_is_setting_enabled( $value ) && ( false !== strpos( $old_settings[ $key ], 'enable_' ) ) )

		) {
			unset( $settings[ $old_settings[ $key ] ] );
		}

		// Tell bot to update setting.
		$update_setting = true;
	}

	// Update setting if any old setting set.
	if ( $update_setting ) {
		update_option( 'give_settings', $settings );
	}
}
add_action( 'update_option_give_settings', 'give_set_settings_with_disable_prefix', 10, 2 );
