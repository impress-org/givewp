<?php

namespace Give\ServiceProviders;

use Give\Helpers\Hooks;
use Give\Onboarding\SettingsRepository;
use Give\Onboarding\Setup\Page as SetupPage;
use Give\Onboarding\Wizard\Page as WizardPage;
use Give\Onboarding\Routes\SettingsRoute;
use Give\Onboarding\Routes\LocationRoute;
use Give\Onboarding\Routes\CurrencyRoute;
use Give\Onboarding\Setup\Handlers\TopLevelMenuRedirect;
use Give\Onboarding\Setup\Handlers\StripeConnectHandler;

class Onboarding implements ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( SetupPage::class );
		give()->bind( DonationsRedirect::class );
		give()->bind( SettingsRoute::class );
		give()->bind( CurrencyRoute::class );
		give()->bind(
			SettingsRepository::class,
			function() {
				$optionName = 'give_settings';
				return new SettingsRepository(
					get_option( $optionName ),
					function( $settings ) use ( $optionName ) {
						return update_option( $optionName, $settings );
					}
				);
			}
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {

		// Load Wizard Page
		$wizardPage = new WizardPage();
		add_action( 'admin_menu', [ $wizardPage, 'add_page' ] );
		add_action( 'admin_init', [ $wizardPage, 'setup_wizard' ] );
		add_action( 'admin_enqueue_scripts', [ $wizardPage, 'enqueue_scripts' ] );

		Hooks::addAction( 'rest_api_init', LocationRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', CurrencyRoute::class, 'registerRoute', 10 ); // Static route, onboarding/settings/currency
		Hooks::addAction( 'rest_api_init', SettingsRoute::class, 'registerRoute', 11 ); // Dynamic route, onboarding/settings/{setting}

		// Maybe load Setup Page
		if ( give_is_setting_enabled( SetupPage::getSetupPageEnabledOrDisabled() ) ) {
			Hooks::addAction( 'admin_init', TopLevelMenuRedirect::class, 'maybeHandle' );
			Hooks::addAction( 'admin_init', StripeConnectHandler::class, 'maybeHandle' );
			Hooks::addAction( 'admin_menu', SetupPage::class, 'add_page' );
			Hooks::addAction( 'admin_enqueue_scripts', SetupPage::class, 'enqueue_scripts' );
			Hooks::addAction( 'admin_post_dismiss_setup_page', SetupPage::class, 'dismissSetupPage' );
		}
	}
}
