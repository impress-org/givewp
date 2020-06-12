<?php
namespace Give\Form\Template;

/**
 * Interface Hookable
 *
 * @since 2.7.0
 * @package Give\Form\Template
 */
interface Hookable {

	/**
	 * Load WordPress hooks
	 *
	 * @since 2.7.0
	 * @return mixed
	 */
	public function loadHooks();
}
