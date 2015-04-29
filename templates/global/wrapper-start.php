<?php
/**
 * Content wrappers
 *
 * @package     Give
 * @subpackage  Templates/Global
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven' :
		echo '<div id="primary" class="give-wrap"><div id="content" role="main" class="twentyeleven">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content give-wrap"><div id="content" role="main" class="twentytwelve">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="site-content give-wrap"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen' :
		echo '<div id="primary" class="content-area give-wrap"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfgive">';
		break;
	case 'twentyfifteen' :
		echo '<div id="primary" role="main" class="content-area twentyfifteen give-wrap"><div id="main" class="site-main t15give">';
		break;
	case 'flatsome' :
		echo '<div id="container" class="row product-page give-wrap"><div id="content" role="main">';
		break;
	case 'x' :
		echo '<div id="container" class="x-container-fluid max width offset cf give-wrap"><div class="x-main full" role="main"><div class="entry-wrap"><div class="entry-content">';
		break;
	default :
		echo apply_filters( 'give_default_wrapper_start', '<div id="container" class="give-wrap"><div id="content" role="main">' );
		break;
}
