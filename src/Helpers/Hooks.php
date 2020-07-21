<?php

namespace Give\Helpers;

use InvalidArgumentException;

class Hooks {
	/**
	 * A function which extends the WordPress add_action method to handle the instantiation of a class
	 * once the action is fired. This prevents the need to instantiate a class before adding it to hook.
	 *
	 * @since 2.8.0
	 *
	 * @param string $tag
	 * @param string $class
	 * @param string $method
	 * @param int    $priority
	 * @param int    $acceptedArgs
	 *
	 * @return void
	 */
	public static function addAction( $tag, $class, $method = '__invoke', $priority = 10, $acceptedArgs = 1 ) {
		if ( ! method_exists( $class, $method ) ) {
			throw new InvalidArgumentException( "The method $method does not exist on $class" );
		}

		add_action(
			$tag,
			static function () use ( $tag, $class, $method ) {
				// Provide a way of disabling the hook
				if ( apply_filters( "give_disable_hook-{$tag}", false ) || apply_filters( "give_disable_hook-{$tag}:{$class}@{$method}", false ) ) {
					return;
				}

				$instance = give( $class );

				call_user_func_array( [ $instance, $method ], func_get_args() );
			},
			$priority,
			$acceptedArgs
		);
	}

	/**
	 * A function which extends the WordPress add_filter method to handle the instantiation of a class
	 * once the filter is fired. This prevents the need to instantiate a class before adding it to hook.
	 *
	 * @since 2.8.0
	 *
	 * @param string $tag
	 * @param string $class
	 * @param string $method
	 * @param int    $priority
	 * @param int    $acceptedArgs
	 *
	 * @return void
	 */
	public static function addFilter( $tag, $class, $method = '__invoke', $priority = 10, $acceptedArgs = 1 ) {
		if ( ! method_exists( $class, $method ) ) {
			throw new InvalidArgumentException( "The method $method does not exist on $class" );
		}

		add_filter(
			$tag,
			static function () use ( $tag, $class, $method ) {
				// Provide a way of disabling the hook
				if ( apply_filters( "give_disable_hook-{$tag}", false ) || apply_filters( "give_disable_hook-{$tag}:{$class}@{$method}", false ) ) {
					return func_get_arg( 0 );
				}

				$instance = give( $class );

				return call_user_func_array( [ $instance, $method ], func_get_args() );
			},
			$priority,
			$acceptedArgs
		);
	}
}
