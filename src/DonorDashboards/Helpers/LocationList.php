<?php

namespace Give\DonorDashboards\Helpers;

use FormatObjectList;

/**
 * Normalize format of location type lists.
 * @since 2.10.0
 */
class LocationList {

	public static function getCountries() {
		$countries = give_get_country_list();
		unset( $countries[''] );
		$formatter = FormatObjectList\Factory::fromKeyValue( $countries );
		return $formatter->format();
	}

	public static function getStates( $country ) {
		$states     = give_get_states( $country );
		$states[''] = sprintf( '%s...', esc_html__( 'Select', 'give' ) );
		$formatter  = FormatObjectList\Factory::fromKeyValue( $states );
		return $formatter->format();
	}
}
