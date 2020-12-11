<?php
/**
 * This template is used to display the donation grid with [give_donor_wall]
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
	<div class="give-donor give-card">
		<div class="give-donor__header">
			<?php
			if ( true === $atts['show_avatar'] ) {

				// Get anonymous donor image.
				$anonymous_donor_img = sprintf(
					'<img src="%1$s" alt="%2$s">',
					esc_url( GIVE_PLUGIN_URL . 'assets/dist/images/anonymous-user.svg' ),
					esc_attr__( 'Anonymous User', 'give' )
				);

				$donor_avatar = sprintf(
					'%2$s<div class="give-donor__name_initial">%1$s</div>',
					$donation['name_initial'],
					$anonymous_donor_img
				);

				// Validate donor gravatar.
				$validate_gravatar = ! empty( $donation['_give_anonymous_donation'] ) ? 0 : give_validate_gravatar( $donation['_give_payment_donor_email'] );

				// Maybe display the Avatar.
				echo sprintf(
					'<div class="give-donor__image" data-donor_email="%1$s" data-has-valid-gravatar="%2$s" data-avatar-size="%3$s" data-anonymous-donation="%5$s" style="max-width:%3$spx;">%4$s</div>',
					md5( strtolower( trim( $donation['_give_payment_donor_email'] ) ) ),
					absint( $validate_gravatar ),
					$atts['avatar_size'],
					$donor_avatar,
					(int) ! empty( $donation['_give_anonymous_donation'] )
				);
			}
			?>

			<div class="give-donor__details">
				<?php if ( true === $atts['show_name'] ) : ?>
					<h3 class="give-donor__name">
						<?php
						// Get donor name based on donation parameter.
						$donor_name = ! empty( $donation['_give_anonymous_donation'] )
							? esc_html__( 'Anonymous', 'give' )
							: trim( $donation['_give_donor_billing_first_name'] . ' ' . $donation['_give_donor_billing_last_name'] );
						?>
						<?php echo esc_html( $donor_name ); ?>
					</h3>
				<?php endif; ?>

				<?php if ( true === $atts['show_company_name'] && isset( $donation['_give_donation_company'] ) ) : ?>
					<h3 class="give-donor__name">
						<?php echo esc_html( $donation['_give_donation_company'] ); ?>
					</h3>
				<?php endif; ?>

				<?php if ( true === $atts['show_total'] ) : ?>
					<span class="give-donor__total">
						<?php echo esc_html( give_donation_amount( $donation['donation_id'], true ) ); ?>
					</span>
				<?php endif; ?>

				<?php if ( true === $atts['show_time'] ) : ?>
					<span class="give-donor__timestamp">
						<?php echo esc_html( give_get_formatted_date( $donation['donation_date'], give_date_format(), 'Y-m-d H:i:s', true ) ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>

		<?php
		if (
			true === $atts['show_comments']
			&& absint( $atts['comment_length'] )
			&& ! empty( $donation['donor_comment'] )
			&& ! $donation['_give_anonymous_donation']
		) :
			?>
			<div class="give-donor__content">
				<?php
				$comment     = trim( $donation['donor_comment'] );
				$total_chars = strlen( $comment );
				$max_chars   = $atts['comment_length'];

				// A truncated excerpt is displayed if the comment is too long.
				if ( $max_chars < $total_chars ) {
					$excerpt    = '';
					$offset     = -( $total_chars - $max_chars );
					$last_space = strrpos( $comment, ' ', $offset );

					if ( $last_space ) {
						// Truncate excerpt at last space before limit.
						$excerpt = substr( $comment, 0, $last_space );
					} else {
						// There are no spaces, so truncate excerpt at limit.
						$excerpt = substr( $comment, 0, $max_chars );
					}

					$excerpt = trim( $excerpt, '.!,:;' );

					echo sprintf(
						'<p class="give-donor__excerpt">%s&hellip;<span> <a class="give-donor__read-more">%s</a></span></p>',
						nl2br( esc_html( $excerpt ) ),
						esc_html( $atts['readmore_text'] )
					);
				}

				echo sprintf(
					'<p class="give-donor__comment">%s</p>',
					nl2br( esc_html( $comment ) )
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</div>
