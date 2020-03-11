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
use function Give\Helpers\Form\Utils\isViewingForm;

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

		add_filter( 'give_form_wrap_classes', array( $this, 'addClasses' ) );
		add_action( 'give_hidden_fields_after', array( $this, 'addHiddenField' ) );
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


	/**
	 * Add custom classes
	 *
	 * @since 2.7.0
	 * @param array $classes
	 *
	 * @return array
	 */
	public function addClasses( $classes ) {
		if ( isViewingForm() ) {
			$classes[] = 'give-embed-form';

			if ( ! empty( $_GET['iframe'] ) ) {
				$classes[] = 'give-viewing-form-in-iframe';
			}
		}

		return $classes;
	}

	/**
	 * Add hidden field
	 *
	 * @since 2.7.0
	 * @param array $classes
	 */
	public function addHiddenField( $classes ) {
		if ( ! isViewingForm() ) {
			return;
		}

		printf(
			'<input type="hidden" name="%1$s" value="%2$s">',
			'give_embed_form',
			'1'
		);
	}
}
