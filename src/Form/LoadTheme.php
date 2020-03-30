<?php

/**
 * Handle Theme Loading Handler
 *
 * @package Give
 * @since   2.7.0
 */

namespace Give\Form;

use _WP_Dependency;
use Give\Form\Theme\Hookable;
use Give\Form\Theme\Scriptable;
use function Give\Helpers\Form\Theme\getActiveID;
use function Give\Helpers\Form\Theme\Utils\Frontend\getFormId;
use function Give\Helpers\Form\Utils\isViewingForm;

defined( 'ABSPATH' ) || exit;

/**
 * LoadTheme class.
 * This class is responsible to load necessary hooks and run required functions which help to render form theme (in different style).
 *
 * @since 2.7.0
 */
class LoadTheme {
	/**
	 * Default form theme ID.
	 *
	 * @var string
	 */
	private $defaultTemplateID = 'legacy';

	/**
	 * Form theme config.
	 *
	 * @var Theme
	 */
	private $theme;

	/**
	 * setup form template
	 *
	 * @since 2.7.0
	 * @param int $formId Form Id. Default value: check explanation in src/Helpers/Form/Utils.php:103
	 */
	private function setUpTemplate( $formId = null ) {
		$formID = (int) ( $formId ?: getFormId() );

		$themeID = getActiveID( $formID ) ?: $this->defaultTemplateID;

		$this->theme = Give()->themes->getTheme( $themeID );
	}

	/**
	 * Initialize form template
	 */
	public function init() {
		$this->setUpTemplate();

		// Exit is theme is not valid.
		if ( ! ( $this->theme instanceof Theme ) ) {
			return;
		}

		// Load theme hooks.
		if ( $this->theme instanceof Hookable ) {
			$this->theme->loadHooks();
		}

		// Load theme scripts.
		if ( $this->theme instanceof Scriptable ) {
			add_action( 'wp_enqueue_scripts', [ $this->theme, 'loadScripts' ] );
		}

		$this->setUpFrontendHooks();
	}


	/**
	 * Setup frontend hooks
	 *
	 * @since 2.7.0
	 */
	private function setUpFrontendHooks() {
		add_action( 'give_embed_head', 'wp_enqueue_scripts', 1 );
		add_action( 'give_embed_head', [ $this, 'handleEnqueueScripts' ], 2 );
		add_action( 'give_embed_head', 'wp_print_styles', 8 );
		add_action( 'give_embed_head', 'wp_print_head_scripts', 9 );
		add_action( 'give_embed_footer', 'wp_print_footer_scripts', 20 );
		add_filter( 'give_form_wrap_classes', [ $this, 'editClassList' ], 999 );
		add_action( 'give_hidden_fields_after', [ $this, 'addHiddenField' ] );
	}


	/**
	 * Handle enqueue script
	 *
	 * @since 2.7.0
	 */
	public function handleEnqueueScripts() {
		global $wp_scripts, $wp_styles;
		wp_enqueue_scripts();

		$wp_styles->dequeue( $this->getListOfScriptsToDequeue( $wp_styles->registered ) );
		$wp_scripts->dequeue( $this->getListOfScriptsToDequeue( $wp_scripts->registered ) );
	}

	/**
	 * Edit donation form wrapper class list.
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 2.7.0
	 */
	public function editClassList( $classes ) {
		// Remove display_style related classes because they (except onpage ) creates style conflict with form template.
		$classes = array_filter(
			$classes,
			static function ( $class ) {
				return false === strpos( $class, 'give-display-' );
			}
		);

		$classes[] = 'give-embed-form';

		if ( ! empty( $_GET['iframe'] ) ) {
			$classes[] = 'give-viewing-form-in-iframe';
		}

		return $classes;
	}

	/**
	 * Add hidden field
	 *
	 * @since 2.7.0
	 */
	public function addHiddenField() {
		printf(
			'<input type="hidden" name="%1$s" value="%2$s">',
			'give_embed_form',
			'1'
		);
	}

	/**
	 * Get filter list to dequeue scripts and style
	 *
	 * @param array $scripts
	 *
	 * @return array
	 * @since 2.7.0
	 */
	private function getListOfScriptsToDequeue( $scripts ) {
		$list = [];
		$skip = [];

		/* @var _WP_Dependency $data */
		foreach ( $scripts as $handle => $data ) {
			// Do not unset dependency.
			if ( in_array( $handle, $skip, true ) ) {
				continue;
			}

			if (
				0 === strpos( $handle, 'give' ) ||
				false !== strpos( $data->src, '\give' )
			) {
				// Store dependencies to skip.
				$skip = array_merge( $skip, $data->deps );
				continue;
			}

			$list[] = $handle;
		}

		return $list;
	}

	/**
	 * Get theme.
	 *
	 * @since 2.7.0
	 * @return Theme
	 */
	public function getTheme() {
		return $this->theme;
	}
}
