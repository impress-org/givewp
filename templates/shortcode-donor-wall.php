<?php
/**
 * This template is used to display the donation grid with [donation_grid]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $donor Give_Donor */
$donation = $args[0];

$give_settings = $args[1]; // Give settings.
$atts          = $args[2]; // Shortcode attributes.
?>

<div class="give-grid__item">
	<div class="give-donor">
		<div class="give-donor__header">
			<?php
			// Maybe display the Avatar.
			if ( true === $atts['show_avatar'] ) {
				echo '<div class="give-donor__image">';
				if ( give_validate_gravatar( $donation['_give_payment_donor_email'] ) ) {
					echo get_avatar( $donation['_give_payment_donor_email'], 60 );
				} else {
					echo $donation['name_initial'];
				}
				echo '</div>';
			}
			?>

			<div class="give-donor__details">
				<?php if ( true === $atts['show_name'] ) : ?>
					<h3 class="give-donor__name">
						<?php $donor_name = trim( $donation['_give_donor_billing_first_name'] . ' ' . $donation['_give_donor_billing_last_name'] ); ?>
						<?php esc_html_e( $donor_name ); ?>
					</h3>
				<?php endif; ?>

				<?php if ( true === $atts['show_total'] ) : ?>
					<span class="give-donor__total">
						<?php echo give_donation_amount( $donation['donation_id'], true ); ?>
					</span>
				<?php endif; ?>

				<?php if ( true === $atts['show_time'] ) : ?>
					<span class="give-donor__timestamp">
						<?php echo date_i18n( give_date_format(), strtotime( $donation['_give_completed_date'] ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<?php
		if (
			true === $atts['show_comments']
			&& absint( $atts['comment_length'] )
			&& ! empty( $donation['donor_comment'] )
		) :
			?>
			<div class="give-donor__content">
				<?php
				$comment_content = apply_filters( 'the_content', $donation['donor_comment'] );

				if ( $atts['comment_length'] < strlen( $donation['donor_comment'] ) ) {
					echo sprintf(
						'<p class="give-donor__comment_excerpt">%s&hellip;<span>&nbsp;<a class="give-donor__read-more">%s</a></span></p>',
						substr( $comment_content, 0, strpos( $comment_content, ' ', $atts['comment_length'] ) ),
						$atts['readmore_text']
					);

					echo sprintf(
						'<div class="give-donor__comment" style="display: none">%s</div>',
						$comment_content
					);
				} else {
					echo $comment_content;
				}
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
