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
 *  1. User can only set absolute integer value as number of decimals.
 *  2. number_decimals setting will be zero if no decimal separator defined
 *
 * @since   1.8
 * @used-by Give_Plugin_Settings::give_settings()
 *
 * @param   string $value
 *
 * @return  mixed
 */
function __give_sanitize_number_decimals_setting_field( $value ) {
	$value_changed = false;
	$old_value     = $value;

	if ( isset( $_POST['decimal_separator'] ) ) {
		$value         = ! empty( $_POST['decimal_separator'] ) ? $value : 0;
		$value_changed = true;
	}

	if ( $value_changed && ( $old_value != $value ) ) {
		Give_Admin_Settings::add_error( 'give-number-decimal', __( 'The \'Number of Decimals\' option has been automatically set to zero because the \'Decimal Separator\' is not set.', 'give' ) );
	}

	$value = absint( $value );

	if( 6 <= $value ) {
		$value = 5;
		Give_Admin_Settings::add_error( 'give-number-decimal', __( 'The \'Number of Decimals\' option has been automatically set to 5 because you entered a number higher than the maximum allowed.', 'give' ) );
	}

	return absint( $value );
}

add_filter( 'give_admin_settings_sanitize_option_number_decimals', '__give_sanitize_number_decimals_setting_field', 10 );


/**
 * Sanitize number of decimals setting field.
 *
 *  1. User can only set absolute integer value as number of decimals.
 *  2. number_decimals setting will be zero if no decimal separator defined
 *
 * @since   1.8
 * @used-by Give_Plugin_Settings::give_settings()
 *
 * @param   string $value
 *
 * @return  mixed
 */
function __give_validate_decimal_separator_setting_field( $value ) {
	$thousand_separator = give_clean( $_POST['thousands_separator'] );
	$decimal_separator  = give_clean( $_POST['decimal_separator'] );

	if ( $decimal_separator === $thousand_separator ) {
		$value                    = '';
		$_POST['number_decimals'] = 0;
		Give_Admin_Settings::add_error( 'give-decimal-separator', __( 'The \'Decimal Separator\' option has automatically been set to empty because it can not be equal to the \'Thousand Separator\'', 'give' ) );
	}

	return $value;
}

add_filter( 'give_admin_settings_sanitize_option_decimal_separator', '__give_validate_decimal_separator_setting_field', 10 );