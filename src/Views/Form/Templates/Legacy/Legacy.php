<?php
namespace Give\Views\Form\Templates\Legacy;

use Give\Form\Template;

class Legacy extends Template {
	/**
	 * @inheritDoc
	 * @since 2.7.0
	 * @var array
	 */
	protected $mapToLegacySetting = [
		'display_settings' => [
			'form_content' => '_give_form_content',
		],
	];

	/**
	 * @inheritDoc
	 */
	public function getID() {
		return 'legacy';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'Legacy - Standard Form', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return 'https://images.unsplash.com/photo-1510070112810-d4e9a46d9e91?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=300&q=80';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptionsConfig() {
		return require 'optionConfig.php';
	}
}
