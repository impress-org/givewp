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
 *
 * @return $instance class
 */
function give_load_deprecated_properties( $instance ) {

	// If a property is renamed then it gets placed below.
	$instance->customers = new Give_DB_Customers();

	return $instance;

}
// add_action( 'give_init', 'give_load_deprecated_properties', 10, 1 );
/**
 * Give_DB_Customers Class (deprecated)
 *
 * This class is for interacting with the customers' database table.
 *
 * @since 1.0
 */
class Give_DB_Customers extends Give_DB {

	/**
	 * Give_DB_Customers constructor.
	 */
	public function __construct() {
	}

	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 1.8.8
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		$deprecated_function_arr = array( 'get_customer_by', 'give_update_donor_email_on_user_update', 'get_customers' );

		// If a property is renamed then it gets placed below.
		$donors_db = new Give_DB_Donors();

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
				case 'get_customers':
					$args    = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $donors_db->get_donors( $args );
				case 'get_customer_by':
					$field    = ! empty( $arguments[0] ) ? $arguments[0] : 'id';
					$donor_id = ! empty( $arguments[1] ) ? $arguments[1] : 0;

					return $donors_db->get_donor_by( $field, $donor_id );
				case 'give_update_donor_email_on_user_update':
					$user_id       = ! empty( $arguments[0] ) ? $arguments[0] : 0;
					$old_user_data = ! empty( $arguments[1] ) ? $arguments[1] : '';

					return $donors_db->get_donor_by( $user_id, $old_user_data );
			}
		}
	}

}


/**
 * Give_Customers Class (Deprecated)
 *
 * @since 1.0
 */
class Give_Customer {

	/**
	 * Give_Customer constructor.
	 */
	public function __construct() {
	}


	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 1.8.8
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		$deprecated_function_arr = array('setup_customer');

		// If a property is renamed then it gets placed below.
		$customer = new Give_Donor();

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
				case 'setup_customer':
					$donor    = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $customer->setup_donors( $donor );
			}
		}
	}

}
