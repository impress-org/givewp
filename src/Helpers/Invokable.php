<?php

namespace Give\Helpers;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

class Invokable {
	/**
	 * A function that calls an invokable class.
	 *
	 * @unreleased
	 *
	 * @param  string  $class
	 * @param  mixed  $args
	 *
	 * @return void
	 */
	public static function execute( $class, $args = null ) {
		if ( ! method_exists( $class, '__invoke' ) ) {
			throw new InvalidArgumentException( "This class is not invokable" );
		}

		$instance = give( $class );

		$instance($args);
	}
}
