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
		add_action( 'admin_init', [ $this, 'render_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function add_page() {
		add_dashboard_page( '', '', 'manage_options', 'give-onboarding-wizard', '' );
	}

	public function render_page() {
		ob_start();
		include_once GIVE_PLUGIN_DIR . 'src/Views/Admin/Pages/templates/onboarding-wizard-template.php';
		exit;
	}

	public function enqueue_scripts() {

	}

}
