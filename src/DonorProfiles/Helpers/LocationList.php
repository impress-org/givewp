<?php

namespace Give\DonorProfiles\Helpers;

use FormatObjectList;

/**
 * Normalize format of location type lists.
 * @since 2.8.0
 */
class LocationList {

	public static function getCountries() {
		$countries = give_get_country_list();
		unset( $countries[''] );
		return FormatObjectList\Factory::fromKeyValue( $countries );
	}

	public static function getStates( $country ) {
		$states     = give_get_states( $country );
		$states[''] = sprintf( '%s...', esc_html__( 'Select', 'give' ) );
		return FormatObjectList\Factory::fromKeyValue( $states );
	}
}
