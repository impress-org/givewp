<?php

namespace Give\FieldsAPI;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\FieldsAPI\Commands\DeprecateOldTemplateHook;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		include_once plugin_dir_path( __FILE__ ) . '/functions.php';
		give()->bind( DeprecateOldTemplateHook::class, function() {
			global $wp_filter;
			return new DeprecateOldTemplateHook( $wp_filter );
		} );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		give( TemplateHooks::class )->walk( give( Commands\SetupNewTemplateHook::class ) );
		if( ! wp_doing_ajax() ) {
			give( TemplateHooks::class )->walk( give( Commands\DeprecateOldTemplateHook::class ) );
		}
	}
}
