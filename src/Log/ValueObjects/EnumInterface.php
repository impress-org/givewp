<?php
namespace Give\Log\ValueObjects;

interface EnumInterface {
	/**
	 * Get value
	 *
	 * @return string|null
	 */
	public function getValue();

	/**
	 * Get default value
	 *
	 * @return string
	 */
	public static function getDefault();

	/**
	 * Check if Enum is equal with the passed variable
	 *
	 * @param  mixed  $value
	 *
	 * @return bool
	 */
	public function equalsTo( $value );
}
