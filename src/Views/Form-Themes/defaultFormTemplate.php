<?php
use Give\Form\ThemeLoader;
use function Give\Helpers\Script\getLocalizedScript;
use function Give\Helpers\Script\getStyleTag;
use function Give\Helpers\Script\getScripTag;

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
		<?php echo getLocalizedScript( 'give_global_vars', Give()->scripts->get_public_data() ); ?>
		<?php echo getStyleTag( Give()->scripts->get_frontend_stylesheet_uri() ); ?>
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

		echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/babel-polyfill.js' );
		echo getScripTag( includes_url( 'js/jquery/jquery.js' ) );
		echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/give.js' );

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );

		// Stripe scripts
		Give()->scripts->stripe_frontend_scripts();
		?>
	</body>
</html>
