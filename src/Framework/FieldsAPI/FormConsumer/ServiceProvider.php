<?php

namespace Give\Framework\FieldsAPI\FormConsumer;

use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Helpers\Hooks;
use Give\Framework\FieldsAPI\FieldCollection;

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

		add_action( 'init', function() {
			$fieldCollection = new FieldCollection( 'root' );
			do_action( 'give_fields_payment_mode_before_gateways', $fieldCollection );
			add_action( 'give_payment_mode_before_gateways', function( $formID ) use ( $fieldCollection ) {
				foreach( $fieldCollection->getFields() as $field ) {
					FieldView::render( $field );
				}
			});
		});
	}
}
