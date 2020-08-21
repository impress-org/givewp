<?php

namespace Give\Onboarding\Helpers;

/**
 * Normalize format of location type lists.
 * @since 2.8.0
 */
class LocationList {

	public static function getCountries() {
		$countries = give_get_country_list();
		unset( $countries[''] );
		return FormatList::fromKeyValue( $countries );
	}

	public static function getStates( $country ) {
		$states     = give_get_states( $country );
		$states[''] = sprintf( '%s...', esc_html__( 'Select', 'give' ) );
		return FormatList::fromKeyValue( $states );
	}
}
