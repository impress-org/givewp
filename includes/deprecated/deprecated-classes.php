<?php
/**
 * Handle renamed classes.
 *
 * @package Give
 */

/**
 * Give_DB_Donors Class
 *
 * This class is for interacting with the customers' database table.
 *
 * @since 1.0
 */
class Give_DB_Customers extends Give_DB {

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
		$deprecated_function_arr = [ 'get_customer_by', '' ];


		// If a property is renamed then it gets placed below.
		$donors_db = new Give_DB_Donors();

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
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
 * Instantiate old properties for backwards compatibility.
 *
 * @param $instance Give()
 */
function give_load_deprecated_properties( $instance ) {


}

add_action( 'give_init', 'give_load_deprecated_properties', 10, 1 );
