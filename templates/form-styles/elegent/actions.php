<?php
function give_elegent_add_form_introduction_section() {
	include 'sections/introduction.php';
}
add_action( 'give_pre_form', 'give_elegent_add_form_introduction_section' );
