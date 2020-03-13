<?php
namespace Give\Form\Theme;

/**
 * Interface Hookable
 *
 * @since 2.7.0
 * @package Give\Form\Theme
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
