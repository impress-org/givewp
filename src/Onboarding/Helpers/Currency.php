<?php

namespace Give\Onboarding\Helpers;

/**
 * Formats an associative array into a JS parsable array of objects.
 */
class Currency {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function getConfiguration( $countryCode ) {
		$currencyList = give_get_currencies_list();
		return $currencyList[ $countryCode ]['setting'];
	}
}
