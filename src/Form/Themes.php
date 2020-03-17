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
 * @since   2.7.0
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
		 * @param Theme[]
		 *
		 * @since 2.7.0
		 */
		$this->themes = apply_filters(
			'give_register_form_theme',
			[
				'sequoia' => Sequoia::class,
				'legacy'  => Legacy::class,
			]
		);
	}

	/**
	 * Get Registered themes
	 *
	 * @return Theme[]
	 * @since 2.7.0
	 */
	public function getThemes() {
		// Check if all themes have there object or not.
		$remainingObjs = array_diff( array_keys( $this->themes ), array_keys( $this->themeObjs ) );

		// Get object if any remaining
		if ( $remainingObjs ) {
			foreach ( $remainingObjs as $themeId ) {
				$this->themeObjs[ $themeId ] = $this->getThemeObject( $themeId );
			}
		}

		return $this->themeObjs;
	}

	/**
	 * Get Registered theme
	 *
	 * @param string $themeId
	 *
	 * @return Theme
	 * @since 2.7.0
	 */
	public function getTheme( $themeId ) {
		if ( isset( $this->themeObjs[ $themeId ] ) ) {
			return $this->themeObjs[ $themeId ];
		}

		$this->themeObjs[ $themeId ] = $this->getThemeObject( $themeId );

		return $this->getThemeObject( $themeId );
	}

	/**
	 * Get class object.
	 *
	 * @param string $themeId
	 *
	 * @return Theme
	 * @since 2.7.0
	 */
	private function getThemeObject( $themeId ) {
		return new $this->themes[ $themeId ]();
	}
}
