<?php

namespace Give\ServiceProviders;

use Give\Helpers\Hooks;
use Give\Onboarding\Setup\Page as SetupPage;
use Give\Onboarding\Setup\StripeConnectHandler;
use Give\Onboarding\Wizard\Page as WizardPage;

class Onboarding implements ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( SetupPage::class );
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

		// Maybe load Setup Page
		if ( give_is_setting_enabled( SetupPage::getSetupPageEnabledOrDisabled() ) ) {
			Hooks::addAction( 'admin_menu', SetupPage::class, 'add_page' );
			Hooks::addAction( 'admin_init', SetupPage::class, 'redirectDonationsToSetup' );
			Hooks::addAction( 'admin_enqueue_scripts', SetupPage::class, 'enqueue_scripts' );
			Hooks::addAction( 'admin_post_dismiss_setup_page', SetupPage::class, 'dismissSetupPage' );
		}

		// Handle Stripe Connect return.
		// Priority 9 to listener implemented by the advanced settings.
		Hooks::addAction( 'admin_init', StripeConnectHandler::class, 'maybeHandle', 9 );
	}
}
