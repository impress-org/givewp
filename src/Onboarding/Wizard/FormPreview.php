<?php

namespace Give\Onboarding\Wizard;

defined( 'ABSPATH' ) || exit;

use Give\Onboarding\Helpers\FormatList;
use Give_Scripts;

/**
 * Onboarding Wizard admin page class
 *
 * Responsible for setting up and rendering Onboarding Wizard page at
 * wp-admin/?page=give-onboarding-wizard
 *
 * @since 2.8.0
 */
class FormPreview {


	/** @var string $slug Page slug used for displaying onboarding wizard */
	protected $slug = 'give-form-preview';

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
	public function setup_form_preview() {
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

		$this->register_scripts();
		ob_start();
		include_once plugin_dir_path( __FILE__ ) . 'templates/form-preview.php';
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
	protected function register_scripts() {

		wp_register_style(
			'give-styles',
			( new Give_Scripts )->get_frontend_stylesheet_uri(),
			[],
			GIVE_VERSION,
			'all'
		);

		wp_register_script(
			'give',
			GIVE_PLUGIN_URL . 'assets/dist/js/give.js',
			[ 'jquery' ],
			GIVE_VERSION,
		);

	}

}
