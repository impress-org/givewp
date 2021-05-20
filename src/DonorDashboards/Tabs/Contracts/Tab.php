<?php

namespace Give\DonorDashboards\Tabs\Contracts;

use Give\Framework\Exceptions\Primitives\RuntimeException;
use Give\DonorDashboards\Tabs\Contracts\Route as RouteAbstract;

/**
 * Class Tab
 *
 * Extend this class when creating Donor Profile tabs.
 *
 * @since 2.10.0
 */
abstract class Tab {
	/**
	 * Return array of routes (must extend DonorDashboard Route class)
	 *
	 * @return array
	 * @since 2.10.0
	 */
	abstract public function routes();

	/**
	 * Return a unique identifier for the tab
	 *
	 * @return string
	 * @since 2.10.0
	 */
	public static function id() {
		throw new RuntimeException( 'A unique ID must be provided for the tab' );
	}


	/**
	 * Enqueue assets required for frontend rendering of tab
	 *
	 * @since 2.10.0
	 */
	public function enqueueAssets() {
		return null;
	}

	/**
	 * Registers routes with WP REST api
	 *
	 * @since 2.10.0
	 */
	public function registerRoutes() {
		$routeClasses = $this->routes();
		foreach ( $routeClasses as $routeClass ) {
			if ( ! is_subclass_of( $routeClass, RouteAbstract::class ) ) {
				throw new \InvalidArgumentException( $routeClass . ' must extend the ' . RouteAbstract::class . ' class' );
			}
			( new $routeClass )->registerRoute();
		}
	}

	public function registerTab() {
		give()->donorDashboardTabs->addTab( get_called_class() );
	}
}
