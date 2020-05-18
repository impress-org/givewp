<?php
namespace Give\Helpers;

use stdClass;

class ArrayDataSet {
	/**
	 * This function will return array with renamed keys.
	 *
	 * This function only support one dimensional array.
	 * You can pass a multi dimensional array but only zero level array keys will be renamed.
	 *
	 * @param array $array
	 * @param array $renameTo Pass array of existing key name as key and new key name as value.
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

	/**
	 * Return whether or not array contains required keys.
	 *
	 * This function only support one dimensional array.
	 *
	 * @since 2.7.0
	 *
	 * @param array $array
	 * @param array $requiredKeys Array of required keys.
	 *
	 * @return bool
	 */
	public static function hasRequiredKeys( $array, $requiredKeys ) {
		return (bool) array_intersect_key( $array, array_flip( $requiredKeys ) );
	}

	/**
	 *  Return array with grouped under specific key.
	 *
	 * @param array  $array
	 * @param array  $itemsToMove
	 * @param string $arrayKey
	 *
	 * @return mixed
	 */
	public static function moveArrayItemsUnderArrayKey( $array, $itemsToMove, $arrayKey ) {
		foreach ( $itemsToMove as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				$array[ $arrayKey ][ $key ] = $array[ $key ];
				unset( $array[ $key ] );
			}
		}

		return $array;
	}

	/**
	 * Return object.
	 *
	 * Note: only applicable on associative Array. Key name must be in camel case.
	 *
	 * @param  array $array
	 *
	 * @return stdClass|stdClass[]
	 */
	public static function convertToObject( $array ) {
		$object = new stdClass();

		foreach ( $array as $property => $value ) {
			$object->{$property} = is_array( $value ) ? self::convertToObject( $value ) : $value;
		}

		return $object;
	}
}
