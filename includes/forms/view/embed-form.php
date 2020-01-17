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
	<style>
		body {
			max-width: 500px;
			min-width: 301px;
			margin: 0;
			padding: 0;
			border: 1px solid #e1e1e1
		}
	</style>
	<body>
		<?php
		// Fetch the Give Form.
		ob_start();
		give_get_donation_form( $atts );
		echo ob_get_clean();

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
	</body>
</html>
