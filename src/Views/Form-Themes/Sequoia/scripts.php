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
	echo getStyleTag( GIVE_PLUGIN_URL . 'assets/dist/css/give-sequoia-theme.css' );
	echo getStyleTag( 'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap' );
}


/**
 * Load form theme scripts
 */
function give_ft_sequoia_page_scripts() {
	echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/babel-polyfill.js' );
	echo getScripTag( includes_url( 'js/jquery/jquery.js' ) );
	echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/give.js' );
	echo getScripTag( GIVE_PLUGIN_URL . 'assets/dist/js/give-sequoia-theme.js' );
}
