<?php
/**
 * Donor
 *
 * @package     Give
 * @subpackage  Classes/Give_Donor
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donor Class
 *
 * This class handles customers.
 *
 * @since 1.0
 */
class Give_Donor {

	/**
	 * The donor ID
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $id = 0;

	/**
	 * The donor's donation count.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $purchase_count = 0;

	/**
	 * The donor's lifetime value.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $purchase_value = 0;

	/**
	 * The donor's email.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $email;

	/**
	 * The donor's emails.
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @var    array
	 */
	public $emails;

	/**
	 * The donor's name.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $name;

	/**
	 * The donor creation date.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $date_created;

	/**
	 * The payment IDs associated with the donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $payment_ids;

	/**
	 * The user ID associated with the donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $user_id;

	/**
	 * Donor notes saved by admins.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    array
	 */
	protected $notes = null;

	/**
	 * Donor address.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    array
	 */
	public $address = array();

	/**
	 * The Database Abstraction
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @var    Give_DB_Donors
	 */
	protected $db;

	/**
	 * Give_Donor constructor.
	 *
	 * @param int|bool $_id_or_email
	 * @param bool     $by_user_id
	 */
	public function __construct( $_id_or_email = false, $by_user_id = false ) {

		$this->db = Give()->donors;

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

		$this->setup_donor( $donor );

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
	private function setup_donor( $donor ) {

		if ( ! is_object( $donor ) ) {
			return false;
		}

		// Get cached donors.
		$donor_vars = Give_Cache::get_group( $donor->id, 'give-donors' );

		if ( is_null( $donor_vars ) ) {
			foreach ( $donor as $key => $value ) {

				switch ( $key ) {

					// @todo We will remove this statement when we will remove notes column from donor table
					// https://github.com/impress-org/give/issues/3632
					case 'notes':
						break;

					default:
						$this->$key = $value;
						break;

				}
			}

			// Get donor's all email including primary email.
			$this->emails = (array) $this->get_meta( 'additional_email', false );
			$this->emails = array( 'primary' => $this->email ) + $this->emails;

			$this->setup_address();

			Give_Cache::set_group( $donor->id, get_object_vars( $this ), 'give-donors' );
		} else {
			foreach ( $donor_vars as $donor_var => $value ) {
				$this->$donor_var = $value;
			}
		}

		// Donor ID and email are the only things that are necessary, make sure they exist.
		if ( ! empty( $this->id ) && ! empty( $this->email ) ) {
			return true;
		}

		return false;

	}


	/**
	 * Setup donor address.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function setup_address() {
		global $wpdb;
		$meta_type = Give()->donor_meta->meta_type;

		$addresses = $this->get_addresses_from_meta_cache();

		$addresses = ! empty( $addresses )
			? $addresses
			: $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT meta_key, meta_value FROM {$wpdb->donormeta}
				WHERE meta_key
				LIKE '%%%s%%'
				AND {$meta_type}_id=%d
				",
					'give_donor_address',
					$this->id
				),
				ARRAY_N
			);

		if ( empty( $addresses ) ) {
			return $this->address;
		}

		foreach ( $addresses as $address ) {
			$address[0] = str_replace( '_give_donor_address_', '', $address[0] );
			$address[0] = explode( '_', $address[0] );

			if ( 3 === count( $address[0] ) ) {
				$this->address[ $address[0][0] ][ $address[0][2] ][ $address[0][1] ] = $address[1];
			} else {
				$this->address[ $address[0][0] ][ $address[0][1] ] = $address[1];
			}
		}
	}


	/**
	 * Get addresses from meta cache
	 *
	 * @since 2.5.0
	 * @return array
	 */
	private function get_addresses_from_meta_cache() {
		$meta      = wp_cache_get( $this->id, 'donor_meta' );
		$addresses = array();

		if ( ! empty( $meta ) ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( false === strpos( $meta_key, 'give_donor_address' ) ) {
					continue;
				}

				$addresses[] = array( $meta_key, current( $meta_value ) );
			}
		}

		return $addresses;
	}

	/**
	 * Returns the saved address for a donor
	 *
	 * @access public
	 *
	 * @since  2.1.3
	 *
	 * @param array $args donor address.
	 *
	 * @return array The donor's address, if any
	 */
	public function get_donor_address( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'address_type' => 'billing',
			)
		);

		$default_address = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'country' => '',
			'zip'     => '',
		);

		// Backward compatibility.
		if ( ! give_has_upgrade_completed( 'v20_upgrades_user_address' ) ) {

			// Backward compatibility for user id param.
			return wp_parse_args( (array) get_user_meta( $this->user_id, '_give_user_address', true ), $default_address );

		}

		if ( ! $this->id || empty( $this->address ) || ! array_key_exists( $args['address_type'], $this->address ) ) {
			return $default_address;
		}

		switch ( true ) {
			case is_string( end( $this->address[ $args['address_type'] ] ) ):
				$address = wp_parse_args( $this->address[ $args['address_type'] ], $default_address );
				break;

			case is_array( end( $this->address[ $args['address_type'] ] ) ):
				$address = wp_parse_args( array_shift( $this->address[ $args['address_type'] ] ), $default_address );
				break;
		}

		return $address;
	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $key
	 *
	 * @return mixed|\WP_Error
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			/* translators: %s: property key */
			return new WP_Error( 'give-donor-invalid-property', sprintf( esc_html__( 'Can\'t get property %s.', 'give' ), $key ) );

		}

	}

	/**
	 * Creates a donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $data Array of attributes for a donor.
	 *
	 * @return bool|int    False if not a valid creation, donor ID if user is found or valid creation.
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 || empty( $data ) ) {
			return false;
		}

		$defaults = array(
			'payment_ids' => '',
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
		 * Fires before creating donors.
		 *
		 * @since 1.0
		 *
		 * @param array $args Donor attributes.
		 */
		do_action( 'give_donor_pre_create', $args );

		$created = false;

		// The DB class 'add' implies an update if the donor being asked to be created already exists
		if ( $this->db->add( $data ) ) {

			// We've successfully added/updated the donor, reset the class vars with the new data
			$donor = $this->db->get_donor_by( 'email', $args['email'] );

			// Setup the donor data with the values from DB
			$this->setup_donor( $donor );

			$created = $this->id;
		}

		/**
		 * Fires after creating donors.
		 *
		 * @since 1.0
		 *
		 * @param bool|int $created False if not a valid creation, donor ID if user is found or valid creation.
		 * @param array    $args    Customer attributes.
		 */
		do_action( 'give_donor_post_create', $created, $args );

		return $created;

	}

	/**
	 * Updates a donor record.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $data Array of data attributes for a donor (checked via whitelist).
	 *
	 * @return bool        If the update was successful or not.
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		/**
		 * Fires before updating donors.
		 *
		 * @since 1.0
		 *
		 * @param int   $donor_id Donor id.
		 * @param array $data     Donor attributes.
		 */
		do_action( 'give_donor_pre_update', $this->id, $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$donor = $this->db->get_donor_by( 'id', $this->id );

			$this->setup_donor( $donor );

			$updated = true;
		}

		/**
		 * Fires after updating donors.
		 *
		 * @since 1.0
		 *
		 * @param bool  $updated  If the update was successful or not.
		 * @param int   $donor_id Donor id.
		 * @param array $data     Donor attributes.
		 */
		do_action( 'give_donor_post_update', $updated, $this->id, $data );

		return $updated;
	}

	/**
	 * Attach Payment
	 *
	 * Attach payment to the donor then triggers increasing stats.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int  $payment_id   The payment ID to attach to the donor.
	 * @param  bool $update_stats For backwards compatibility, if we should increase the stats or not.
	 *
	 * @return bool            If the attachment was successfully.
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
		 * Fires before attaching payments to donors.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id Payment id.
		 * @param int $donor_id   Donor id.
		 */
		do_action( 'give_donor_pre_attach_payment', $payment_id, $this->id );

		$payment_added = $this->update( array( 'payment_ids' => $new_payment_ids ) );

		if ( $payment_added ) {

			$this->payment_ids = $new_payment_ids;

			// We added this payment successfully, increment the stats
			if ( $update_stats ) {
				$payment_amount = give_donation_amount( $payment_id, array( 'type' => 'stats' ) );

				if ( ! empty( $payment_amount ) ) {
					$this->increase_value( $payment_amount );
				}

				$this->increase_purchase_count();
			}
		}

		/**
		 * Fires after attaching payments to the donor.
		 *
		 * @since 1.0
		 *
		 * @param bool $payment_added If the attachment was successfully.
		 * @param int  $payment_id    Payment id.
		 * @param int  $donor_id      Donor id.
		 */
		do_action( 'give_donor_post_attach_payment', $payment_added, $payment_id, $this->id );

		return $payment_added;
	}

	/**
	 * Remove Payment
	 *
	 * Remove a payment from this donor, then triggers reducing stats.
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
		 * @param int $payment_id Payment id.
		 * @param int $donor_id   Customer id.
		 */
		do_action( 'give_donor_pre_remove_payment', $payment_id, $this->id );

		$payment_removed = $this->update( array( 'payment_ids' => $new_payment_ids ) );

		if ( $payment_removed ) {

			$this->payment_ids = $new_payment_ids;

			if ( $update_stats ) {
				// We removed this payment successfully, decrement the stats
				$payment_amount = give_donation_amount( $payment_id );

				if ( ! empty( $payment_amount ) ) {
					$this->decrease_value( $payment_amount );
				}

				$this->decrease_donation_count();
			}
		}

		/**
		 * Fires after removing payments from donors.
		 *
		 * @since 1.0
		 *
		 * @param bool $payment_removed If the removal was successfully.
		 * @param int  $payment_id      Payment id.
		 * @param int  $donor_id        Donor id.
		 */
		do_action( 'give_donor_post_remove_payment', $payment_removed, $payment_id, $this->id );

		return $payment_removed;

	}

	/**
	 * Increase the donation count of a donor.
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
		 * Fires before increasing the donor's donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $count    The number to increase by.
		 * @param int $donor_id Donor id.
		 */
		do_action( 'give_donor_pre_increase_donation_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		/**
		 * Fires after increasing the donor's donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $purchase_count Donor donation count.
		 * @param int $count          The number increased by.
		 * @param int $donor_id       Donor id.
		 */
		do_action( 'give_donor_post_increase_donation_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
	}

	/**
	 * Decrease the donor donation count.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $count The amount to decrease by.
	 *
	 * @return mixed      If successful, the new count, otherwise false.
	 */
	public function decrease_donation_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		$new_total = (int) $this->purchase_count - (int) $count;

		if ( $new_total < 0 ) {
			$new_total = 0;
		}

		/**
		 * Fires before decreasing the donor's donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $count    The number to decrease by.
		 * @param int $donor_id Customer id.
		 */
		do_action( 'give_donor_pre_decrease_donation_count', $count, $this->id );

		if ( $this->update( array( 'purchase_count' => $new_total ) ) ) {
			$this->purchase_count = $new_total;
		}

		/**
		 * Fires after decreasing the donor's donation count.
		 *
		 * @since 1.0
		 *
		 * @param int $purchase_count Donor's donation count.
		 * @param int $count          The number decreased by.
		 * @param int $donor_id       Donor id.
		 */
		do_action( 'give_donor_post_decrease_donation_count', $this->purchase_count, $count, $this->id );

		return $this->purchase_count;
	}

	/**
	 * Increase the donor's lifetime value.
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
		 * Fires before increasing donor lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $value    The value to increase by.
		 * @param int   $donor_id Customer id.
		 */
		do_action( 'give_donor_pre_increase_value', $value, $this->id );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		/**
		 * Fires after increasing donor lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $purchase_value Donor's lifetime value.
		 * @param float $value          The value increased by.
		 * @param int   $donor_id       Donor id.
		 */
		do_action( 'give_donor_post_increase_value', $this->purchase_value, $value, $this->id );

		return $this->purchase_value;
	}

	/**
	 * Decrease a donor's lifetime value.
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
		 * Fires before decreasing donor lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $value    The value to decrease by.
		 * @param int   $donor_id Donor id.
		 */
		do_action( 'give_donor_pre_decrease_value', $value, $this->id );

		if ( $this->update( array( 'purchase_value' => $new_value ) ) ) {
			$this->purchase_value = $new_value;
		}

		/**
		 * Fires after decreasing donor lifetime value.
		 *
		 * @since 1.0
		 *
		 * @param float $purchase_value Donor lifetime value.
		 * @param float $value          The value decreased by.
		 * @param int   $donor_id       Donor id.
		 */
		do_action( 'give_donor_post_decrease_value', $this->purchase_value, $value, $this->id );

		return $this->purchase_value;
	}

	/**
	 * Decrease/Increase a donor's lifetime value.
	 *
	 * This function will update donation stat on basis of current amount and new amount donation difference.
	 * Difference value can positive or negative. Negative value will decrease user donation stat while positive value
	 * increase donation stat.
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
		if ( ! $payment_total_diff ) {
			return false;
		}

		if ( $payment_total_diff > 0 ) {
			$this->increase_value( $payment_total_diff );
		} else {
			// Pass payment total difference as +ve value to decrease amount from user lifetime stat.
			$this->decrease_value( - $payment_total_diff );
		}

		return $this->purchase_value;
	}

	/**
	 * Get the parsed notes for a donor as an array.
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
	 * @return int The number of notes for the donor.
	 */
	public function get_notes_count() {

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		return count( $notes_array );

	}

	/**
	 * Get the total donation amount.
	 *
	 * @since 1.8.17
	 *
	 * @param array $args Pass any additional data.
	 *
	 * @return string|float
	 */
	public function get_total_donation_amount( $args = array() ) {

		/**
		 * Filter total donation amount.
		 *
		 * @since 1.8.17
		 *
		 * @param string|float $purchase_value Donor Purchase value.
		 * @param integer      $donor_id       Donor ID.
		 * @param array        $args           Pass additional data.
		 */
		return apply_filters( 'give_get_total_donation_amount', $this->purchase_value, $this->id, $args );
	}

	/**
	 * Add a note for the donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $note The note to add. Default is empty.
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

		// Backward compatibility.
		$note_string        = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' - ' . $note;
		$formatted_new_note = apply_filters( 'give_customer_add_note_string', $note_string );
		$notes             .= "\n\n" . $formatted_new_note;

		/**
		 * Fires before donor note is added.
		 *
		 * @since 1.0
		 *
		 * @param string $formatted_new_note Formatted new note to add.
		 * @param int    $donor_id           Donor id.
		 */
		do_action( 'give_donor_pre_add_note', $formatted_new_note, $this->id );

		if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			// Backward compatibility.
			$updated = $this->update( array( 'notes' => $notes ) );
		} else {
			$updated = Give()->comment->db->add(
				array(
					'comment_content' => $note,
					'user_id'         => get_current_user_id(),
					'comment_parent'  => $this->id,
					'comment_type'    => 'donor',
				)
			);
		}

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}

		/**
		 * Fires after donor note added.
		 *
		 * @since 1.0
		 *
		 * @param array  $donor_notes        Donor notes.
		 * @param string $formatted_new_note Formatted new note added.
		 * @param int    $donor_id           Donor id.
		 */
		do_action( 'give_donor_post_add_note', $this->notes, $formatted_new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $formatted_new_note;
	}

	/**
	 * Get the notes column for the donor
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return string The Notes for the donor, non-parsed.
	 */
	private function get_raw_notes() {
		$all_notes = '';
		$comments  = Give()->comment->db->get_results_by( array( 'comment_parent' => $this->id ) );

		// Generate notes output as we are doing before 2.3.0.
		if ( ! empty( $comments ) ) {
			/* @var stdClass $comment */
			foreach ( $comments  as $comment ) {
				$all_notes .= date_i18n( 'F j, Y H:i:s', strtotime( $comment->comment_date ) ) . " - {$comment->comment_content}\n\n";
			}
		}

		// Backward compatibility.
		if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			$all_notes = $this->db->get_column( 'notes', $this->id );
		}

		return $all_notes;

	}

	/**
	 * Retrieve a meta field for a donor.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  string $meta_key The meta key to retrieve. Default is empty.
	 * @param  bool   $single   Whether to return a single value. Default is true.
	 *
	 * @return mixed            Will be an array if $single is false. Will be value of meta data field if $single is
	 *                          true.
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return Give()->donor_meta->get_meta( $this->id, $meta_key, $single );
	}

	/**
	 * Add a meta data field to a donor.
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
		return Give()->donor_meta->add_meta( $this->id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update a meta field based on donor ID.
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
		return Give()->donor_meta->update_meta( $this->id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a donor.
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
		return Give()->donor_meta->delete_meta( $this->id, $meta_key, $meta_value );
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
	 * @param  string $email   The email address to attach to the donor
	 * @param  bool   $primary Allows setting the email added as the primary
	 *
	 * @return bool            If the email was added successfully
	 */
	public function add_email( $email = '', $primary = false ) {
		if ( ! is_email( $email ) ) {
			return false;
		}
		$existing = new Give_Donor( $email );

		if ( $existing->id > 0 ) {
			// Email address already belongs to another donor
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
	 * Remove an email from the donor.
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @param  string $email The email address to remove from the donor.
	 *
	 * @return bool          If the email was removed successfully.
	 */
	public function remove_email( $email = '' ) {
		if ( ! is_email( $email ) ) {
			return false;
		}

		do_action( 'give_donor_pre_remove_email', $email, $this->id, $this );

		$ret = (bool) $this->delete_meta( 'additional_email', $email );

		do_action( 'give_donor_post_remove_email', $email, $this->id, $this );

		return $ret;
	}

	/**
	 * Set an email address as the donor's primary email.
	 *
	 * This will move the donor's previous primary email to an additional email.
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @param  string $new_primary_email The email address to remove from the donor.
	 *
	 * @return bool                      If the email was set as primary successfully.
	 */
	public function set_primary_email( $new_primary_email = '' ) {
		if ( ! is_email( $new_primary_email ) ) {
			return false;
		}

		do_action( 'give_donor_pre_set_primary_email', $new_primary_email, $this->id, $this );

		$existing = new Give_Donor( $new_primary_email );

		if ( $existing->id > 0 && (int) $existing->id !== (int) $this->id ) {
			// This email belongs to another donor.
			return false;
		}

		$old_email = $this->email;

		// Update donor record with new email.
		$update = $this->update( array( 'email' => $new_primary_email ) );

		// Remove new primary from list of additional emails.
		$remove = $this->remove_email( $new_primary_email );

		// Add old email to additional emails list.
		$add = $this->add_email( $old_email );

		$ret = $update && $remove && $add;

		if ( $ret ) {
			$this->email = $new_primary_email;
		}

		do_action( 'give_donor_post_set_primary_email', $new_primary_email, $this->id, $this );

		return $ret;
	}

	/**
	 * Check if address valid or not.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param $address
	 *
	 * @return bool
	 */
	private function is_valid_address( $address ) {
		$is_valid_address = true;

		// Address ready to process even if only one value set.
		foreach ( $address as $address_type => $value ) {
			// @todo: Handle state field validation on basis of country.
			if ( in_array( $address_type, array( 'line2', 'state' ) ) ) {
				continue;
			}

			if ( empty( $value ) ) {
				$is_valid_address = false;
				break;
			}
		}

		return $is_valid_address;
	}

	/**
	 * Add donor address
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $address_type
	 * @param array  $address {
	 *
	 * @type string  $address2
	 * @type string city
	 * @type string zip
	 * @type string state
	 * @type string country
	 * }
	 *
	 * @return bool
	 */
	public function add_address( $address_type, $address ) {
		// Bailout.
		if ( empty( $address_type ) || ! $this->is_valid_address( $address ) || ! $this->id ) {
			return false;
		}

		// Check if multiple address exist or not and set params.
		$multi_address_id = null;
		if ( $is_multi_address = ( false !== strpos( $address_type, '[]' ) ) ) {
			$address_type = $is_multi_address ? str_replace( '[]', '', $address_type ) : $address_type;
		} elseif ( $is_multi_address = ( false !== strpos( $address_type, '_' ) ) ) {
			$exploded_address_type = explode( '_', $address_type );
			$multi_address_id      = $is_multi_address ? array_pop( $exploded_address_type ) : $address_type;

			$address_type = $is_multi_address ? array_shift( $exploded_address_type ) : $address_type;
		}

		// Bailout: do not save duplicate orders
		if ( $this->does_address_exist( $address_type, $address ) ) {
			return false;
		}

		// Set default address.
		$address = wp_parse_args(
			$address,
			array(
				'line1'   => '',
				'line2'   => '',
				'city'    => '',
				'state'   => '',
				'country' => '',
				'zip'     => '',
			)
		);

		// Set meta key prefix.
		global $wpdb;
		$meta_key_prefix = "_give_donor_address_{$address_type}_{address_name}";
		$meta_type       = Give()->donor_meta->meta_type;

		if ( $is_multi_address ) {
			if ( is_null( $multi_address_id ) ) {
				// Get latest address key to set multi address id.
				$multi_address_id = $wpdb->get_var(
					$wpdb->prepare(
						"
						SELECT meta_key FROM {$wpdb->donormeta}
						WHERE meta_key
						LIKE '%%%s%%'
						AND {$meta_type}_id=%d
						ORDER BY meta_id DESC
						LIMIT 1
						",
						"_give_donor_address_{$address_type}_line1",
						$this->id
					)
				);

				if ( ! empty( $multi_address_id ) ) {
					$multi_address_id = absint( substr( strrchr( $multi_address_id, '_' ), 1 ) );
					$multi_address_id ++;
				} else {
					$multi_address_id = 0;
				}
			}

			$meta_key_prefix = "_give_donor_address_{$address_type}_{address_name}_{$multi_address_id}";
		}

		// Save donor address.
		foreach ( $address as $type => $value ) {
			$meta_key = str_replace( '{address_name}', $type, $meta_key_prefix );
			Give()->donor_meta->update_meta( $this->id, $meta_key, $value );
		}

		$this->setup_address();

		return true;
	}

	/**
	 * Remove donor address
	 *
	 * @since  2.0
	 * @access public
	 * @global wpdb  $wpdb
	 *
	 * @param string $address_id
	 *
	 * @return bool
	 */
	public function remove_address( $address_id ) {
		global $wpdb;

		// Get address type.
		$is_multi_address = false !== strpos( $address_id, '_' ) ? true : false;

		$address_key_arr = explode( '_', $address_id );

		$address_type  = false !== strpos( $address_id, '_' ) ? array_shift( $address_key_arr ) : $address_id;
		$address_count = false !== strpos( $address_id, '_' ) ? array_pop( $address_key_arr ) : null;

		// Set meta key prefix.
		$meta_key_prefix = "_give_donor_address_{$address_type}_%";
		if ( $is_multi_address && is_numeric( $address_count ) ) {
			$meta_key_prefix .= "_{$address_count}";
		}

		$meta_type = Give()->donor_meta->meta_type;

		// Process query.
		$row_affected = $wpdb->query(
			$wpdb->prepare(
				"
				DELETE FROM {$wpdb->donormeta}
				WHERE meta_key
				LIKE '%s'
				AND {$meta_type}_id=%d
				",
				$meta_key_prefix,
				$this->id
			)
		);

		// Delete cache.
		Give_Cache::delete_group( $this->id, 'give-donors' );
		wp_cache_delete( $this->id, "{$meta_type}_meta" );

		$this->setup_address();

		return (bool) $row_affected;
	}

	/**
	 * Update donor address
	 *
	 * @since  2.0
	 * @access public
	 * @global wpdb  $wpdb
	 *
	 * @param string $address_id
	 * @param array  $address
	 *
	 * @return bool
	 */
	public function update_address( $address_id, $address ) {
		global $wpdb;

		// Get address type.
		$is_multi_address    = false !== strpos( $address_id, '_' ) ? true : false;
		$exploded_address_id = explode( '_', $address_id );

		$address_type = false !== strpos( $address_id, '_' ) ? array_shift( $exploded_address_id ) : $address_id;

		$address_count = false !== strpos( $address_id, '_' ) ? array_pop( $exploded_address_id ) : null;

		// Set meta key prefix.
		$meta_key_prefix = "_give_donor_address_{$address_type}_%";
		if ( $is_multi_address && is_numeric( $address_count ) ) {
			$meta_key_prefix .= "_{$address_count}";
		}

		$meta_type = Give()->donor_meta->meta_type;

		// Process query.
		$row_affected = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT meta_key FROM {$wpdb->donormeta}
				WHERE meta_key
				LIKE '%s'
				AND {$meta_type}_id=%d
				",
				$meta_key_prefix,
				$this->id
			)
		);

		// Return result.
		if ( ! count( $row_affected ) ) {
			return false;
		}

		// Update address.
		if ( ! $this->add_address( $address_id, $address ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Check if donor already has current address
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string $current_address_type
	 * @param array  $current_address
	 *
	 * @return bool|null
	 */
	public function does_address_exist( $current_address_type, $current_address ) {
		$status = false;

		// Bailout.
		if ( empty( $current_address_type ) || empty( $current_address ) ) {
			return null;
		}

		// Bailout.
		if ( empty( $this->address ) || empty( $this->address[ $current_address_type ] ) ) {
			return $status;
		}

		// Get address.
		$address = $this->address[ $current_address_type ];

		switch ( true ) {

			// Single address.
			case is_string( end( $address ) ):
				$status = $this->is_address_match( $current_address, $address );
				break;

			// Multi address.
			case is_array( end( $address ) ):
				// Compare address.
				foreach ( $address as $saved_address ) {
					if ( empty( $saved_address ) ) {
						continue;
					}

					// Exit loop immediately if address exist.
					if ( $status = $this->is_address_match( $current_address, $saved_address ) ) {
						break;
					}
				}
				break;
		}

		return $status;
	}

	/**
	 * Compare address.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param array $address_1
	 * @param array $address_2
	 *
	 * @return bool
	 */
	private function is_address_match( $address_1, $address_2 ) {
		$result = array_diff_assoc( $address_1, $address_2 );

		return empty( $result );
	}

	/**
	 * Split donor name into first name and last name
	 *
	 * @param   int $id Donor ID
	 *
	 * @since   2.0
	 * @return  object
	 */
	public function split_donor_name( $id ) {
		$first_name = $last_name = '';
		$donor      = new Give_Donor( $id );

		$split_donor_name = explode( ' ', $donor->name, 2 );

		// Check for existence of first name after split of donor name.
		if ( is_array( $split_donor_name ) && ! empty( $split_donor_name[0] ) ) {
			$first_name = $split_donor_name[0];
		}

		// Check for existence of last name after split of donor name.
		if ( is_array( $split_donor_name ) && ! empty( $split_donor_name[1] ) ) {
			$last_name = $split_donor_name[1];
		}

		return (object) array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
		);
	}

	/**
	 * Retrieves first name of donor with backward compatibility
	 *
	 * @since   2.0
	 * @return  string
	 */
	public function get_first_name() {
		$first_name = $this->get_meta( '_give_donor_first_name' );
		if ( ! $first_name ) {
			$first_name = $this->split_donor_name( $this->id )->first_name;
		}

		return $first_name;
	}

	/**
	 * Retrieves last name of donor with backward compatibility
	 *
	 * @since   2.0
	 * @return  string
	 */
	public function get_last_name() {
		$first_name = $this->get_meta( '_give_donor_first_name' );
		$last_name  = $this->get_meta( '_give_donor_last_name' );

		// This condition will prevent unnecessary splitting of donor name to fetch last name.
		if ( ! $first_name && ! $last_name ) {
			$last_name = $this->split_donor_name( $this->id )->last_name;
		}

		return ( $last_name ) ? $last_name : '';
	}

	/**
	 * Retrieves company name of donor
	 *
	 * @since   2.1
	 *
	 * @return  string $company_name Donor Company Name
	 */
	public function get_company_name() {
		$company_name = $this->get_meta( '_give_donor_company' );

		return $company_name;
	}

	/**
	 * Retrieves last donation for the donor.
	 *
	 * @since   2.1
	 *
	 * @return  string $company_name Donor Company Name
	 */
	public function get_last_donation() {

		$payments = array_unique( array_values( explode( ',', $this->payment_ids ) ) );

		return end( $payments );

	}

	/**
	 * Retrieves last donation for the donor.
	 *
	 * @since   2.1
	 *
	 * @param bool $formatted Whether to return with the date format or not.
	 *
	 * @return string The date of the last donation.
	 */
	public function get_last_donation_date( $formatted = false ) {
		$completed_data = '';

		// Return if donation id is invalid.
		if ( ! ( $last_donation = absint( $this->get_last_donation() ) ) ) {
			return $completed_data;
		}

		$completed_data = give_get_payment_completed_date( $last_donation );

		if ( $formatted ) {
			return date_i18n( give_date_format(), strtotime( $completed_data ) );
		}

		return $completed_data;

	}

	/**
	 * Retrieves a donor's initials (first name and last name).
	 *
	 * @since   2.1
	 *
	 * @return string The donor's two initials (no middle).
	 */
	public function get_donor_initals() {
		/**
		 * Filter the donor name initials
		 *
		 * @since 2.1.0
		 */
		return apply_filters(
			'get_donor_initals',
			give_get_name_initial(
				array(
					'firstname' => $this->get_first_name(),
					'lastname'  => $this->get_last_name(),
				)
			)
		);

	}

}
