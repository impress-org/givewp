<?php

namespace Give\Framework\Support\Facades;

use Give\Framework\Exceptions\Primitives\Exception;

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
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 * @throws Exception
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
	 * @throws Exception
	 */
	protected static function getFacadeClass() {
		throw new Exception( 'This method must be overridden and return the class to decorate' );
	}
}
