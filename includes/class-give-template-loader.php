<?php

/**
 * Template Loader
 *
 * @version        1.0
 * @subpackage     Classes/Template-Loader
 * @copyright      Copyright (c) 2015, WordImpress
 * @license        http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since          1.0.0
 *
 */
class Give_Template_Loader {

	/**
	 * Hook in methods
	 */
	public static function init() {

		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );


		/**
		 * Load Template Functions
		 *
		 * @see: template-functions.php
		 */

		/**
		 * Content Wrappers
		 *
		 * @see 3give_output_content_wrapper()
		 * @see give_output_content_wrapper_end()
		 */
		add_action( 'give_before_main_content', 'give_output_content_wrapper', 10 );
		add_action( 'give_after_main_content', 'give_output_content_wrapper_end', 10 );

		/**
		 * Before Single Forms Summary Div
		 *
		 * @see give_show_product_images()
		 * @see give_show_avatars()
		 */
		add_action( 'give_before_single_form_summary', 'give_left_sidebar_pre_wrap', 5 );
		add_action( 'give_before_single_form_summary', 'give_show_form_images', 10 );
		//add_action( 'give_before_single_form_summary', 'give_show_avatars', 15 );
		add_action( 'give_before_single_form_summary', 'give_get_forms_sidebar', 20 );
		add_action( 'give_before_single_form_summary', 'give_left_sidebar_post_wrap', 30 );


		/**
		 * Single Forms Summary Box
		 *
		 * @see give_template_single_title()
		 */
		add_action( 'give_single_form_summary', 'give_template_single_title', 5 );
		add_action( 'give_single_form_summary', 'give_get_donation_form', 10 );


	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. Give looks for theme
	 * overrides in /theme/give/ by default
	 *
	 * For beginners, it also looks for a give.php template first. If the user adds
	 * this to the theme (containing give() inside) this will be used for all
	 * give templates.
	 *
	 * @param mixed $template
	 *
	 * @return string
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

Give_Template_Loader::init();
