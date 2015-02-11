<?php
/**
 * Front-end Actions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks Give actions, when present in the $_GET superglobal. Every give_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
 */
function give_get_actions() {
	if ( isset( $_GET['give_action'] ) ) {
		do_action( 'give_' . $_GET['give_action'], $_GET );
	}
}

add_action( 'init', 'give_get_actions' );

/**
 * Hooks Give actions, when present in the $_POST superglobal. Every give_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
 */
function give_post_actions() {
	if ( isset( $_POST['give_action'] ) ) {
		do_action( 'give_' . $_POST['give_action'], $_POST );
	}
}

add_action( 'init', 'give_post_actions' );