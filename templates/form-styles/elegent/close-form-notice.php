<?php
$form_title = ! is_singular( 'give_forms' )
	? apply_filters( 'give_form_title', '<h2 class="give-form-title">' . get_the_title( $form->ID ) . '</h2>' )
	: '';

// Get Goal thank you message.
$goal_achieved_message = get_post_meta( $form->ID, '_give_form_goal_achieved_message', true );
$goal_achieved_message = ! empty( $goal_achieved_message ) ? $form_title . apply_filters( 'the_content', $goal_achieved_message ) : '';

// Print thank you message.
echo apply_filters( 'give_goal_closed_output', $goal_achieved_message, $form->ID, $form );
