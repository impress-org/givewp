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
 * @param  array  $settings    Array of settings.
 * @param  string $option_name Setting name.
 *
 * @return void
 */
function give_set_settings_with_disable_prefix( $settings, $option_name ) {
	// Get old setting names.
	$old_settings   = give_v18_renamed_core_settings();
	$update_setting = false;

	foreach ( $settings as $key => $value ) {

		// Check 1. Check if new option is really updated or not.
		// Check 2. Continue if key is not renamed.
		if (
			! isset( $_POST[ $key ] )
			|| false === ( $old_setting_name = array_search( $key, $old_settings ) )
		) {
			continue;
		}

		// Set old setting.
		$settings[ $old_setting_name ] = 'on';

		// Do not need to set old setting if new setting is not set.
		if ( give_is_setting_enabled( $value ) ) {
			unset( $settings[ $old_setting_name ] );
		}

		// Tell bot to update setting.
		$update_setting = true;
	}

	// Update setting if any old setting set.
	if ( $update_setting ) {
		update_option( $option_name, $settings );
	}
}

add_filter( 'give_save_settings_give_settings', 'give_set_settings_with_disable_prefix', 10, 2 );
