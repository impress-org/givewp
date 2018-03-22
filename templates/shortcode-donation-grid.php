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
	<div class="give-card">
		<?php
		// The featured image.
		if (
			give_is_setting_enabled( $give_settings['form_featured_img'] ) &&
			has_post_thumbnail() &&
			'true' == $atts['show_featured_image']
		) {
			printf( '<div class="give-card__media">' );
			the_post_thumbnail();
			printf( '</div>' );
		}

		// The card heading.
		the_title( '<h3 class="give-card__heading">', '</h3>' );

		// The goal progess bar.
		if (
			give_is_setting_enabled( get_post_meta( $form_id, '_give_goal_option', true ) ) &&
			'true' == $atts['show_goal']
		) {
			echo '<div class="grid-item-progress">';
			give_show_goal_progress( $form_id );
			echo '</div>';
		}

		// The excerpt.
		if (
			give_is_setting_enabled( $give_settings['forms_excerpt'] ) &&
			'true' == $atts['show_excerpt']
		) {
			printf( '<div class="give-card__description">%s</div>', get_the_excerpt() );
		}

		// The 'Donate Now' button.
		if ( 'redirect' == $atts['display_type'] ) {

			// 'Donate Now' button if the 'display_type' attribute is set to 'redirect'
			printf(
				'<a class="grid-donate-now" href="%1$s">%2$s</a>',
				get_the_permalink(),
				apply_filters( 'donation_grid_donate_now', esc_html__( 'Donate Now', 'grid' ) )
			);

		} elseif ( 'modal' == $atts['display_type'] ) {

			// 'Donate Now' button if the 'display_type' attribute is set to 'modal'
			printf(
				'<a class="grid-donate-now grid-donate-now-modal-button" data-effect="mfp-zoom-out" href="#popup-form-%1$s">%2$s</a>',
				get_the_ID(),
				apply_filters( 'donation_grid_donate_now', esc_html__( 'Donate Now', 'grid' ) )
			);

			// The modal window.
			printf(
				'<div id="popup-form-%1$s" class="give-donation-grid-item-form zoom-anim-dialog mfp-hide">',
				get_the_ID()
			);
			give_get_donation_form( get_the_ID() );
			printf( '</div>' );
		}
		?>

	</div>
</div>
