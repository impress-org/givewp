<?php

namespace Give\ServiceProviders;

use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\Onboarding\Migrations\SetFormDonationLevelsToStrings;
use Give\Onboarding\SettingsRepository;
use Give\Onboarding\FormRepository;
use Give\Onboarding\DefaultFormFactory;
use Give\Onboarding\LocaleCollection;
use Give\Onboarding\SettingsRepositoryFactory;
use Give\Onboarding\Setup\Page as SetupPage;
use Give\Onboarding\Setup\PageView as SetupPageView;
use Give\Onboarding\Wizard\Page as WizardPage;
use Give\Onboarding\Wizard\FormPreview;
use Give\Onboarding\Routes\SettingsRoute;
use Give\Onboarding\Routes\LocationRoute;
use Give\Onboarding\Routes\CurrencyRoute;
use Give\Onboarding\Routes\AddonsRoute;
use Give\Onboarding\Routes\FeaturesRoute;
use Give\Onboarding\Routes\FormRoute;
use Give\Onboarding\Setup\Handlers\AdminNoticeHandler;
use Give\Onboarding\Setup\Handlers\TopLevelMenuRedirect;

class Onboarding implements ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {

		// Onboarding Wizard and Setup page require WP v5.0.x or greater
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<=' ) ) {
			return;
		}

		give()->singleton( SetupPage::class );
		give()->singleton( WizardPage::class );
		give()->singleton( FormPreview::class );
		give()->bind( DonationsRedirect::class );
		give()->bind( SettingsRoute::class );
		give()->bind( CurrencyRoute::class );
		give()->bind( AddonsRoute::class );
		give()->bind( FeaturesRoute::class );
		give()->bind( FormRoute::class );
		give()->bind( FormRepository::class );
		give()->bind( DefaultFormFactory::class );
		give()->bind( SettingsRepositoryFactory::class );
		give()->bind( LocaleCollection::class );
		give()->singleton( SetupPageView::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		$this->registerMigrations();

		// Onboarding Wizard and Setup page require WP v5.0.x or greater
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<=' ) ) {
			return;
		}

		// Load Wizard Page
		Hooks::addAction( 'admin_menu', WizardPage::class, 'add_page' );
		Hooks::addAction( 'admin_init', WizardPage::class, 'redirect' );
		Hooks::addAction( 'admin_init', WizardPage::class, 'setup_wizard' );
		Hooks::addAction( 'admin_enqueue_scripts', WizardPage::class, 'enqueue_scripts' );

		// Load Form Preview
		Hooks::addAction( 'admin_menu', FormPreview::class, 'add_page' );
		Hooks::addAction( 'admin_init', FormPreview::class, 'setup_form_preview' );

		Hooks::addAction( 'rest_api_init', FormRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', LocationRoute::class, 'registerRoute' );
		Hooks::addAction( 'rest_api_init', AddonsRoute::class, 'registerRoute', 10 ); // Static route, onboarding/settings/addons
		Hooks::addAction( 'rest_api_init', CurrencyRoute::class, 'registerRoute', 10 ); // Static route, onboarding/settings/currency
		Hooks::addAction( 'rest_api_init', FeaturesRoute::class, 'registerRoute', 10 ); // Static route, onboarding/settings/features
		Hooks::addAction( 'rest_api_init', SettingsRoute::class, 'registerRoute', 11 ); // Dynamic route, onboarding/settings/{setting}

		// Maybe load Setup Page
		if ( give_is_setting_enabled( SetupPage::getSetupPageEnabledOrDisabled() ) ) {
			Hooks::addAction( 'admin_init', AdminNoticeHandler::class, 'maybeHandle' );
			Hooks::addAction( 'admin_init', TopLevelMenuRedirect::class, 'maybeHandle' );
			Hooks::addAction( 'admin_menu', SetupPage::class, 'add_page' );
			Hooks::addAction( 'admin_enqueue_scripts', SetupPage::class, 'enqueue_scripts' );
			Hooks::addAction( 'admin_post_dismiss_setup_page', SetupPage::class, 'dismissSetupPage' );
		}
	}

	/**
	 * Registers migrations
	 *
	 * @since 2.13.3
	 */
	private function registerMigrations()
	{
		give(MigrationsRegister::class)->addMigrations([
			SetFormDonationLevelsToStrings::class
		]);
	}
}
