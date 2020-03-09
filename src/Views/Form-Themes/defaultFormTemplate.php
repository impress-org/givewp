<?php
use Give\Form\ThemeLoader;
?>
<!DOCTYPE html>
<html lang="en" class="give-form-styles" style="margin-top: 0 !important;">
	<?php
	global $post;

	$atts = array( 'display_style' => 'onpage' );
	?>
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
		// Load form theme.
		$themeLoader = new ThemeLoader( $post->ID );
		$themeLoader->init();

		// Fetch the Give Form.
		ob_start();
		give_get_donation_form( array_map( 'give_clean', wp_parse_args( $_SERVER['QUERY_STRING'] ) ) );
		echo ob_get_clean();

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
	</body>
</html>
