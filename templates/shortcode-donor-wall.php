<?php
/**
 * This template is used to display the donation grid with [donation_grid]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $donor Give_Donor */
$donor = $args[0];
$donor = new Give_Donor( $donor->id );

$give_settings = $args[1]; // Give settings.
$atts          = $args[2]; // Shortcode attributes.
?>

<div class="give-grid__item">
	<div class="give-donor">
		<div class="give-donor__header">
			<?php
			// Maybe display the Avatar.
			if ( true === $atts['show_avatar'] ) {
				echo give_get_donor_avatar( $donor );
			}
			?>

			<div class="give-donor__details">
				<?php if ( true === $atts['show_name'] ) : ?>
					<h3 class="give-donor__name"><?php esc_html_e( $donor->name ); ?></h3>
				<?php endif; ?>

				<?php if ( true === $atts['show_total'] ) : ?>
					<span class="give-donor__total">
						<?php
						// If not filtered by form ID then display total donations
						// Else filtered by form ID, only display donations made for this form.
						$donated_amount = $donor->purchase_value;

						if ( ! empty( $atts['form_id'] ) ) {
							$donated_amount = Give_Donor_Stats::donated(
								array(
									'donor'      => $donor->id,
									'give_forms' => $atts['form_id']
								)
							);
						}

						echo give_currency_filter( give_format_amount( $donated_amount, array( 'sanitize' => false ) ) );
						?>
					</span>
				<?php endif; ?>

				<?php if ( true === $atts['show_time'] ) : ?>
					<span class="give-donor__timestamp">
						<?php
						// If not filtered by form ID then display the "Donor Since" text.
						// If filtered by form ID then display the last donation date.
						echo $donor->get_last_donation_date( true );
						?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<?php
		$comment = give_get_donor_latest_comment( $donor->id, $atts['form_id'] );

		if ( true === $atts['show_comments'] && absint( $atts['comment_length'] ) && $comment instanceof WP_Comment ) :
		?>
			<div class="give-donor__content">
					<?php
					if ( $atts['comment_length'] < strlen( $comment->comment_content ) ) {
						echo sprintf(
							'<p class="give-donor__comment_excerpt">%s&hellip;<span>&nbsp;<a class="give-donor__read-more">%s</a></span></p>',
							substr( $comment->comment_content, 0, $atts['comment_length'] ),
							$atts['readmore_text']
						);

						echo sprintf(
							'<div class="give-donor__comment" style="display: none">%s</div>',
							apply_filters( 'the_content', $comment->comment_content )
						);
					} else {
						echo apply_filters( 'the_content', $comment->comment_content );
					}
					?>
			</div>
		<?php endif; ?>
	</div>
</div>