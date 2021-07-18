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
 * To use this, simply make a new facade class which extends this once, then implement the getFacadeClass and return the
 * class to be decorated, for example: return MyClass::class;
 *
 * To help the IDE, take the methods from the decorated class and add them your class docblock. So if Repository had an
 * insert method, you would add "@method static Model insert()" to the top.
 *
 * @unreleased
 */
abstract class Facade {
	/**
	 * Static helper for calling the facade methods
	 *
	 * @unreleased
	 *
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		give()->singletonIf( static::class );
		$staticInstance = give( static::class );

		$facadeClass = $staticInstance->getFacadeClass();
		give()->singletonIf( $facadeClass );
		$facadeInstance = give( $facadeClass );

		return $facadeInstance->$name( ...$arguments );
	}

	/**
	 * Retrieves the fully qualified class name or alias for the class being decorated
	 *
	 * @unreleased
	 *
	 * @return string
	 */
	abstract protected function getFacadeClass();
}
