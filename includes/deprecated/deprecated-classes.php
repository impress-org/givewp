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
 * @return Give
 */
function give_load_deprecated_properties( $instance ) {

	// If a property is renamed then it gets placed below.
	$instance->customers     = new Give_DB_Customers();
	$instance->customer_meta = new Give_DB_Customer_Meta();

	return $instance;

}

add_action( 'give_init', 'give_load_deprecated_properties', 10, 1 );

/**
 * Give_DB_Customers Class (deprecated)
 *
 * This class is for interacting with the customers' database table.
 *
 * @since 1.0
 */
class Give_DB_Customers extends Give_DB_Donors {

	/**
	 * Give_DB_Customers constructor.
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_customers';
		$this->primary_key = 'id';
		$this->version     = '1.0';
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
		$deprecated_function_arr = array(
			'get_customer_by',
			'give_update_customer_email_on_user_update',
			'get_customers',
		);

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
				case 'get_customers':
					$args = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $this->get_donors( $args );
				case 'get_customer_by':
					$field    = ! empty( $arguments[0] ) ? $arguments[0] : 'id';
					$donor_id = ! empty( $arguments[1] ) ? $arguments[1] : 0;

					return $this->get_donor_by( $field, $donor_id );

				case 'give_update_customer_email_on_user_update':
					$user_id       = ! empty( $arguments[0] ) ? $arguments[0] : 0;
					$old_user_data = ! empty( $arguments[1] ) ? $arguments[1] : false;

					return $this->update_donor_email_on_user_update( $user_id, $old_user_data );
			}
		}
	}

}


/**
 * Give_Customer Class (Deprecated)
 *
 * @since 1.0
 */
class Give_Customer {

	/**
	 * Give_Customer constructor.
	 *
	 * @param bool $_id_or_email
	 * @param bool $by_user_id
	 */
	public function __construct( $_id_or_email = false, $by_user_id = false ) {

		$this->db = new Give_DB_Donors();

		if ( false === $_id_or_email || ( is_numeric( $_id_or_email ) && (int) $_id_or_email !== absint( $_id_or_email ) ) ) {
			return false;
		}

		$by_user_id = is_bool( $by_user_id ) ? $by_user_id : false;

		if ( is_numeric( $_id_or_email ) ) {
			$field = $by_user_id ? 'user_id' : 'id';
		} else {
			$field = 'email';
		}

		$donor = $this->db->get_donor_by( $field, $_id_or_email );

		if ( empty( $donor ) || ! is_object( $donor ) ) {
			return false;
		}

		$this->setup_customer( $donor );

	}


	/**
	 * Setup Donor
	 *
	 * Set donor variables.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param  object $donor The Donor Object.
	 *
	 * @return bool             If the setup was successful or not.
	 */
	private function setup_customer( $donor ) {

		if ( ! is_object( $donor ) ) {
			return false;
		}

		foreach ( $donor as $key => $value ) {

			switch ( $key ) {

				case 'notes':
					$this->$key = $this->get_notes();
					break;

				default:
					$this->$key = $value;
					break;

			}
		}

		// Get donor's all email including primary email.
		$this->emails = (array) $this->get_meta( 'additional_email', false );
		$this->emails = array( 'primary' => $this->email ) + $this->emails;

		// Donor ID and email are the only things that are necessary, make sure they exist.
		if ( ! empty( $this->id ) && ! empty( $this->email ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Responsible for setting properties from the Give_Donor class.
	 *
	 * @param $name
	 */
	public function __get( $name ) {

		$properties = get_object_vars( $this->customer );

		if ( array_key_exists( $name, $properties ) ) {
			return $properties[ $name ];
		}

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
		$deprecated_function_arr = array(
			'setup_customer',
			'decrease_purchase_count',
		);

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
				case 'setup_customer':
					$donor = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $this->customer->setup_donors( $donor );
				case 'decrease_purchase_count':
					$donor = ! empty( $arguments[0] ) ? $arguments[0] : array();

					return $this->customer->decrease_donation_count( $donor );
			}
		}
	}

}


/**
 * Give_DB_Customer_Meta Class (Deprecated)
 *
 * @since 1.0
 */
class Give_DB_Customer_Meta {

	/**
	 * Give_DB_Customer_Meta constructor.
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_customermeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';
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

	}

}
