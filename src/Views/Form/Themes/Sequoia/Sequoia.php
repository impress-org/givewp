<?php
namespace Give\Views\Form\Themes\Sequoia;

use Give\Form\Theme;
use Give\Form\Theme\Hookable;
use Give\Form\Theme\Scriptable;

/**
 * Class Sequoia
 *
 * @package Give\Form\Theme
 */
class Sequoia extends Theme implements Hookable, Scriptable {

	/**
	 * @inheritDoc
	 */
	public function loadHooks() {
		$actions = new Actions();
		$actions->init();
	}

	/**
	 * @inheritDoc
	 */
	public function loadScripts() {
		wp_enqueue_style( 'give-google-font-montserrat', 'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap', array(), GIVE_VERSION );
		wp_enqueue_style( 'give-sequoia-theme-css', GIVE_PLUGIN_URL . 'assets/dist/css/give-sequoia-theme.css', array( 'give-styles' ), GIVE_VERSION );
		wp_enqueue_script( 'give-sequoia-theme-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-sequoia-theme.js', array( 'give' ), GIVE_VERSION, true );

		// Localize Theme options
		global $post;
		$templateOptions = give_get_meta( $post->ID, '_give_sequoia_form_theme_settings', true, null );

		// Set defaults
		$templateOptions['introduction']['donate_label']          = ! empty( $templateOptions['introduction']['donate_label'] ) ? $templateOptions['introduction']['donate_label'] : __( 'Donate Now', 'give' );
		$templateOptions['payment_amount']['next_label']          = ! empty( $templateOptions['payment_amount']['payment_amount'] ) ? $templateOptions['payment_amount']['next_label'] : __( 'Continue', 'give' );
		$templateOptions['payment_information']['checkout_label'] = ! empty( $templateOptions['payment_information']['checkout_label'] ) ? $templateOptions['payment_information']['checkout_label'] : __( 'Process Donation', 'give' );

		wp_localize_script( 'give-sequoia-theme-js', 'sequoiaTemplateOptions', $templateOptions );
	}

	/**
	 * @inheritDoc
	 */
	public function getID() {
		return 'sequoia';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'Sequoia - Multi-Step Form', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return 'https://images.unsplash.com/photo-1448387473223-5c37445527e7?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=300&q=100';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptionsConfig() {
		return require 'optionConfig.php';
	}
}
