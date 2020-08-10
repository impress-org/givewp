<?php

namespace Give\Onboarding\Helpers;

/**
 * Formats an associative array into a JS parsable array of objects.
 *
 * @since 2.8.0
 */
class FormatList {

	/**
	 * @param array $data
	 *
	 * @return array
	 *
	 * @since 2.8.0
	 */
	public static function fromKeyValue( $data ) {
		return self::format(
			$data,
			function( $key, $label ) {
				return [
					'value' => $key,
					'label' => $label,
				];
			}
		);
	}

	public static function fromValueKey( $data ) {
		return self::format(
			$data,
			function( $label, $key ) {
				return [
					'value' => $key,
					'label' => $label,
				];
			}
		);
	}

	protected static function format( $data, $function ) {
		return array_map(
			$function,
			array_keys( $data ),
			array_values( $data )
		);
	}
}
