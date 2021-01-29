<?php

namespace Give\DonorProfiles\Tabs\Contracts;

use RuntimeException;

/**
 * Class Tab
 *
 * Extend this class when creating Donor Profile tabs.
 *
 * @since 2.11.0
 */
abstract class Tab {
	/**
	 * Return array of routes (must extend DonorProfile Route class)
	 *
	 * @return array
	 * @since 2.11.0
	 */
	abstract public function routes();

	/**
	 * Return a unique identifier for the tab
	 *
	 * @return string
	 * @since 2.11.0
	 */
	public static function id() {
		throw new RuntimeException( 'A unique ID must be provided for the tab' );
	}

	/**
	 * Registers routes with WP REST api
	 *
	 * @since 2.11.0
	 */
	public function registerRoutes() {
		$routeClasses = $this->routes();
		foreach ( $routeClasses as $routeClass ) {
			if ( ! is_subclass_of( $routeClass, Route::class ) ) {
				throw new \InvalidArgumentException( 'Class must extend the ' . Route::class . ' class' );
			}
			( new $routeClass )->registerRoute();
		}

	}
}
