<?php
use Give\Form\ThemeLoader;

global $post;

$queryString   = array_map( 'give_clean', wp_parse_args( $_SERVER['QUERY_STRING'] ) );
$shortcodeArgs = array_intersect_key( $queryString, give_get_default_form_shortcode_args() );
$formTheme     = ! empty( $shortcodeArgs['form_theme'] ) ? $shortcodeArgs['form_theme'] : '';

// Load form theme.
$themeLoader = new ThemeLoader( $post->ID, $formTheme );
$themeLoader->init();
?>
<!DOCTYPE html>
<html lang="en" class="give-form-styles" style="margin-top: 0 !important;">
	<head>
		<meta charset="utf-8">
		<title><?php echo esc_html( $post->post_title ); ?></title>
		<?php
		/**
		 * Fire the action hook in header
		 */
		do_action( 'give_embed_head' );
		?>
	</head>
	<body>
		<?php
		// Fetch the Give Form.
		give_get_donation_form( $shortcodeArgs );

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
	</body>
</html>
