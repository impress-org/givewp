<?php

namespace Give\Views\Form\Templates\Classic;

use Give\Form\Template;

/**
 * @unreleased
 */
class Classic extends Template {

	/**
	 * @inheritDoc
	 */
	public function getID() {
		return 'classic';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'Classic Donation Form', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return GIVE_PLUGIN_URL . 'assets/dist/images/admin/ClassicForm.jpg';
	}

	/**
	 * @inheritDoc
	 */
	public function getOptionsConfig() {
		return require 'optionConfig.php';
	}
}
