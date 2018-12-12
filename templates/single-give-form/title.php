<?php
/**
 * Single Give Form Title
 * 
 * Displays the main title for the single donation form - Override this template by copying it to yourtheme/give/single-give-form/title.php
 * 
 * @package     Give
 * @subpackage  templates/single-give-form
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
} ?>
<h1 itemprop="name" class="give-form-title entry-title"><?php the_title(); ?></h1>
