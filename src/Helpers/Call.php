<?php

namespace Give\Helpers;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

class Call {
	/**
	 * Call an invokable class.
	 *
	 * @unreleased
	 *
	 * @param  string  $class
	 * @param  mixed  $args
	 *
	 * @return callable
	 */
	public static function invoke( $class, ...$args ) {
		if ( ! method_exists( $class, '__invoke' ) ) {
			throw new InvalidArgumentException( "This class is not invokable" );
		}

		/** @var callable $instance */
		$instance = give( $class );

		return $instance(...$args);
	}
}
