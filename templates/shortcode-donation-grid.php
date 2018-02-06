<div class="give-donation-grid-item give-grid-col-<?php echo esc_attr( $atts['columns'] ); ?>">
	<div class="box">
		<?php
		$form_ID = get_the_ID();

		// The featured image.
		if ( 'enabled' === $give_settings['form_featured_img'] && has_post_thumbnail() ) {
			printf( '<div class="donation-grid-featured-image">' );
			the_post_thumbnail();
			printf( '</div>' );
		}

		// The title.
		the_title( '<span class="donation-grid-title">', '</span>' );

		// The progess bar for goal.
		if ( 'enabled' === get_post_meta( $form_ID, '_give_goal_option', true ) ) {
			give_show_goal_progress( $form_ID );
		}

		// The excerpt.
		if ( 'enabled' === $give_settings['forms_excerpt'] ) {
			printf( '<div class="donor-grid-excerpt">%s</div>', get_the_excerpt() );
		}

		// Donate now button.
		printf( '<a class="grid-donate-now" href="%1$s">%2$s</a>', get_the_permalink(), apply_filters( 'donation_grid_donate_now', esc_html__( 'Donate Now', 'grid' ) ) );
		?>
	</div>
</div>