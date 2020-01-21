<?php
remove_action( 'give_pre_form', 'give_show_goal_progress', 10 );

add_action( 'give_pre_form', 'give_elegent_add_form_introduction_section' );


function give_elegent_add_form_introduction_section() {
	include 'sections/introduction.php';
}
