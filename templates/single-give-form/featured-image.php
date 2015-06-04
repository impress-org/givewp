<?php
/**
 * Single Form Featured Image
 *
 * @package       Give/Templates
 * @version       1.0
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

	<?php
	if ( has_post_thumbnail() ) {

		$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
		$image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
		$image       = get_the_post_thumbnail( $post->ID, apply_filters( 'single_give_form_large_thumbnail_size', 'give_form_single' ), array(
			'title' => $image_title
		) );

		echo apply_filters( 'single_give_form_image_html', sprintf( '<a href="%s" itemprop="image" class="give-main-image" title="%s">%s</a>', $image_link, $image_title, $image ), $post->ID );

	} else {

		echo apply_filters( 'single_give_form_image_html', sprintf( '<img src="%s" alt="%s" />', give_get_placeholder_img_src(), __( 'Placeholder', 'give' ) ), $post->ID );

	}
	?>
</div>

<?php do_action( 'give_post_featured_thumbnail' ); ?>
