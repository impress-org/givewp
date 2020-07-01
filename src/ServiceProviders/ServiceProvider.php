<?php

namespace Give\ServiceProviders;

interface ServiceProvider {

	/**
	 * Registers the Service Provider within the application. Use this to bind anything to the
	 * Service Container. This prepares the service.
	 *
	 * @return void
	 */
	public function register();

	/**
	 * The bootstraps the service after all of the services have been registered. The importance of this
	 * is that any cross service dependencies should be resolved by this point, so it should be safe to
	 * bootstrap the service.
	 *
	 * @return void
	 */
	public function boot();
}
