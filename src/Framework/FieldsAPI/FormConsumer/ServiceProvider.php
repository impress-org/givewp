<?php

namespace Give\Framework\FieldsAPI\FormConsumer;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		include_once plugin_dir_path( __FILE__ ) . '/functions.php';
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_action( 'init', new FormFieldMediator() );
	}
}
