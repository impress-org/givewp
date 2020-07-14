<?php

namespace Give\ServiceProviders;

/**
 * Interface ServiceProvider
 *
 * For use when defining Service Providers, see the method docs for when to use them
 *
 * @since 2.8.0
 */
interface ServiceProvider {
	/**
	 * Registers the Service Provider within the application. Use this to bind anything to the
	 * Service Container. This prepares the service.
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function register();

	/**
	 * The bootstraps the service after all of the services have been registered. The importance of this
	 * is that any cross service dependencies should be resolved by this point, so it should be safe to
	 * bootstrap the service.
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function boot();
}
