<?php

/**
 * Handle Form Themes
 *
 * @package Give
 * @since   2.7.0
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
	 * Load themes
	 *
	 * @since 2.7.0
	 */
	public function loadThemes() {
		$coreFormThemes = require GIVE_PLUGIN_DIR . 'src/Form/Config/Themes/Load.php';

		/**
		 * Filter list of form theme
		 *
		 * @since 2.7.0
		 */
		$themes = apply_filters( 'give_form_themes', $coreFormThemes );

		foreach ( $themes as $theme ) {
			$this->set( new Theme( $theme ) );
		}
	}


	/**
	 * Get Registered themes
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public function get() {
		return $this->themes;
	}

	/**
	 * Get Registered theme
	 *
	 * @param string $themeID
	 *
	 * @return Theme
	 * @since 2.7.0
	 */
	public function getTheme( $themeID ) {
		return $this->themes[ $themeID ];
	}

	/**
	 * Themes constructor.
	 *
	 * @param Theme $registerTheme
	 */
	public function set( Theme $registerTheme ) {
		$this->themes[ $registerTheme->getID() ] = $registerTheme;
	}
}
