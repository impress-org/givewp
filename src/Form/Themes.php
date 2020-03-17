<?php

/**
 * Handle Form Themes
 *
 * @package Give
 * @since   2.7.0
 */

namespace Give\Form;

use Give\Views\Form\Themes\Legacy\Legacy;
use Give\Views\Form\Themes\Sequoia\Sequoia;

defined( 'ABSPATH' ) || exit;

/**
 * Class RegisterThemes
 *
 * @package Give\Form
 *
 * @since 2.7.0
 */
class Themes {
	/**
	 * Themes
	 *
	 * @var array
	 */
	private $themes = [];


	/**
	 * Themes Objects
	 *
	 * @var Theme[]
	 */
	private $themeObjs = [];

	/**
	 * Load themes
	 *
	 * @since 2.7.0
	 */
	public function load() {
		/**
		 * Filter list of form theme
		 *
		 * @since 2.7.0
		 *
		 * @param Theme[]
		 */
		$this->themes = apply_filters(
			'give_register_form_theme',
			[
				Sequoia::class,
				Legacy::class,
			]
		);

		$this->themeObjs = array_map( array( $this, 'getThemeObject' ), $this->themes );
	}

	/**
	 * Get Registered themes
	 *
	 * @return Theme[]
	 * @since 2.7.0
	 */
	public function getThemes() {
		return $this->themeObjs;
	}

	/**
	 * Get Registered theme
	 *
	 * @param string $themeId
	 *
	 * @return Theme|null
	 * @since 2.7.0
	 */
	public function getTheme( $themeId ) {
		foreach ( $this->themeObjs as $theme ) {
			if ( $themeId === $theme->getID() ) {
				return $theme;
			}
		}

		return null;

	}

	/**
	 * Get class object.
	 *
	 * @since 2.7.0
	 * @param string $className
	 *
	 * @return Theme
	 */
	private function getThemeObject( $className ) {
		$obj = new $className();

		return $this->isValidTheme( $obj ) ? $obj : null;
	}

	/**
	 * Check if theme is valid or not
	 *
	 * @since 2.7.0
	 * @param $theme
	 *
	 * @return bool
	 */
	private function isValidTheme( $theme ) {
		return $theme instanceof Theme;
	}
}
