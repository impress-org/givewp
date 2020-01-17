<?php
/**
 * Show form title:
 * 1. if admin set form display_style to button or modal
 */
$form_title = apply_filters( 'give_form_title', '<h2 class="give-form-title">' . get_the_title( $form->ID ) . '</h2>' );

if ( true === $args['show_title'] ) {
	echo $form_title;
}
