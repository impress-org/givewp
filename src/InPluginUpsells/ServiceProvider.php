<?php

namespace Give\InPluginUpsells;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface {

	public function register() {
	}

	public function boot() {
		Hooks::addAction( 'admin_menu', AdminPage::class, 'register' );

		if ( AdminPage::isShowing() ) {
			Hooks::addAction( 'admin_enqueue_scripts', AdminPage::class, 'loadScripts' );
		}
	}
}
