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
		$coreFormThemes = [
			GIVE_PLUGIN_DIR . 'src/Views/Form-Themes/Sequoia',
			GIVE_PLUGIN_DIR . 'src/Views/Form-Themes/Legacy',
		];

		/**
		 * Filter list of form theme
		 *
		 * @since 2.7.0
		 */
		$thirdPartyThemes = apply_filters( 'give_register_form_theme', [] );

		$allThemes = $coreFormThemes;

		if ( $thirdPartyThemes ) {
			$allThemes = array_unique( array_merge( $allThemes, array_filter( $thirdPartyThemes ) ) );
		}

		foreach ( $allThemes as $themePath ) {
			$themePath  = trailingslashit( $themePath );
			$configFile = $themePath . 'config.php';

			if ( file_exists( $configFile ) ) {
				$config          = require_once $configFile;
				$config['entry'] = $themePath;

				$this->set( new Theme( $config ) );
			}
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
		return isset( $this->themes[ $themeID ] ) ? $this->themes[ $themeID ] : [];
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
