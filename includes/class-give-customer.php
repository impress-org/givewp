<?php
/**
 * Customer (Donor)
 *
 * @package     Give
 * @subpackage  Classes/Give_Customer
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Customer Class
 *
 * This class handles customers.
 *
 * @since 1.0
 */
class Give_Customer {

	/**
	 * The customer ID
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $id = 0;

	/**
	 * The customer's donation count
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $purchase_count = 0;

	/**
	 * The customer's lifetime value
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $purchase_value = 0;

	/**
	 * The customer's email
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $email;

	/**
	 * The customer's emails
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @var    array
	 */
	public $emails;

	/**
	 * The customer's name
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $name;

	/**
	 * The customer's creation date
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $date_created;

	/**
	 * The payment IDs associated with the customer
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    array
	 */
	public $payment_ids;

	/**
	 * The user ID associated with the customer
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $user_id;

	/**
	 * Customer Notes
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $notes;

	/**
	 * The Database Abstraction
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var    Give_DB_Customers
	 */
	protected $db;

	/**
	 * Class Constructor
	 *
	 * Set up the Give Customer Class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  bool $_id_or_email 
	 * @param  bool $by_user_id
	 */
	public function __construct( $_id_or_email = false, $by_user_id = false ) {

		$this->db = new Give_DB_Customers;

		if ( false === $_id_or_email || ( is_numeric( $_id_or_email ) && (int) $_id_or_email !== absint( $_id_or_email ) ) ) {
			return false;
		}

		$by_user_id = is_bool( $by_user_id ) ? $by_user_id : false;

		if ( is_numeric( $_id_or_email ) ) {
			$field = $by_user_id ? 'user_id' : 'id';
		} else {
			$field = 'email';
		}

		$customer = $this->db->get_customer_by( $field, $_id_or_email );

		if ( empty( $customer ) || ! is_object( $customer ) ) {
			return false;
		}

		$this->setup_customer( $customer );

	}

	/**
	 * Setup Customer
	 *
	 * Given the customer data, let's set the variables.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @param  object $customer The Customer Object.
	 *
	 * @return bool             If the setup was successful or not.
	 */
	private function setup_customer( $customer ) {

		if ( ! is_object( $customer ) ) {
			return false;
		}

		foreach ( $customer as $key => $value ) {

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
		$this->emails   = (array) $this->get_meta( 'additional_email', false );
		$this->emails = array( 'primary' => $this->email ) + $this->emails;

		// Customer ID and email are the only things that are necessary, make sure they exist.
		if ( ! empty( $this->id ) && ! empty( $this->email ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			/* translators: %s: property key */
			return new WP_Error( 'give-customer-invalid-property', sprintf( esc_html__( 'Can\'t get property %s.', 'give' ), $key ) );

		}

	}

	/**
	 * Creates a customer
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $data Array of attributes for a customer.
	 *
	 * @return bool|int    False if not a valid creation, customer ID if user is found or valid creation.
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'payment_ids' => ''
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		if ( empty( $args['email'] ) || ! is_email( $args['email'] ) ) {
			return false;
		}

		if ( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		/**
		 * Fires before creating customers.
		 *
		 * @since 1.0
		 *
		 * @param array $args Customer attributes.
		 */
		do_action( 'give_customer_pre_create', $args );

		$created = false;

		// The DB class 'add' implies an update if the customer being asked to be created already exists
		if ( $this->db->add( $data ) ) {

			// We've successfully added/updated the customer, reset the class vars with the new data
			$customer = $this->db->get_customer_by( 'email', $args['email'] );

			// Setup the customer data with the values from DB
			$this->setup_customer( $customer );

			$created = $this->id;
		}

		/**
		 * Fires after creating customers.
		 *
		 * @since 1.0
		 *
		 * @param bool|int $created False if not a valid creation,
		 *                          customer ID if user is found or valid creation.
		 * @param array    $args    Customer attributes.
		 */
		do_action( 'give_customer_post_create', $created, $args );

		return $created;

	}

	/**
	 * Update a customer record
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $data Array of data attributes for a customer (checked via whitelist).
	 *
	 * @return bool        If the update was successful or not.
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		/**
		 * Fires before updating customers.
		 *
		 * @since 1.0
		 *
		 * @param int   $customer_id Customer id.
		 * @param array $data        Customer attributes.
		 */
		do_action( 'give_customer_pre_update', $this->id, $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$customer = $this->db->get_customer_by( 'id', $this->id );
			$this->setup_customer( $customer );

			$updated = true;
		}

		/**
		 * Fires after updating customers.
		 *
		 * @since 1.0
		 *
		 * @param bool  $updated     If the update was successful or not.
		 * @param int   $customer_id Customer id.
		 * @param array $data        Customer attributes.
		 */
		do_action( 'give_customer_post_update', $updated, $this->id, $data );

		return $updated;
	}

	/**
	 * Attach Payment
	 *
	 * Attach payment to the customer then triggers increasing stats.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int  $payment_id   The payment ID to attach to the customer.
	 * @param  bool $update_stats For backwards compatibility, if we should increase the stats or not.
	 *
	 * @return bool            If the attachment was successfuly.
	 */
	public function attach_payment( $payment_id = 0, $update_stats = true ) {

		if ( empty( $payment_id ) ) {
			return false;
		}

		if ( empty( $this->payment_ids ) ) {

			$new_payment_ids = $payment_id;

		} else {

			$payment_ids = array_map( 'absint', explode( ',', $this->payment_ids ) );

			if ( in_array( $payment_id, $payment_ids ) ) {
				$update_stats = false;
			}

			$payment_ids[] = $payment_id;

			$new_payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		/**
		 * Fires before attaching payments to customers.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id  Payment id.
		 * @param int $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_attach_payment', $payment_id, $this->id );

		$payment_added = $this->update( array( 'payment_ids' => $new_payment_ids ) );

		if ( $payment_added ) {

			$this->payment_ids = $new_payment_ids;

			// We added this payment successfully, increment the stats
			if ( $update_stats ) {
				$payment_amount = give_get_payment_amount( $payment_id );

				if ( ! empty( $payment_amount ) ) {
					$this->increase_value( $payment_amount );
				}

				$this->increase_purchase_count();
			}

		}

		/**
		 * Fires after attaching payments to customers.
		 *
		 * @since 1.0
		 *
		 * @param bool $payment_added If the attachment was successfuly.
		 * @param int  $payment_id    Payment id.
		 * @param int  $customer_id   Customer id.
		 */
		do_action( 'give_customer_post_attach_payment', $payment_added, $payment_id, $this->id );

		return $payment_added;
	}

	/**
	 * Remove Payment
	 *
	 * Remove a payment from this customer, then triggers reducing stats.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int  $payment_id   The Payment ID to remove.
	 * @param  bool $update_stats For backwards compatibility, if we should increase the stats or not.
	 *
	 * @return boolean               If the removal was successful.
	 */
	public function remove_payment( $payment_id = 0, $update_stats = true ) {

		if ( empty( $payment_id ) ) {
			return false;
		}

		$payment = new Give_Payment( $payment_id );

		if ( 'publish' !== $payment->status && 'revoked' !== $payment->status ) {
			$update_stats = false;
		}

		$new_payment_ids = '';

		if ( ! empty( $this->payment_ids ) ) {

			$payment_ids = array_map( 'absint', explode( ',', $this->payment_ids ) );

			$pos = array_search( $payment_id, $payment_ids );
			if ( false === $pos ) {
				return false;
			}

			unset( $payment_ids[ $pos ] );
			$payment_ids = array_filter( $payment_ids );

			$new_payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		/**
		 * Fires before removing payments from customers.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id  Payment id.
		 * @param int $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_remove_payment', $payment_id, $this->id );

		$payment_removed = $this->update( array( 'payment_ids' => $new_payment_ids ) );

		if ( $payment_removed ) {

			$this->payment_ids = $new_payment_ids;

			if ( $update_stats ) {
				// We removed this payment successfully, decrement the stats
				$payment_amount = give_get_payment_amount( $payment_id );

				if ( ! empty( $payment_amount ) ) {
					$this->decrease_value( $payment_amount );
				}

				$this->decrease_purchase_count();
			}

		}

		/**
		 * Fires after removing payments from customers.
		 *
		 * @since 1.0
		 *
		 * @param bool $payment_removed If the removal was successfuly.
		 * @param int  $payment_id      Payment id.
		 * @param int  $customer_id     Customer id.
		 */
		do_action( 'give_customer_post_remove_payment', $payment_removed, $payment_id, $this->id );

		return $payment_removed;

	}

	/**
	 * Increase the donation count of a customer.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $count The number to increase by.
	 *
	 * @return int        The donation count.
	 */
	public function increase_purchase_count( $count = 1 ) {

		// Make sure it's numeric and not negative.
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->purchase_count + (int) $count;

		/**
		 * Fires before increasing customer donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $count       The number to increase by.
		 * @param int $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_increase_purchase_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		/**
		 * Fires after increasing customer donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $purchase_count Customer donation count.
		 * @param int $count          The number increased by.
		 * @param int $customer_id    Customer id.
		 */
		do_action( 'give_customer_post_increase_purchase_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
	}

	/**
	 * Decrease the customer donation count.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $count The amount to decrease by.
	 *
	 * @return mixed      If successful, the new count, otherwise false.
	 */
	public function decrease_purchase_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->purchase_count - (int) $count;

		if ( $new_total < 0 ) {
			$new_total = 0;
		}

		/**
		 * Fires before decreasing customer donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $count       The number to decrease by.
		 * @param int $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_decrease_purchase_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		/**
		 * Fires after decreasing customer donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $purchase_count Customer donation count.
		 * @param int $count          The number decreased by.
		 * @param int $customer_id    Customer id.
		 */
		do_action( 'give_customer_post_decrease_purchase_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
	}

	/**
	 * Increase the customer's lifetime value.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  float $value The value to increase by.
	 *
	 * @return mixed        If successful, the new value, otherwise false.
	 */
	public function increase_value( $value = 0.00 ) {

		$new_value = floatval( $this->purchase_value ) + $value;

		/**
		 * Fires before increasing customer lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $value       The value to increase by.
		 * @param int   $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_increase_value', $value, $this->id );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		/**
		 * Fires after increasing customer lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $purchase_value Customer lifetime value.
		 * @param float $value          The value increased by.
		 * @param int   $customer_id    Customer id.
		 */
		do_action( 'give_customer_post_increase_value', $this->purchase_value, $value, $this->id );

		return $this->purchase_value;
	}

	/**
	 * Decrease a customer's lifetime value.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  float $value The value to decrease by.
	 *
	 * @return mixed        If successful, the new value, otherwise false.
	 */
	public function decrease_value( $value = 0.00 ) {

		$new_value = floatval( $this->purchase_value ) - $value;

		if ( $new_value < 0 ) {
			$new_value = 0.00;
		}

		/**
		 * Fires before decreaseing customer lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $value       The value to decrease by.
		 * @param int   $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_decrease_value', $value, $this->id );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		/**
		 * Fires after decreaseing customer lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $purchase_value Customer lifetime value.
		 * @param float $value          The value decreased by.
		 * @param int   $customer_id    Customer id.
		 */
		do_action( 'give_customer_post_decrease_value', $this->purchase_value, $value, $this->id );

		return $this->purchase_value;
	}

	/**
	 * Decrease/Increase a customer's lifetime value.
     *
     * This function will update donation stat on basis of current amount and new amount donation difference.
     * Difference value can positive or negative. Negative value will decrease user donation stat while positive value increase donation stat.
     *
	 * @since  1.0
     * @access public
	 *
	 * @param  float $curr_amount Current Donation amount.
	 * @param  float $new_amount  New (changed) Donation amount.
	 *
	 * @return mixed              If successful, the new donation stat value, otherwise false.
	 */
	public function update_donation_value( $curr_amount, $new_amount ) {
        /**
         * Payment total difference value can be:
         *  zero   (in case amount not change)
         *  or -ve (in case amount decrease)
         *  or +ve (in case amount increase)
         */
        $payment_total_diff = $new_amount - $curr_amount;

        // We do not need to update donation stat if donation did not change.
        if( ! $payment_total_diff ) {
            return false;
        }


        if( $payment_total_diff > 0 ) {
            $this->increase_value( $payment_total_diff );
        } else {
            // Pass payment total difference as +ve value to decrease amount from user lifetime stat.
            $this->decrease_value( -$payment_total_diff );
        }

        return $this->purchase_value;
	}

	/**
	 * Get the parsed notes for a customer as an array.
	 *
	 * @since  1.0
     * @access public
	 *
	 * @param  int $length The number of notes to get.
	 * @param  int $paged  What note to start at.
	 *
	 * @return array       The notes requested.
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		$length = is_numeric( $length ) ? $length : 20;
		$offset = is_numeric( $paged ) && $paged != 1 ? ( ( absint( $paged ) - 1 ) * $length ) : 0;

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		$desired_notes = array_slice( $notes_array, $offset, $length );

		return $desired_notes;

	}

	/**
	 * Get the total number of notes we have after parsing.
	 *
	 * @since  1.0
     * @access public
	 *
	 * @return int The number of notes for the customer.
	 */
	public function get_notes_count() {

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		return count( $notes_array );

	}

	/**
	 * Add a note for the customer.
	 *
	 * @since  1.0
     * @access public
	 *
	 * @param  string $note   The note to add. Default is empty.
	 *
	 * @return string|boolean The new note if added successfully, false otherwise.
	 */
	public function add_note( $note = '' ) {

		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		$notes = $this->get_raw_notes();

		if ( empty( $notes ) ) {
			$notes = '';
		}

		$note_string = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' - ' . $note;
		$new_note    = apply_filters( 'give_customer_add_note_string', $note_string );
		$notes .= "\n\n" . $new_note;

		/**
		 * Fires before customer note added.
		 *
		 * @since 1.0
		 *
		 * @param string $new_note    New note to add.
		 * @param int    $customer_id Customer id.
		 */
		do_action( 'give_customer_pre_add_note', $new_note, $this->id );

		$updated = $this->update( array( 'notes' => $notes ) );

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}

		/**
		 * Fires after customer note added.
		 *
		 * @since 1.0
		 *
		 * @param array  $customer_notes Customer notes.
		 * @param string $new_note       New note added.
		 * @param int    $customer_id    Customer id.
		 */
		do_action( 'give_customer_post_add_note', $this->notes, $new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $new_note;

	}

	/**
	 * Get the notes column for the customer
	 *
	 * @since  1.0
     * @access private
	 *
	 * @return string The Notes for the customer, non-parsed.
	 */
	private function get_raw_notes() {

		$all_notes = $this->db->get_column( 'notes', $this->id );

		return $all_notes;

	}

	/**
	 * Retrieve customer meta field for a customer.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  string $meta_key The meta key to retrieve. Default is empty.
	 * @param  bool   $single   Whether to return a single value. Default is true.
	 *
	 * @return mixed            Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return Give()->customer_meta->get_meta( $this->id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a customer.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  string $meta_key   Metadata name. Default is empty.
	 * @param  mixed  $meta_value Metadata value.
	 * @param  bool   $unique     Optional. Whether the same key should not be added. Default is false.
	 *
	 * @return bool               False for failure. True for success.
	 */
	public function add_meta( $meta_key = '', $meta_value, $unique = false ) {
		return Give()->customer_meta->add_meta( $this->id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update customer meta field based on customer ID.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  string $meta_key   Metadata key. Default is empty.
	 * @param  mixed  $meta_value Metadata value.
	 * @param  mixed  $prev_value Optional. Previous value to check before removing. Default is empty.
	 *
	 * @return bool               False on failure, true if success.
	 */
	public function update_meta( $meta_key = '', $meta_value, $prev_value = '' ) {
		return Give()->customer_meta->update_meta( $this->id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a customer.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  string $meta_key   Metadata name. Default is empty.
	 * @param  mixed  $meta_value Optional. Metadata value. Default is empty.
	 *
	 * @return bool               False for failure. True for success.
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return Give()->customer_meta->delete_meta( $this->id, $meta_key, $meta_value );
	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  1.0
     * @access private
	 *
	 * @param  array $data The data to sanitize.
	 *
	 * @return array       The sanitized data, based off column defaults.
	 */
	private function sanitize_columns( $data ) {

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {

				case '%s':
					if ( 'email' == $key ) {
						$data[ $key ] = sanitize_email( $data[ $key ] );
					} elseif ( 'notes' == $key ) {
						$data[ $key ] = strip_tags( $data[ $key ] );
					} else {
						$data[ $key ] = sanitize_text_field( $data[ $key ] );
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || (int) $data[ $key ] !== absint( $data[ $key ] ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;

			}

		}

		return $data;
	}

	/**
	 * Attach an email to the donor
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @param  string $email   The email address to attach to the customer
	 * @param  bool   $primary Allows setting the email added as the primary
	 *
	 * @return bool            If the email was added successfully
	 */
	public function add_email( $email = '', $primary = false ) {
		if( ! is_email( $email ) ) {
			return false;
		}
		$existing = new Give_Customer( $email );

		if( $existing->id > 0 ) {
			// Email address already belongs to another customer
			return false;
		}

		if ( email_exists( $email ) ) {
			$user = get_user_by( 'email', $email );
			if ( $user->ID != $this->user_id ) {
				return false;
			}
		}

		do_action( 'give_donor_pre_add_email', $email, $this->id, $this );

		// Add is used to ensure duplicate emails are not added
		$ret = (bool) $this->add_meta( 'additional_email', $email );

		do_action( 'give_donor_post_add_email', $email, $this->id, $this );

		if ( $ret && true === $primary ) {
			$this->set_primary_email( $email );
		}

		return $ret;
	}

	/**
	 * Remove an email from the customer
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @param  string $email The email address to remove from the customer
	 *
	 * @return bool          If the email was removed successfully
	 */
	public function remove_email( $email = '' ) {
		if( ! is_email( $email ) ) {
			return false;
		}

		do_action( 'give_donor_pre_remove_email', $email, $this->id, $this );

		$ret = (bool) $this->delete_meta( 'additional_email', $email );

		do_action( 'give_donor_post_remove_email', $email, $this->id, $this );

		return $ret;
	}

	/**
	 * Set an email address as the customer's primary email
	 *
	 * This will move the customer's previous primary email to an additional email
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @param  string $new_primary_email The email address to remove from the customer
	 *
	 * @return bool                      If the email was set as primary successfully
	 */
	public function set_primary_email( $new_primary_email = '' ) {
		if( ! is_email( $new_primary_email ) ) {
			return false;
		}

		do_action( 'give_donor_pre_set_primary_email', $new_primary_email, $this->id, $this );

		$existing = new Give_Customer( $new_primary_email );

		if( $existing->id > 0 && (int) $existing->id !== (int) $this->id ) {
			// This email belongs to another customer
			return false;
		}

		$old_email = $this->email;

		// Update customer record with new email
		$update = $this->update( array( 'email' => $new_primary_email ) );

		// Remove new primary from list of additional emails
		$remove = $this->remove_email( $new_primary_email );

		// Add old email to additional emails list
		$add = $this->add_email( $old_email );

		$ret = $update && $remove && $add;

		if( $ret ) {
			$this->email = $new_primary_email;
		}

		do_action( 'give_donor_post_set_primary_email', $new_primary_email, $this->id, $this );

		return $ret;
	}
}
