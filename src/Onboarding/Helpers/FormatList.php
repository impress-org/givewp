<?php

namespace Give\Onboarding\Helpers;

/**
 * Formats an associative array into a JS parsable array of objects.
 */
class FormatList {

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function fromKeyValue( $data ) {
		$keys = array_keys( $data );
		return array_map(
			function( $key, $label ) {
				return [
					'value' => $key,
					'label' => $label,
				];
			},
			$keys,
			$data
		);
	}
}
