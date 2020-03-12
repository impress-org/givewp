<?php

/**
 * Load form theme style
 *
 * @since 2.7.0
 */
function give_ft_sequoia_enqueue_scripts() {
	wp_enqueue_style( 'give-google-font-montserrat', 'https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap', array(), GIVE_VERSION );
	wp_enqueue_style( 'give-sequoia-theme-css', GIVE_PLUGIN_URL . 'assets/dist/css/give-sequoia-theme.css', array( 'give-styles' ), GIVE_VERSION );
	wp_enqueue_script( 'give-sequoia-theme-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-sequoia-theme.js', array( 'give' ), GIVE_VERSION, true );

}
add_action( 'wp_enqueue_scripts', 'give_ft_sequoia_enqueue_scripts' );

