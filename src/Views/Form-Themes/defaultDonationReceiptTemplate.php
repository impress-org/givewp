<!DOCTYPE html>
<html lang="en" class="give-form-styles" style="margin-top: 0 !important;">
	<head>
		<meta charset="utf-8">
		<title><?php _e( 'Donation Receipt', 'give' ); ?></title>

		<?php
		/**
		 * Fire the action hook in header
		 */
		do_action( 'give_embed_head' );
		?>
	</head>
	<body>
		<?php
		global $post;
		// Fetch the Give Form.
		ob_start();

		echo apply_filters( 'the_content', $post->post_content );

		echo ob_get_clean();

		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );
		?>
	</body>
</html>
