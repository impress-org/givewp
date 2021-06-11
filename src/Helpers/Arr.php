<?php


namespace Give\Helpers;

/**
 * Class Arr
 *
 * @unreleased
 */
class Arr {
	/**
	 * Is the array associative?
	 *
	 * @param array $array
	 * @return bool
	 */
	public static function isAssoc( array $array ) {
		$keys = array_keys( $array );
		return array_keys( $keys ) !== $keys;
	}
}
