<?php

namespace Give\Framework\Support\Facades;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * Class Facade
 *
 * This class provides a way of taking a normal instance class and creating a static facade out of it. It does it in
 * such a way, though, that the facade is still mockable. It does this by instantiating the decorated class through the
 * Give Service Container (SC). So by injecting a mock singleton of the decorated class in the SC, it can be mocked.
 *
 * To use this, simply make a new facade class which extends this once, then override the getFacadeClass and return the
 * class to be decorated, for example: return MyClass::class;
 *
 * To help the IDE, take the methods from the decorated class and add them your class docblock. So if Repository had an
 * insert method, you would add "@method static Model insert()" to the top.
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
