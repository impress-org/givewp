<?php

/**
 * Handle Form Themes
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Themes class
 *
 * @since 2.7.0
 */
class Themes {
	/**
	 * Themes
	 *
	 * @var array
	 */
	private $themes = array();


	/**
	 * Get Registered themes
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	public function get() {
		return $this->themes;
	}

	/**
	 * Get Registered theme
	 *
	 * @since 2.7.0
	 *
	 * @param string $themeID
	 *
	 * @return Theme
	 */
	public function getTheme( $themeID ) {
		return $this->themes[ $themeID ];
	}

	/**
	 * Themes constructor.
	 *
	 * @param Theme $registerTheme
	 */
	public function set( $registerTheme ) {
		$this->themes[ $registerTheme->getID() ] = $registerTheme;
	}
}
