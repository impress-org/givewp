<?php
/**
 * Single Form Featured Image
 *
 * @package       Give/Templates
 * @description Displays the featured image for the single donation form - Override this template by copying it to yourtheme/give/single-give-form/featured-image.php
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

/**
 * Fires before the featured thumbnail
 *
 * @since 1.0
 */
do_action( 'give_pre_featured_thumbnail' );
?>
<div class="images">
	<?php //Featured Thumbnail
	if ( has_post_thumbnail() ) {
		
		$image_size = give_get_option( 'featured_image_size' );
		$image      = get_the_post_thumbnail( $post->ID, apply_filters( 'single_give_form_large_thumbnail_size', ( ! empty( $image_size ) ? $image_size : 'large' ) ) );

		echo apply_filters( 'single_give_form_image_html', $image );

	} else {

		//Placeholder Image
		echo apply_filters( 'single_give_form_image_html', sprintf( '<img src="%s" alt="%s" />', give_get_placeholder_img_src(), __( 'Placeholder', 'give' ) ), $post->ID );

	} ?>
</div>

<?php do_action( 'give_post_featured_thumbnail' ); ?>
