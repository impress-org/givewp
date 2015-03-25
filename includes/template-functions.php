<?php
/**
 * Template Functions
 *
 * @package     Give
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
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
 * Retrieves a template part
 *
 * @since v1.0
 *
 * Taken from bbPress
 *
 * @param string $slug
 * @param string $name Optional. Default null
 * @param bool   $load
 *
 * @return string
 *
 * @uses  give_locate_template()
 * @uses  load_template()
 * @uses  get_template_part()
 */
function give_get_template_part( $slug, $name = null, $load = true ) {

	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
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
 * Taken from bbPress
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

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach ( give_get_theme_template_paths() as $template_path ) {

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
 * Returns a list of paths to check for template locations
 *
 * @since 1.0
 * @return mixed|void
 */
function give_get_theme_template_paths() {

	$template_dir = give_get_theme_template_dir_name();

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => give_get_templates_dir()
	);

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
 * Should we add schema.org microdata?
 *
 * @since 1.0
 * @return bool
 */
function give_add_schema_microdata() {
	// Don't modify anything until after wp_head() is called
	$ret = did_action( 'wp_head' );

	return apply_filters( 'give_add_schema_microdata', $ret );
}

/**
 * Add Microdata to Give titles
 *
 * @since  1.0
 * @author Sunny Ratilal
 *
 * @param string $title Post Title
 * @param int    $id    Post ID
 *
 * @return string $title New title
 */
function give_microdata_title( $title, $id = 0 ) {

	if ( ! give_add_schema_microdata() ) {
		return $title;
	}

	if ( is_singular( 'give_forms' ) && 'give_forms' == get_post_type( intval( $id ) ) ) {
		$title = '<span itemprop="name">' . $title . '</span>';
	}

	return $title;
}

add_filter( 'the_title', 'give_microdata_title', 10, 2 );

/**
 * Add Microdata to download description
 *
 * @since  1.5
 * @author Sunny Ratilal
 *
 * @param $content
 *
 * @return mixed|void New title
 */
function give_microdata_wrapper( $content ) {
	global $post;

	if ( ! give_add_schema_microdata() ) {
		return $content;
	}

	if ( $post && $post->post_type == 'give_forms' && is_singular() && is_main_query() ) {
		$content = apply_filters( 'give_microdata_wrapper', '<div itemscope itemtype="http://schema.org/Product" itemprop="description">' . $content . '</div>' );
	}

	return $content;
}

//add_filter( 'the_content', 'give_microdata_wrapper', 10 );

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
 * Determines if we're currently on the Purchase History page.
 *
 * @since 1.0
 * @return bool True if on the Purchase History page, false otherwise.
 */
function give_is_purchase_history_page() {
	global $give_options;
	$ret = isset( $give_options['purchase_history_page'] ) ? is_page( $give_options['purchase_history_page'] ) : false;

	return apply_filters( 'give_is_purchase_history_page', $ret );
}

/**
 * Adds body classes for Give pages
 *
 * @since 1.0
 *
 * @param array $classes current classes
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

	if ( give_is_purchase_history_page() ) {
		$classes[] = 'give-purchase-history';
		$classes[] = 'give-page';
	}

	if ( give_is_test_mode() ) {
		$classes[] = 'give-test-mode';
		$classes[] = 'give-page';
	}

	return array_unique( $classes );
}

add_filter( 'body_class', 'give_add_body_classes' );


/**
 * Get an image size.
 *
 * Variable is filtered by give_get_image_size_{image_size}
 *
 * @param string $image_size
 *
 * @return array
 */
function give_get_image_size( $image_size ) {
	if ( in_array( $image_size, array( 'give_form_thumbnail', 'give_form_single' ) ) ) {
		$size           = get_option( $image_size . '_image_size', array() );
		$size['width']  = isset( $size['width'] ) ? $size['width'] : '300';
		$size['height'] = isset( $size['height'] ) ? $size['height'] : '300';
		$size['crop']   = isset( $size['crop'] ) ? $size['crop'] : 0;
	} else {
		$size = array(
			'width'  => '300',
			'height' => '300',
			'crop'   => 1
		);
	}

	return apply_filters( 'give_get_image_size_' . $image_size, $size );
}

/**
 * Get the placeholder image URL for forms etc
 *
 * @access public
 * @return string
 */
function give_get_placeholder_img_src() {

	$image_size = give_get_image_size( 'give_form_thumbnail' );

	$placeholder_url = 'http://placehold.it/' . $image_size['width'] . 'x' . $image_size['height'] . '&text=' . __( 'Give+Placeholder+Image+', 'give' ) . '(' . $image_size['width'] . 'x' . $image_size['height'] . ')';

	return apply_filters( 'give_placeholder_img_src', $placeholder_url );
}


/** Global ****************************************************************/

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

if ( ! function_exists( 'give_get_sidebar' ) ) {

	/**
	 * Get the shop sidebar template.
	 */
	function give_get_sidebar() {
		give_get_template_part( 'global/sidebar' );
	}
}


/** Single Give Form ********************************************************/
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

if ( ! function_exists( 'give_show_form_images' ) ) {

	/**
	 * Output the product image before the single product summary.
	 */
	function give_show_form_images() {
		give_get_template_part( 'single-give-form/featured-image' );
	}
}

if ( ! function_exists( 'give_template_single_title' ) ) {

	/**
	 * Output the product title.
	 */
	function give_template_single_title() {
		give_get_template_part( 'single-give-form/title' );
	}
}
if ( ! function_exists( 'give_show_avatars' ) ) {

	/**
	 * Output the product title.
	 */
	function give_show_avatars() {
		echo do_shortcode( '[give_donators_gravatars]' );
	}
}