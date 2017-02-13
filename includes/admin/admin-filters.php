<?php
/**
 * Admin Filters
 *
 * @package     Give
 * @subpackage  Admin/Filters
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize number of decimals setting field.
 *
 * User can only set absolute integer value as number of decimals.
 *
 * @since   1.8
 * @used-by Give_Plugin_Settings::give_settings()
 *
 * @param   string $value
 *
 * @return  mixed
 */
function _give_sanitize_number_decimals_setting_field( $value ) {
	return absint( $value );
}
add_filter( 'give_admin_settings_sanitize_option_number_decimals', '_give_sanitize_number_decimals_setting_field', 10 );