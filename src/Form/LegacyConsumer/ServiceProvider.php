<?php

namespace Give\Form\LegacyConsumer;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give\Form\LegacyConsumer\Commands\DeprecateOldTemplateHook;

class ServiceProvider implements ServiceProviderInterface {

	/**
	 * @inheritDoc
	 */
	public function register() {
		include_once plugin_dir_path( __FILE__ ) . '/functions.php';
		give()->bind(
			DeprecateOldTemplateHook::class,
			function() {
				global $wp_filter;
				return new DeprecateOldTemplateHook( $wp_filter );
			}
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		give( TemplateHooks::class )->walk( give( Commands\SetupNewTemplateHook::class ) );

		add_filter(
			'give_donation_form_required_fields',
			function( $requiredFields, $formID ) {
				return give( TemplateHooks::class )->reduce( new Commands\SetupFieldValidation( $formID ), $requiredFields );
			},
			10,
			2
		);

		add_action(
			'give_insert_payment',
			function( $donationID, $donationData ) {
				give( TemplateHooks::class )->walk( new Commands\SetupFieldPersistance( $donationID, $donationData ) );
			},
			10,
			2
		);

		give( TemplateHooks::class )->walk( new Commands\CommandFactory( Commands\SetupPaymentDetailsDisplay::class ) );
		give( TemplateHooks::class )->walk( new Commands\CommandFactory( Commands\SetupFieldReciept::class ) );
		give( TemplateHooks::class )->walk( new Commands\CommandFactory( Commands\SetupFieldConfirmation::class ) );
		give( TemplateHooks::class )->walk( new Commands\CommandFactory( Commands\SetupFieldEmailTag::class ) );
		if ( ! wp_doing_ajax() ) {
			give( TemplateHooks::class )->walk( give( Commands\DeprecateOldTemplateHook::class ) );
		}
	}
}
