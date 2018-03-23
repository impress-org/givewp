<?php
/**
 * This template is used to display the donation grid with [donation_grid]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_id       = get_the_ID(); // Form ID.
$give_settings = $args[0]; // Give settings.
$atts          = $args[1]; // Shortcode attributes.
?>

<div class="give-grid__item">
	<?php
	// Print the opening anchor tag based on display style.
	if ( 'redirect' == $atts['display_style'] ) {
		printf(
			'<a class="give-card" href="%1$s">',
			esc_attr( get_the_permalink() )
		);
	} elseif ( 'modal' == $atts['display_style'] ) {
		printf(
			'<a class="give-card js-give-grid-modal-launcher" data-effect="mfp-zoom-out" href="#popup-form-%1$s">',
			get_the_ID()
		);
	}
	?>

		<div class="give-card__body">
			<?php
			// Display the card heading.
			the_title( '<h3 class="give-card__title">', '</h3>' );

			// Maybe display the form excerpt.
			if (
				give_is_setting_enabled( $give_settings['forms_excerpt'] )
				&& 'true' == $atts['show_excerpt']
			) {
				printf( '<p class="give-card__text">%s</p>', get_the_excerpt() );
			}

			// Maybe display the goal progess bar.
			if (
				give_is_setting_enabled( get_post_meta( $form_id, '_give_goal_option', true ) )
				&& 'true' == $atts['show_goal']
			) {
				echo '<div class="give-card__progress">';
					give_show_goal_progress( $form_id );
				echo '</div>';
			}

			// If modal, print form in hidden container until it is time to be revealed.
			if ( 'modal' == $atts['display_style'] ) {
				printf(
					'<div id="popup-form-%1$s" class="give-donation-grid-item-form zoom-anim-dialog mfp-hide">',
					get_the_ID()
				);
				give_get_donation_form( get_the_ID() );
				echo '</div>';
			}
			?>
		</div>

		<?php
		// Maybe display the featured image.
		if (
			give_is_setting_enabled( $give_settings['form_featured_img'] )
			&& has_post_thumbnail()
			&& 'true' == $atts['show_featured_image']
		) {
			printf( '<div class="give-card__media">' );
				the_post_thumbnail();
			printf( '</div>' );
		}
		?>
	</a>
</div>
