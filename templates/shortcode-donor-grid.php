<?php
/**
 * This template is used to display the donation grid with [donation_grid]
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var $donor Give_Donor */
$donor            = $args[0];
$give_settings    = $args[1]; // Give settings.
$atts             = $args[2]; // Shortcode attributes.
$raw_content      = ''; // Raw form content.
$stripped_content = ''; // Form content stripped of HTML tags and shortcodes.
$excerpt          = ''; // Trimmed form excerpt ready for display.

?>

<div class="give-grid__item">

	<div class="give-card__body">
		<?php
		// Maybe display the Avatar.
		if ( true === $atts['show_avatar'] ) :
			echo get_avatar( 'email@example.com', 32 );
		endif;
		?>

		<?php // Maybe display the form title.
		if ( true === $atts['show_name'] ) : ?>
			<h3 class="give-card__title"><?php echo $donor->name; ?></h3>
		<?php endif; ?>
	</div>
	</a>
</div>
