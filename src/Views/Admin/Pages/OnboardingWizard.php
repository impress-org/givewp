<?php

namespace Give\Views\Admin\Pages;

defined( 'ABSPATH' ) || exit;

/**
 * undocumented class
 */
class OnboardingWizard {


	protected $slug = 'give-onboarding-wizard';

	public function init() {
		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'admin_init', [ $this, 'setup_wizard' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function add_page() {
		add_dashboard_page( '', '', 'manage_options', 'give-onboarding-wizard', '' );
	}

	public function setup_wizard() {
		if ( empty( $_GET['page'] ) || $this->slug !== $_GET['page'] ) { // WPCS: CSRF ok, input var ok.
			return;
		} else {
			$this->render_page();
		}
	}

	public function render_page() {
		ob_start();
		include_once GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/onboarding-wizard-template.php';
		exit;

	}

	public function enqueue_scripts() {
		wp_enqueue_style(
			'give-admin-onboarding-wizard',
			GIVE_PLUGIN_URL . 'assets/dist/css/admin-onboarding-wizard.css',
			[],
			'0.0.1'
		);
		wp_enqueue_script(
			'give-admin-onboarding-wizard-app',
			GIVE_PLUGIN_URL . 'assets/dist/js/admin-onboarding-wizard.js',
			[ 'wp-element', 'wp-api', 'wp-i18n' ],
			'0.0.1',
			true
		);
		wp_set_script_translations( 'give-admin-onboarding-wizard-app', 'give' );

		wp_localize_script(
			'give-admin-onboarding-wizard-app',
			'giveOnboardingWizardData',
			[
				'setupUrl'   => admin_url( '?page=give-getting-started' ),
				'currencies' => array_keys( give_get_currencies_list() ),
			]
		);

		wp_enqueue_style(
			'give-google-font-montserrat',
			'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap',
			[],
			GIVE_VERSION
		);

	}

}
