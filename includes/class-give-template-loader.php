<?php
/**
 * Template Loader
 *
 * @package     Give
 * @subpackage  Classes/Give_Template_Loader
 * @copyright   Copyright (c) 2016, Give
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Template_Loader Class
 *
 * Base class template loader.
 *
 * @since 1.0
 */
class Give_Template_Loader {

	/**
	 * Class Constructor
	 *
	 * Set up the template loader Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {

		/**
		 * Templates
		 */
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

		/**
		 * Content Wrappers
		 */
		add_action( 'give_before_main_content', 'give_output_content_wrapper', 10 );
		add_action( 'give_after_main_content', 'give_output_content_wrapper_end', 10 );

		/**
		 * Entry Summary Classes
		 */
		add_filter( 'give_forms_single_summary_classes', array( $this, 'give_set_single_summary_classes' ) );

		/**
		 * Sidebar
		 */
		add_action( 'give_before_single_form_summary', array( $this, 'give_output_sidebar_option' ), 1 );

		/**
		 * Single Forms Summary Box
		 */
		add_action( 'give_single_form_summary', 'give_template_single_title', 5 );
		add_action( 'give_single_form_summary', 'give_get_donation_form', 10 );

	}

	/**
	 * Give Set Single Summary Classes
	 *
	 * Determines if the single form should be full width or with a sidebar.
	 *
	 * @access public
	 *
	 * @param  string $classes List of space separated class names.
	 *
	 * @return string $classes List of space separated class names.
	 */
	public function give_set_single_summary_classes( $classes ) {

		// Add full width class when feature image is disabled AND no widgets are present
		if ( ! give_is_setting_enabled( give_get_option( 'form_sidebar' ) ) ) {
			$classes .= ' give-full-width';
		}

		return $classes;

	}

	/**
	 * Output sidebar option
	 *
	 * Determines whether the user has enabled or disabled the sidebar for Single Give forms.
	 *
	 * @since  1.3
	 * @access public
	 *
	 * @return void
	 */
	public function give_output_sidebar_option() {

		// Add full width class when feature image is disabled AND no widgets are present
		if ( give_is_setting_enabled( give_get_option( 'form_sidebar' ) ) ) {
			add_action( 'give_before_single_form_summary', 'give_left_sidebar_pre_wrap', 5 );
			add_action( 'give_before_single_form_summary', 'give_show_form_images', 10 );
			add_action( 'give_before_single_form_summary', 'give_get_forms_sidebar', 20 );
			add_action( 'give_before_single_form_summary', 'give_left_sidebar_post_wrap', 30 );
		}

	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. Give looks for theme
	 * overrides in /theme/give/ by default.
	 *
	 * For beginners, it also looks for a give.php template first. If the user adds this
	 * to the theme (containing give() inside) this will be used for all give templates.
	 *
	 * @access public
	 *
	 * @param  mixed $template
	 *
	 * @return string $template
	 */
	public static function template_loader( $template ) {
		$find = array( 'give.php' );
		$file = '';

		if ( is_single() && get_post_type() == 'give_forms' ) {
			$file   = 'single-give-form.php';
			$find[] = $file;
			$find[] = apply_filters( 'give_template_path', 'give/' ) . $file;
		}

		if ( $file ) {
			$template = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = GIVE_PLUGIN_DIR . '/templates/' . $file;
			}
		}

		return $template;
	}

}
