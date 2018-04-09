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
				<h3 class="give-donor__name"><?php echo $donor->name; ?></h3>
			<?php endif; ?>

			<?php if ( true === $atts['show_total'] ) : ?>
				<span class="give-donor__total">
					<?php
					// If not filtered by form ID then display total donations
					echo give_currency_filter( give_format_amount( $donor->purchase_value, array(
						'sanitize' => false,
						'decimal'  => false
					) ) );

					// Else filtered by form ID, only display donations made for this form.
					?>
				</span>
			<?php endif; ?>

			<?php if ( true === $atts['show_time'] ) : ?>
				<span class="give-donor__timestamp">
					<?php
					// If not filtered by form ID then display the "Donor Since" text.

					// If filtered by form ID then display the last donation date.
					echo $donor->get_last_donation_date( true ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( true === $atts['show_comments'] ) : ?>
		<div class="give-donor__content">
			<p>
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tempus, dui eu
				posuere viverra, orci tortor congue urna, non fringilla enim tellus sed quam. Maecenas vel mattis erat.
				Maecenas tincidunt neque a orci dapibus faucibus. Curabitur nulla ex, scelerisque vel congue in.
			</p>
		</div>
	<?php endif; ?>
</div>
