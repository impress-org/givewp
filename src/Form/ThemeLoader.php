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
	 * Default form theme ID.
	 *
	 * @var string
	 */
	private $defaultThemeID = 'legacy';

	/**
	 * Saved form theme settings
	 *
	 * @var array
	 */
	private $themeSettings;

	/**
	 * Form theme config.
	 *
	 * @var Theme
	 */
	private $theme;

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
		$this->activeThemeID = $this->activeThemeID ?: $this->defaultThemeID;

		$this->themeSettings = getThemeSettings( $this->formID );
		$this->theme         = Give()->themes->getTheme( $this->activeThemeID );
	}


	/**
	 * Initialize form theme
	 */
	public function init() {
		// Exit.
		if ( ! ( $entryFilePath = $this->theme->getThemePath() ) ) {
			return;
		}

		$entryFilePath = "{$entryFilePath}functions.php";

		// Exit.
		if ( ! file_exists( $entryFilePath ) ) {
			return;
		}

		require_once $entryFilePath;
	}
}
