<?php
namespace Give\Views\Form\Themes;

use Give\Form\Theme;
use Give\Form\Themes\Sequoia\Actions;
use Give\Form\Themes\Sequoia\Scripts;


/**
 * Class Sequoia
 *
 * @package Give\Form\Theme
 */
class Sequoia extends Theme implements Theme\Themeable {

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
		add_action( 'wp_enqueue_scripts', array( new Scripts(), 'init' ) );
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
	public function geName() {
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
