<?php
use Give\Form\ThemeLoader;
use function Give\Helpers\Script\getLocalizedScript;
use function Give\Helpers\Script\getStyleTag;

global $post;


// Load form theme.
$themeLoader = new ThemeLoader( $post->ID );
$themeLoader->init();

$atts = array( 'display_style' => 'onpage' );
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
		give_get_donation_form( array_map( 'give_clean', wp_parse_args( $_SERVER['QUERY_STRING'] ) ) );
		?>
		<script>
			var iFrameResizer = {
				targetOrigin: '<?php echo esc_js( home_url() ); ?>',
				onReady: function(){
					window.parentIFrame.sendMessage( 'giveEmbedFormContentLoaded' );
				},
				onMessage: function( message ) {
					console.log( message );

					if ('currentPage' in message) {
						let $field = document.getElementsByName( 'give-current-url' );
						if( $field.length ) {
							$field[0].setAttribute('value', message.currentPage);
						}
					}
				}
			}
		</script>
		<?php
		/**
		 * Fire the action hook in footer
		 */
		do_action( 'give_embed_footer' );

		// Stripe scripts
		Give()->scripts->stripe_frontend_scripts();
		?>
	</body>
</html>
