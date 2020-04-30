<?php
namespace Give\Helpers;

class ArrayDataSet {
	/**
	 * This function will return array with renamed keys.
	 *
	 * This function only support one dimensional array.
	 * You can pass a multi dimensional array but only zero level array keys will be renamed.
	 *
	 * @param array $array
	 * @param array $renameTo Pass array as existing key name as key and new key name as value.
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public static function renameKeys( $array, $renameTo ) {
		// Rename key if property name exist for them.
		foreach ( $renameTo as $oldKey => $newKey ) {
			if ( array_key_exists( $oldKey, $array ) ) {
				$array[ $newKey ] = $array[ $oldKey ];
				unset( $array[ $oldKey ] );
			}
		}

		return $array;
	}
}
