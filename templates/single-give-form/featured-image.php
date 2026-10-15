<?php
/**
 * Single Form Featured Image
 *
 * Displays the featured image for the single donation form - Override this template by copying it to yourtheme/give/single-give-form/featured-image.php
 *
 * @package       Give/Templates
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since TBD Escape output. Add the `givewp_single_form_large_thumbnail_size` and `givewp_single_form_image_html` filters; the old `single_give_form_*` names still run, as deprecated.
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template variables; this file is included inside a function, so they are not globals.

global $post;

/**
 * Fires in single form template, before the form featured image.
 *
 * Allows you to add elements before the image.
 *
 * @since 1.0
 */
do_action( 'give_pre_featured_thumbnail' );
?>

<div class="images">
	<?php
	// Featured Thumbnail
	if ( has_post_thumbnail() ) {

		$image_size = give_get_option( 'featured_image_size' );
		$image_size = ! empty( $image_size ) ? $image_size : 'large';

		/**
		 * Filter the size of the form featured image. Deprecated: use `givewp_single_form_large_thumbnail_size`.
		 *
		 * @since TBD Deprecated in favor of `givewp_single_form_large_thumbnail_size`.
		 * @since 1.0
		 */
		$image_size = apply_filters_deprecated( 'single_give_form_large_thumbnail_size', [ $image_size ], 'TBD', 'givewp_single_form_large_thumbnail_size' );

		/**
		 * Filter the size of the form featured image.
		 *
		 * @since TBD Replaces `single_give_form_large_thumbnail_size`.
		 */
		$image_size = apply_filters( 'givewp_single_form_large_thumbnail_size', $image_size );
		$image      = get_the_post_thumbnail( $post->ID, $image_size );

		/**
		 * Filter the form featured image HTML. Deprecated: use `givewp_single_form_image_html`.
		 *
		 * @since TBD Deprecated in favor of `givewp_single_form_image_html`.
		 * @since 1.0
		 */
		$image = apply_filters_deprecated( 'single_give_form_image_html', [ $image ], 'TBD', 'givewp_single_form_image_html' );

		/**
		 * Filter the form featured image HTML.
		 *
		 * @since TBD Replaces `single_give_form_image_html`.
		 */
		echo wp_kses_post( apply_filters( 'givewp_single_form_image_html', $image ) );

	} else {

		// Placeholder Image
		$placeholder = sprintf( '<img src="%s" alt="%s" />', esc_url( give_get_placeholder_img_src() ), esc_attr__( 'Placeholder', 'give' ) );

		/** This filter is documented above. Deprecated: use `givewp_single_form_image_html`. */
		$placeholder = apply_filters_deprecated( 'single_give_form_image_html', [ $placeholder, $post->ID ], 'TBD', 'givewp_single_form_image_html' );

		/** This filter is documented above. */
		echo wp_kses_post( apply_filters( 'givewp_single_form_image_html', $placeholder, $post->ID ) );

	}
	?>
</div>

<?php
/**
 * Fires in single form template, after the form featured image.
 *
 * Allows you to add elements after the image.
 *
 * @since 1.0
 */
do_action( 'give_post_featured_thumbnail' );
?>
