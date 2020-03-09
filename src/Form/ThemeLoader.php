<?php

/**
 * Handle Theme Loading Handler
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use function Give\Helpers\Form\Theme\get as getThemeSettings;
use function Give\Helpers\Form\Theme\getActiveID;

defined( 'ABSPATH' ) || exit;

/**
 * ThemeLoader class.
 *
 * @since 2.7.0
 */
class ThemeLoader {
	/**
	 * Saved form theme settings
	 *
	 * @var array
	 */
	private $themeSettings = [];

	/**
	 * Form theme config.
	 *
	 * @var array
	 */
	private $themeConfig = [];

	/**
	 * Activate form theme id.
	 *
	 * @var string
	 */
	private $activeThemeID;

	/**
	 * Form ID.
	 *
	 * @var string
	 */
	private $formID;

	/**
	 * Form Theme loading handler
	 *
	 * @param int $formID
	 */
	public function __construct( $formID = 0 ) {
		global $post;

		$this->formID = $formID ?: $post->ID;

		$this->activeThemeID = getActiveID( $this->formID );
		$this->themeSettings = getThemeSettings( $this->formID );
		$this->themeConfig   = Give()->themes->getTheme( $this->activeThemeID );
	}


	/**
	 * Initialize form theme
	 */
	public function init() {
		// Exit.
		if ( ! ( $entryFilePath = $this->getThemePath() ) ) {
			return;
		}

		$entryFilePath = "{$entryFilePath}functions.php";

		// Exit.
		if ( ! file_exists( $entryFilePath ) ) {
			return;
		}

		require_once $entryFilePath;
	}


	/**
	 * Get form theme path
	 *
	 * @return string
	 */
	private function getThemePath() {
		return array_key_exists( 'entry', $this->themeConfig ) ? $this->themeConfig['entry'] : '';
	}
}
