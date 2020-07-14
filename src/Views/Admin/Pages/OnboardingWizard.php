<?php

namespace Give\Views\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * Onboarding Wizard admin page class
 *
 * Responsible for setting up and rendering Onboarding Wizard page at
 * wp-admin/?page=give-onboarding-wizard
 *
 * @since 2.8.0
 */
class OnboardingWizard {


	/** @var string $slug Page slug used for displaying onboarding wizard */
	protected $slug = 'give-onboarding-wizard';

	/**
	 * Adds Onboarding Wizard hooks
	 *
	 * Handles setting up hooks relates to the Onboarding Wizard admin page.
	 *
	 * @since 2.8.0
	 **/
	public function init() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_init', [ $this, 'setup_wizard' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Adds Onboarding Wizard as dashboard page
	 *
	 * Register Onboarding Wizard as an admin page route
	 *
	 * @since 2.8.0
	 **/
	public function add_page() {
		add_dashboard_page( '', '', 'manage_options', $this->slug, '' );
	}

	/**
	 * Conditionally renders Onboarding Wizard
	 *
	 * If the current page query matches the onboarding wizard's slug, method renders the onboarding wizard.
	 *
	 * @since 2.8.0
	 **/
	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
			return;
		} else {
			$this->render_page();
		}
	}

	/**
	 * Renders onboarding wizard markup
	 *
	 * Uses an object buffer to display the onboarding wizard template
	 *
	 * @since 2.8.0
	 **/
	public function render_page() {
		ob_start();
		include_once GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/onboarding-wizard-template.php';
		exit;

	}

	/**
	 * Enqueues onboarding wizard scripts/styles
	 *
	 * Enqueues scripts/styles necessary for loading the Onboarding Wizard React app,
	 * and localizes some additional data for the app to access.
	 *
	 * @since 2.8.0
	 **/
	public function enqueue_scripts() {
		wp_enqueue_style(
			'give-admin-onboarding-wizard',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-onboarding-wizard.css',
			[],
			GIVE_VERSION
		);
		wp_enqueue_script(
			'give-admin-onboarding-wizard-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-onboarding-wizard.js',
			[ 'wp-element', 'wp-api', 'wp-i18n' ],
			GIVE_VERSION,
			true
		);
		wp_set_script_translations( 'give-admin-onboarding-wizard-app', 'give' );

		wp_localize_script(
			'give-admin-onboarding-wizard-app',
			'giveOnboardingWizardData',
			[
				'setupUrl'   => admin_url( 'edit.php?post_type=give_forms&page=give-setup' ),
				'currencies' => array_keys( give_get_currencies_list() ),
			]
		);

		wp_enqueue_style(
			'give-google-font-montserrat',
			'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap',
			[],
			null
		);

	}

}
