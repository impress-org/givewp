<?php

/**
 * Handle Theme Loading Handler
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

use _WP_Dependency;
use WP_Scripts;
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
	 * @param int    $formID
	 * @param string $formTheme Theme ID. Add form_theme shortcode argument to load selective form theme.
	 */
	public function __construct( $formID = 0, $formTheme = '' ) {
		global $post;

		$this->formID = $formID ?: $post->ID;

		$this->activeThemeID = getActiveID( $this->formID );
		$this->activeThemeID = $formTheme ?: ( $this->activeThemeID ?: $this->defaultThemeID );

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

		// Script loading handler.
		add_action( 'give_embed_head', array( $this, 'enqueue_scripts' ), 1 );
		add_action( 'give_embed_head', 'wp_print_head_scripts', 9 );
		add_action( 'give_embed_footer', 'wp_print_footer_scripts', 20 );
	}


	/**
	 * Handle enqueue script
	 *
	 * @since 2.7.0
	 */
	public function enqueue_scripts() {
		global $wp_scripts, $wp_styles;
		wp_enqueue_scripts();

		$wp_styles->dequeue( $this->getListOFScriptsToDequeue( $wp_styles->registered ) );
		$wp_scripts->dequeue( $this->getListOFScriptsToDequeue( $wp_scripts->registered ) );
	}


	/**
	 * Get filter list to dequeue scripts and style
	 *
	 * @since 2.7.0
	 *
	 * @param array $scripts
	 *
	 * @return array
	 */
	private function getListOFScriptsToDequeue( $scripts ) {
		$list = [];

		/* @var _WP_Dependency $data */
		foreach ( $scripts as $handle => $data ) {
			if ( 0 === strpos( $handle, 'give-' ) || false !== strpos( $data->src, '\give-' ) ) {
				continue;
			}

			$list[] = $handle;
		}

		return $list;
	}
}
