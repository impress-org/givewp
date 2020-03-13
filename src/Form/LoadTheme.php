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
use WP_Post;
use function Give\Helpers\Form\Theme\getActiveID;
use function Give\Helpers\Form\Utils\isViewingForm;

defined( 'ABSPATH' ) || exit;

/**
 * ThemeLoader class.
 *
 * @since 2.7.0
 */
class LoadTheme {
	/**
	 * Default form theme ID.
	 *
	 * @var string
	 */
	private $defaultThemeID = 'legacy';

	/**
	 * Form theme config.
	 *
	 * @var Theme
	 */
	private $theme;

	/**
	 * Form Theme loading handler
	 *
	 * @param string $formTheme Theme ID. Add form_theme shortcode argument to load selective form theme.
	 *
	 * @global WP_Post $post
	 */
	public function __construct( $formTheme = '' ) {
		$formID = (int) $this->getFormId();

		$themeID = getActiveID( $formID );
		$themeID = $formTheme ?: ( $themeID ?: $this->defaultThemeID );

		$this->theme = Give()->themes->getTheme( $themeID );
	}


	/**
	 * Initialize form theme
	 */
	public function init() {
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
			$this->theme->loadScripts();
		}

		// Script loading handler.
		add_action( 'give_embed_head', 'wp_enqueue_scripts', 1 );
		add_action( 'give_embed_head', array( $this, 'enqueue_scripts' ), 2 );
		add_action( 'give_embed_head', 'wp_print_styles', 8 );
		add_action( 'give_embed_head', 'wp_print_head_scripts', 9 );
		add_action( 'give_embed_footer', 'wp_print_footer_scripts', 20 );

		// Update form DOM.
		add_filter( 'give_form_wrap_classes', array( $this, 'addClasses' ) );
		add_action( 'give_hidden_fields_after', array( $this, 'addHiddenField' ) );
	}


	/**
	 * Handle enqueue script
	 *
	 * @since 2.7.0
	 */
	public function enqueue_scripts() {
		global $wp_scripts, $wp_styles;
		wp_enqueue_scripts();

		$wp_styles->dequeue( $this->getListOfScriptsToDequeue( $wp_styles->registered ) );
		$wp_scripts->dequeue( $this->getListOfScriptsToDequeue( $wp_scripts->registered ) );
	}

	/**
	 * Add custom classes
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 2.7.0
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
	 * @param array $classes
	 *
	 * @since 2.7.0
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
	 * Get form ID.
	 *
	 * @global WP_Post $post
	 * @return int|null
	 * @since 2.7.0
	 */
	private function getFormId() {
		global $post;
		$donorSession = give_get_purchase_session();

		$formId = ! empty( $donorSession['post_data']['give-form-id'] ) ? absint( $donorSession['post_data']['give-form-id'] ) : null;

		if ( $formId ) {
			return $formId;
		}

		if ( 'give_forms' === get_post_type( $post ) ) {
			return $post->ID;
		}

		return null;
	}
}
