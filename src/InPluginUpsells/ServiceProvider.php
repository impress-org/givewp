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
		Hooks::addAction( 'admin_menu', AddonsAdminPage::class, 'register' );

		if ( AddonsAdminPage::isShowing() ) {
			Hooks::addAction( 'admin_enqueue_scripts', AddonsAdminPage::class, 'loadScripts' );
		}

        if ( RecurringDonationsTab::isShowing() ) {
            Hooks::addAction( 'admin_enqueue_scripts', RecurringDonationsTab::class, 'loadScripts' );
        }
	}
}
