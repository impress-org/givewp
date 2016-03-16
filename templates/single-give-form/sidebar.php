<?php
/**
 * Single Give Form Sidebar
 *
 * @package     Give
 * @subpackage  Templates/Single-Give-Form
 * @description Adds a dynamic sidebar to single Give Forms (singular post type for give_forms) - Override this template by copying it to yourtheme/give/single-give-form/sidebar.php
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( is_active_sidebar( 'give-forms-sidebar' ) ) {
	dynamic_sidebar( 'give-forms-sidebar' );
}