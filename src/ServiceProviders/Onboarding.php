<?php

namespace Give\ServiceProviders;

use Give\Onboarding\Setup\Page as SetupPage;
use Give\Onboarding\Setup\StripeConnectHandler;
use Give\Onboarding\Wizard\Page as WizardPage;

class Onboarding implements ServiceProvider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		// ...
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
			$setupPage = new SetupPage;
			add_action( 'admin_menu', [ $setupPage, 'add_page' ] );
			add_action( 'admin_init', [ $setupPage, 'redirectDonationsToSetup' ] );
			add_action( 'admin_enqueue_scripts', [ $setupPage, 'enqueue_scripts' ] );
			add_action( 'admin_notices', [ $setupPage, 'hide_admin_notices' ], -999999 );
			add_action( 'admin_post_dismiss_setup_page', [ $setupPage, 'dismissSetupPage' ] );
		}

		// Handle Stripe Connect return.
		// Priority 9 to listener implemented by the advanced settings.
		add_action( 'admin_init', [ StripeConnectHandler::class, 'maybeHandle' ], 9 );
	}
}
