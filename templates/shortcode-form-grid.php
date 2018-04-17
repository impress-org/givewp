<?php
/**
 * This template is used to display the donation grid with [donation_grid]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_id          = get_the_ID(); // Form ID.
$give_settings    = $args[0]; // Give settings.
$atts             = $args[1]; // Shortcode attributes.
$raw_content      = ''; // Raw form content.
$stripped_content = ''; // Form content stripped of HTML tags and shortcodes.
$excerpt          = ''; // Trimmed form excerpt ready for display.
?>

<div class="give-grid__item">
	<?php
	// Print the opening anchor tag based on display style.
	if ( 'redirect' === $atts['display_style'] ) {
		printf(
			'<a id="give-card-%1$s" class="give-card" href="%2$s">',
			esc_attr( $form_id ),
			esc_attr( get_the_permalink() )
		);
	} elseif ( 'modal_reveal' === $atts['display_style'] ) {
		printf(
			'<a id="give-card-%1$s" class="give-card js-give-grid-modal-launcher" data-effect="mfp-zoom-out" href="#give-modal-form-%1$s">',
			esc_attr( $form_id )
		);
	}
	?>

		<div class="give-card__body">
			<?php
			// Maybe display the form title.
			if ( true === $atts['show_title'] ) {
				the_title( '<h3 class="give-card__title">', '</h3>' );
			}

			// Maybe display the form excerpt.
			if ( true === $atts['show_excerpt'] ) {
				if ( has_excerpt( $form_id ) ) {
					// Get excerpt from the form post's excerpt field.
					$raw_content      = get_the_excerpt( $form_id );
					$stripped_content = wp_strip_all_tags(
						strip_shortcodes( $raw_content )
					);
				} else {
					// Get content from the form post's content field.
					$raw_content = give_get_meta( $form_id, '_give_form_content', true );

					if ( ! empty( $raw_content ) ) {
						$stripped_content = wp_strip_all_tags(
							strip_shortcodes( $raw_content )
						);
					}
				}

				// Maybe truncate excerpt.
				if ( 0 < $atts['excerpt_length'] ) {
					$excerpt = wp_trim_words( $stripped_content, $atts['excerpt_length'] );
				} else {
					$excerpt = $stripped_content;
				}

				printf( '<p class="give-card__text">%s</p>', $excerpt );
			}

			// Maybe display the goal progess bar.
			if (
				give_is_setting_enabled( get_post_meta( $form_id, '_give_goal_option', true ) )
				&& true === $atts['show_goal']
			) {
				echo '<div class="give-card__progress">';
					give_show_goal_progress( $form_id );
				echo '</div>';
			}
			?>
		</div>

		<?php
		// Maybe display the featured image.
		if (
			give_is_setting_enabled( $give_settings['form_featured_img'] )
			&& has_post_thumbnail()
			&& true === $atts['show_featured_image']
		) {
			/*
			 * Filters the image size used in card layouts.
			 *
			 * @param string The image size.
			 * @param array  Form grid attributes.
			 */
			$image_size = apply_filters( 'give_form_grid_image_size', $atts['image_size'], $atts );
			$image_attr = '';

			echo '<div class="give-card__media">';
				if ( 'auto' !== $atts['image_height'] ) {
					$image_attr = array(
						'style' => 'height: ' . $atts['image_height'],
					);
				}
				the_post_thumbnail( $image_size, $image_attr );
			echo '</div>';
		}
		?>
	</a>
	<?php
	// If modal, print form in hidden container until it is time to be revealed.
	if ( 'modal_reveal' === $atts['display_style'] ) {
		printf(
			'<div id="give-modal-form-%1$s" class="give-donation-grid-item-form give-modal--slide mfp-hide">',
			$form_id
		);
		give_get_donation_form( $form_id );
		echo '</div>';
	}
	?>
</div>
