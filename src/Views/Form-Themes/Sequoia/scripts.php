<?php

use function Give\Helpers\Script\getLocalizedScript;
use function Give\Helpers\Script\getScripTag;
use function Give\Helpers\Script\getStyleTag;

add_action( 'give_embed_head', 'give_ft_sequoia_page_styles' );
add_action( 'give_embed_footer', 'give_ft_sequoia_page_scripts' );


/**
 * Load form theme style
 */
function give_ft_sequoia_page_styles() {
	echo getStyleTag( Give()->scripts->get_frontend_stylesheet_uri() );
	echo getStyleTag( GIVE_PLUGIN_URL . 'assets/dist/css/give-elegent-theme.css' );
	echo getStyleTag( 'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap' );

	echo getLocalizedScript( 'give_global_vars', Give()->scripts->get_public_data() );
}


/**
 * Load form theme scripts
 */
function give_ft_sequoia_page_scripts() {
	echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/babel-polyfill.js' );
	echo getScripTag( includes_url( 'js/jquery/jquery.js' ) );

	// @todo: move js code to own file.
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
	echo getScripTag( 'https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/4.2.9/iframeResizer.contentWindow.min.js' );
	echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/give.js' );
	echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/give-elegent-theme.js' );

	Give()->scripts->stripe_frontend_scripts();
}
