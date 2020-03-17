<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use Give\Form\Theme\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
abstract class Theme {
	/**
	 * return theme ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract  public function getID();

	/**
	 * Get theme name.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getName();

	/**
	 * Get theme image.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	abstract public function getImage();

	/**
	 * Get options config
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	abstract public function getOptionsConfig();


	/**
	 * Get theme options
	 *
	 * @return Options
	 */
	public function getOptions() {
		return Options::fromArray( $this->getOptionsConfig() );
	}
}
