<?php

namespace Give\Framework\Support\Facades;

/**
 * Class Facade
 *
 * @unreleased
 */
class Facade {
	/**
	 * Static helper for calling the facade methods
	 *
	 * @unreleased
	 *
	 * @param  string  $name
	 * @param  array  $arguments
	 *
	 * @return false|mixed
	 *
	 */
	public static function __callStatic( $name, $arguments ) {
		$instance = give( static::getFacadeClass() );

		return call_user_func_array( [ $instance, $name ], $arguments );
	}

	/**
	 * Each facade will call this method and return the intended class instance
	 *
	 * @unreleased
	 *
	 * @return string
	 */
	protected static function getFacadeClass() {
		return static::class;
	}
}