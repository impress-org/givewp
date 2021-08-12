<?php
/**
 * Template Functions
 *
 * @package     Give
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the path to the Give templates directory
 *
 * @since 1.0
 * @return string
 */
function give_get_templates_dir() {
	return GIVE_PLUGIN_DIR . 'templates';
}

/**
 * Returns the URL to the Give templates directory
 *
 * @since 1.0
 * @return string
 */
function give_get_templates_url() {
	return GIVE_PLUGIN_URL . 'templates';
}

/**
 * Get other templates, passing attributes and including the file.
 *
 * @since 1.6
 *
 * @param string $template_name Template file name.
 * @param array  $args          Passed arguments. Default is empty array().
 * @param string $template_path Template file path. Default is empty.
 * @param string $default_path  Default path. Default is empty.
 */
function give_get_template( $template_name, $args = [], $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$template_names = "{$template_name}.php";

	$located = give_get_locate_template( $template_names, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		/* translators: %s: the template */
		Give_Notices::print_frontend_notice( sprintf( __( 'The %s template was not found.', 'give' ), $located ), true );

		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'give_get_template', $located, $template_name, $args, $template_path, $default_path );

	/**
	 * Fires in give template, before the file is included.
	 *
	 * Allows you to execute code before the file is included.
	 *
	 * @since 1.6
	 *
	 * @param string $template_name Template file name.
	 * @param string $template_path Template file path.
	 * @param string $located       Template file filter by 3rd party plugin.
	 * @param array  $args          Passed arguments.
	 */
	do_action( 'give_before_template_part', $template_name, $template_path, $located, $args );

	include $located;

	/**
	 * Fires in give template, after the file is included.
	 *
	 * Allows you to execute code after the file is included.
	 *
	 * @since 1.6
	 *
	 * @param string $template_name Template file name.
	 * @param string $template_path Template file path.
	 * @param string $located       Template file filter by 3rd party plugin.
	 * @param array  $args          Passed arguments.
	 */
	do_action( 'give_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Retrieves a template part
 *
 * Taken from bbPress.
 *
 * @since 1.0
 *
 * @param string $slug Template part file slug {slug}.php.
 * @param string $name Optional. Template part file name {slug}-{name}.php. Default is null.
 * @param bool   $load If true the template file will be loaded, if it is found.
 *
 * @return string
 */
function give_get_template_part( $slug, $name = null, $load = true ) {

	/**
	 * Fires in give template part, before the template part is retrieved.
	 *
	 * Allows you to execute code before retrieving the template part.
	 *
	 * @since 1.0
	 *
	 * @param string $slug Template part file slug {slug}.php.
	 * @param string $name Template part file name {slug}-{name}.php.
	 */
	do_action( "get_template_part_{$slug}", $slug, $name );

	// Setup possible parts
	$templates = [];
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'give_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return give_locate_template( $templates, $load, false );
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Forked from bbPress
 *
 * @since 1.0
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool         $load           If true the template file will be loaded if it is found.
 * @param bool         $require_once   Whether to require_once or require. Default true.
 *                                     Has no effect if $load is false.
 *
 * @return string The template filename if one is located.
 */
function give_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet
	$located = false;

	$theme_template_paths = give_get_theme_template_paths();

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach ( $theme_template_paths as $template_path ) {

			if ( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if ( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @since  2.0.3
 * @access public
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 *
 * @return string
 */
function give_get_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = give_get_theme_template_dir_name() . '/';
	}

	if ( ! $default_path ) {
		$default_path = GIVE_PLUGIN_DIR . 'templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		[
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		]
	);

	// Get default template/
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	/**
	 * Filter the template
	 *
	 * @since 2.0.3
	 */
	return apply_filters( 'give_get_locate_template', $template, $template_name, $template_path );
}

/**
 * Returns a list of paths to check for template locations
 *
 * @since 1.0
 * @return array
 */
function give_get_theme_template_paths() {

	$template_dir = give_get_theme_template_dir_name();

	$file_paths = [
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => give_get_templates_dir(),
	];

	$file_paths = apply_filters( 'give_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the give_templates_dir filter.
 *
 * @since 1.0
 * @return string
 */
function give_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'give_templates_dir', 'give' ) );
}

/**
 * Adds Give Version to the <head> tag
 *
 * @since 1.0
 * @return void
 */
function give_version_in_header() {
	echo '<meta name="generator" content="Give v' . GIVE_VERSION . '" />' . "\n";
}

add_action( 'wp_head', 'give_version_in_header' );

/**
 * Determines if we're currently on the Donations History page.
 *
 * @since 1.0
 * @return bool True if on the Donations History page, false otherwise.
 */
function give_is_donation_history_page() {

	$ret = is_page( give_get_option( 'history_page' ) );

	return apply_filters( 'give_is_donation_history_page', $ret );
}

/**
 * Adds body classes for Give pages
 *
 * @since 1.0
 *
 * @param array $class current classes
 *
 * @return array Modified array of classes
 */
function give_add_body_classes( $class ) {
	$classes = (array) $class;

	if ( give_is_success_page() ) {
		$classes[] = 'give-success';
		$classes[] = 'give-page';
	}

	if ( give_is_failed_transaction_page() ) {
		$classes[] = 'give-failed-transaction';
		$classes[] = 'give-page';
	}

	if ( give_is_donation_history_page() ) {
		$classes[] = 'give-donation-history';
		$classes[] = 'give-page';
	}

	if ( give_is_test_mode() ) {
		$classes[] = 'give-test-mode';
		$classes[] = 'give-page';
	}

	// Theme-specific Classes used to prevent conflicts via CSS
	/* @var WP_Theme $current_theme */
	$current_theme = wp_get_theme();

	switch ( $current_theme->get_template() ) {

		case 'Divi':
			$classes[] = 'give-divi';
			break;
		case 'Avada':
			$classes[] = 'give-avada';
			break;
		case 'twentysixteen':
			$classes[] = 'give-twentysixteen';
			break;
		case 'twentyseventeen':
			$classes[] = 'give-twentyseventeen';
			break;
		case 'twentynineteen':
			$classes[] = 'give-twentynineteen';
			break;

	}

	return array_unique( $classes );
}

add_filter( 'body_class', 'give_add_body_classes' );


/**
 * Add Post Class Filter
 *
 * Adds extra post classes for forms
 *
 * @since       1.0
 *
 * @param array        $classes
 * @param string|array $class
 * @param int|string   $post_id
 *
 * @return array
 */
function give_add_post_class( $classes, $class = '', $post_id = '' ) {
	if ( ! $post_id || 'give_forms' !== get_post_type( $post_id ) ) {
		return $classes;
	}

	// @TODO: Add classes for custom taxonomy and form configurations (multi vs single donations, etc).

	if ( false !== ( $key = array_search( 'hentry', $classes ) ) ) {
		unset( $classes[ $key ] );
	}

	return $classes;
}


add_filter( 'post_class', 'give_add_post_class', 20, 3 );

/**
 * Get the placeholder image URL for forms etc
 *
 * @access public
 * @return string
 */
function give_get_placeholder_img_src() {

	$placeholder_url = esc_url( GIVE_PLUGIN_URL . 'assets/dist/images/give-placeholder.jpg');

	return apply_filters( 'give_placeholder_img_src', $placeholder_url );
}


/**
 * Global
 */
if ( ! function_exists( 'give_output_content_wrapper' ) ) {

	/**
	 * Output the start of the page wrapper.
	 */
	function give_output_content_wrapper() {
		give_get_template_part( 'global/wrapper-start' );
	}
}
if ( ! function_exists( 'give_output_content_wrapper_end' ) ) {

	/**
	 * Output the end of the page wrapper.
	 */
	function give_output_content_wrapper_end() {
		give_get_template_part( 'global/wrapper-end' );
	}
}

/**
 * Single Give Form
 */
if ( ! function_exists( 'give_left_sidebar_pre_wrap' ) ) {
	function give_left_sidebar_pre_wrap() {
		echo apply_filters( 'give_left_sidebar_pre_wrap', '<div id="give-sidebar-left" class="give-sidebar give-single-form-sidebar-left">' );
	}
}

if ( ! function_exists( 'give_left_sidebar_post_wrap' ) ) {
	function give_left_sidebar_post_wrap() {
		echo apply_filters( 'give_left_sidebar_post_wrap', '</div>' );
	}
}

if ( ! function_exists( 'give_get_forms_sidebar' ) ) {
	function give_get_forms_sidebar() {
		give_get_template_part( 'single-give-form/sidebar' );
	}
}

if ( ! function_exists( 'give_show_form_images' ) ) {

	/**
	 * Output the donation form featured image.
	 */
	function give_show_form_images() {
		if ( give_is_setting_enabled( give_get_option( 'form_featured_img' ) ) ) {
			give_get_template_part( 'single-give-form/featured-image' );
		}
	}
}

if ( ! function_exists( 'give_template_single_title' ) ) {

	/**
	 * Output the form title.
	 */
	function give_template_single_title() {
		give_get_template_part( 'single-give-form/title' );
	}
}

/**
 * Conditional Functions
 */

if ( ! function_exists( 'is_give_form' ) ) {

	/**
	 * is_give_form
	 *
	 * Returns true when viewing a single form.
	 *
	 * @since 1.6
	 * @since 2.10.4 Updated post type slug to match registration.
	 *
	 * @return bool
	 */
	function is_give_form() {
		return is_singular( [ 'give_forms' ] );
	}
}

if ( ! function_exists( 'is_give_category' ) ) {

	/**
	 * is_give_category
	 *
	 * Returns true when viewing give form category archive.
	 *
	 * @since 1.6
	 *
	 * @param string $term The term slug your checking for.
	 *                     Leave blank to return true on any.
	 *                     Default is blank.
	 *
	 * @return bool
	 */
	function is_give_category( $term = '' ) {
		return is_tax( 'give_forms_category', $term );
	}
}

if ( ! function_exists( 'is_give_tag' ) ) {

	/**
	 * is_give_tag
	 *
	 * Returns true when viewing give form tag archive.
	 *
	 * @since 1.6
	 *
	 * @param string $term The term slug your checking for.
	 *                     Leave blank to return true on any.
	 *                     Default is blank.
	 *
	 * @return bool
	 */
	function is_give_tag( $term = '' ) {
		return is_tax( 'give_forms_tag', $term );
	}
}

if ( ! function_exists( 'is_give_taxonomy' ) ) {

	/**
	 * is_give_taxonomy
	 *
	 * Returns true when viewing a give form taxonomy archive.
	 *
	 * @since 1.6
	 *
	 * @return bool
	 */
	function is_give_taxonomy() {
		return is_tax( get_object_taxonomies( 'give_form' ) );
	}
}
