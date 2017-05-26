<?php
/**
 * Handle renamed classes.
 *
 * @package Give
 */


/**
 * Instantiate old properties for backwards compatibility.
 *
 * @param $instance Give()
 */
function give_load_deprecated_properties( $instance ) {

	//If a property is renamed then it gets placed below.
	$instance->customers = new Give_DB_Donors();

}

add_action( 'give_init', 'give_load_deprecated_properties', 10, 1 );