<?php
/**
 * Payments
 *
 * @package     Give
 * @subpackage  Classes/Give_Payment
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Payment Class
 *
 * This class is for working with payments in Give.
 *
 * @property int        $ID
 * @property bool       $new
 * @property string     $number
 * @property string     $mode
 * @property string     $import
 * @property string     $key
 * @property string     $form_title
 * @property string|int $form_id
 * @property string|int $price_id
 * @property string|int $total
 * @property string|int $subtotal
 * @property string|int $fees
 * @property string|int $fees_total
 * @property string     $post_status
 * @property string     $date
 * @property string     $post_date
 * @property string     $status
 * @property string     $email
 * @property array      $payment_meta
 * @property string     $customer_id
 * @property string     $donor_id
 * @property string     $completed_date
 * @property string     $currency
 * @property string     $ip
 * @property array      $user_info
 * @property string     $gateway
 * @property string     $user_id
 * @property string     $title_prefix
 * @property string     $first_name
 * @property string     $last_name
 * @property string     $parent_payment
 * @property string     $transaction_id
 * @property string     $old_status
 *
 * @since 1.5
 */
final class Give_Payment {

	/**
	 * The Payment ID.
	 *
	 * @since  1.5
	 *
	 * @var    int
	 */
	public $ID = 0;

	/**
	 * Protected non-read $_ID.
	 *
	 * @var int
	 */
	protected $_ID = 0;

	/**
	 * Identify if the payment is a new one or existing.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    boolean
	 */
	protected $new = false;

	/**
	 * The Payment number (for use with sequential payments).
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $number = '';

	/**
	 * The Gateway mode the payment was made in.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $mode = 'live';

	/**
	 * Is donations is Import or not.
	 *
	 * @since  1.8.13
	 * @access protected
	 *
	 * @var    bool
	 */
	protected $import = false;

	/**
	 * The unique donation payment key.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $key = '';

	/**
	 * The Donation Form Title
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $form_title = 0;

	/**
	 * The Donation Form ID
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $form_id = 0;

	/**
	 * The Donation Form Price ID
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string|int
	 */
	protected $price_id = 0;

	/**
	 * The total amount of the donation payment.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    float
	 */
	protected $total = 0.00;

	/**
	 * The Subtotal fo the payment.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    float
	 */
	protected $subtotal = 0;

	/**
	 * The date the payment was created
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $date = '';

	/**
	 * The date the payment post was created.
	 *
	 * @var string
	 */
	protected $post_date = '';

	/**
	 * The date the payment was marked as 'complete'.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $completed_date = '';

	/**
	 * The status of the donation payment.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $status = 'pending';

	/**
	 * Donation Status.
	 *
	 * @var string
	 */
	protected $post_status = 'pending'; // Same as $status but here for backwards compat.

	/**
	 * When updating, the old status prior to the change
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $old_status = '';

	/**
	 * The display name of the current payment status.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $status_nicename = '';

	/**
	 * The donor ID that made the payment.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    integer
	 */
	protected $customer_id = null;

	/**
	 * The Donor ID (if logged in) that made the payment
	 *
	 * @since  1.8.13
	 * @access protected
	 *
	 * @var    integer
	 */
	protected $donor_id = 0;

	/**
	 * The User ID (if logged in) that made the payment
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    integer
	 */
	protected $user_id = 0;

	/**
	 * The Title Prefix/Salutation of the Donor.
	 *
	 * @since 2.2
	 *
	 * @var string
	 */
	protected $title_prefix = '';

	/**
	 * The first name of the payee
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $first_name = '';

	/**
	 * The last name of the payee
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $last_name = '';

	/**
	 * The email used for the payment
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $email = '';

	/**
	 * Legacy (not to be accessed) array of user information
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @var    array
	 */
	private $user_info = array();

	/**
	 * Legacy (not to be accessed) payment meta array
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @var    array
	 */
	private $payment_meta = array();

	/**
	 * The physical address used for the payment if provided
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    array
	 */
	protected $address = array();

	/**
	 * The transaction ID returned by the gateway
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $transaction_id = '';

	/**
	 * IP Address payment was made from
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $ip = '';

	/**
	 * The gateway used to process the payment
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $gateway = '';

	/**
	 * The the payment was made with
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    string
	 */
	protected $currency = '';

	/**
	 * Array of items that have changed since the last save() was run.
	 * This is for internal use, to allow fewer update_payment_meta calls to be run.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @var    array
	 */
	private $pending;

	/**
	 * The parent payment (if applicable)
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    integer
	 */
	protected $parent_payment = 0;

	/**
	 * Setup the Give Payments class
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  int|bool $payment_id A given payment.
	 *
	 * @return mixed void|false
	 */
	public function __construct( $payment_id = false ) {

		if ( empty( $payment_id ) ) {
			return false;
		}

		$this->setup_payment( $payment_id );
	}

	/**
	 * Magic GET function.
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $key The property.
	 *
	 * @return mixed        The value.
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			$value = call_user_func( array( $this, 'get_' . $key ) );

		} else {

			$value = $this->$key;

		}

		return $value;
	}

	/**
	 * Magic SET function
	 *
	 * Sets up the pending array for the save method
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $key   The property name.
	 * @param  mixed  $value The value of the property.
	 */
	public function __set( $key, $value ) {
		$ignore = array( '_ID' );

		if ( 'status' === $key ) {
			$this->old_status = $this->status;
		}

		if ( ! in_array( $key, $ignore ) ) {
			$this->pending[ $key ] = $value;
		}

		if ( '_ID' !== $key ) {
			$this->$key = $value;
		}
	}

	/**
	 * Magic ISSET function, which allows empty checks on protected elements
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $name The attribute to get.
	 *
	 * @return boolean|null       If the item is set or not
	 */
	public function __isset( $name ) {
		if ( property_exists( $this, $name ) ) {
			return false === empty( $this->$name );
		} else {
			return null;
		}
	}

	/**
	 * Setup payment properties
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  int $payment_id The payment ID.
	 *
	 * @return bool            If the setup was successful or not
	 */
	private function setup_payment( $payment_id ) {
		$this->pending = array();

		if ( empty( $payment_id ) ) {
			return false;
		}

		$payment = get_post( absint( $payment_id ) );

		if ( ! $payment || is_wp_error( $payment ) ) {
			return false;
		}

		if ( 'give_payment' !== $payment->post_type ) {
			return false;
		}

		Give_Payments_Query::update_meta_cache( array( $payment_id ) );

		/**
		 * Fires before payment setup.
		 *
		 * Allow extensions to perform actions before the payment is loaded.
		 *
		 * @since 1.5
		 *
		 * @param Give_Payment $this       Payment object.
		 * @param int          $payment_id The ID of the payment.
		 */
		do_action( 'give_pre_setup_payment', $this, $payment_id );

		// Get payment from cache.
		$donation_vars = Give_Cache::get_group( $payment_id, 'give-donations' );

		if ( is_null( $donation_vars ) ) {
			// Primary Identifier.
			$this->ID = absint( $payment_id );

			// Protected ID that can never be changed.
			$this->_ID = absint( $payment_id );

			// We have a payment, get the generic payment_meta item to reduce calls to it.
			$this->payment_meta = $this->get_meta();

			// Status and Dates.
			$this->date           = $payment->post_date;
			$this->post_date      = $payment->post_date;
			$this->completed_date = $this->setup_completed_date();
			$this->status         = $payment->post_status;
			$this->post_status    = $this->status;
			$this->mode           = $this->setup_mode();
			$this->import         = $this->setup_import();
			$this->parent_payment = $payment->post_parent;

			$all_payment_statuses  = give_get_payment_statuses();
			$this->status_nicename = array_key_exists( $this->status, $all_payment_statuses ) ? $all_payment_statuses[ $this->status ] : ucfirst( $this->status );

			// Currency Based.
			$this->total    = $this->setup_total();
			$this->subtotal = $this->setup_subtotal();
			$this->currency = $this->setup_currency();

			// Gateway based.
			$this->gateway        = $this->setup_gateway();
			$this->transaction_id = $this->setup_transaction_id();

			// User based.
			$this->ip           = $this->setup_ip();
			$this->customer_id  = $this->setup_donor_id(); // Backward compatibility.
			$this->donor_id     = $this->setup_donor_id();
			$this->user_id      = $this->setup_user_id();
			$this->email        = $this->setup_email();
			$this->user_info    = $this->setup_user_info();
			$this->address      = $this->setup_address();
			$this->first_name   = $this->user_info['first_name'];
			$this->last_name    = $this->user_info['last_name'];
			$this->title_prefix = isset( $this->user_info['title'] ) ? $this->user_info['title'] : '';

			// Other Identifiers.
			$this->form_title = $this->setup_form_title();
			$this->form_id    = $this->setup_form_id();
			$this->price_id   = $this->setup_price_id();
			$this->key        = $this->setup_payment_key();
			$this->number     = $this->setup_payment_number();

			Give_Cache::set_group( $this->ID, get_object_vars( $this ), 'give-donations' );
		} else {

			foreach ( $donation_vars as $donation_var => $value ) {
				$this->$donation_var = $value;
			}
		} // End if().

		/**
		 * Fires after payment setup.
		 *
		 * Allow extensions to add items to this object via hook.
		 *
		 * @since 1.5
		 *
		 * @param Give_Payment $this       Payment object.
		 * @param int          $payment_id The ID of the payment.
		 */
		do_action( 'give_setup_payment', $this, $payment_id );

		return true;
	}


	/**
	 * Payment class object is storing various meta value in object parameter.
	 * So if user is updating payment meta but not updating payment object, then payment meta values will not reflect/changes on payment meta automatically
	 * and you can still access payment meta old value in any old payment object ( previously created ) which can cause to show or save wrong payment data.
	 * To prevent that user can use this function after updating any payment meta value ( in bulk or single update ).
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  int $payment_id Payment ID.
	 *
	 * @return void
	 */
	public function update_payment_setup( $payment_id ) {
		// Delete cache.
		Give_Cache::delete_group( $this->ID, 'give-donations' );

		$this->setup_payment( $payment_id );
	}

	/**
	 * Create the base of a payment.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int|bool False on failure, the payment ID on success.
	 */
	private function insert_payment() {

		// Construct the payment title.
		$payment_title = '';
		if ( ! empty( $this->first_name ) && ! empty( $this->last_name ) ) {
			$payment_title = $this->first_name . ' ' . $this->last_name;
		} elseif ( ! empty( $this->first_name ) && empty( $this->last_name ) ) {
			$payment_title = $this->first_name;
		} elseif ( ! empty( $this->email ) && is_email( $this->email ) ) {
			$payment_title = $this->email;
		}

		// Set Key.
		if ( empty( $this->key ) ) {

			$auth_key             = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
			$this->key            = strtolower( md5( $this->email . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'give', true ) ) );  // Unique key.
			$this->pending['key'] = $this->key;
		}

		// Set IP.
		if ( empty( $this->ip ) ) {

			$this->ip            = give_get_ip();
			$this->pending['ip'] = $this->ip;

		}

		// @todo: payment data exist here only for backward compatibility
		// issue: https://github.com/impress-org/give/issues/1132
		$payment_data = array(
			'price'        => $this->total,
			'date'         => $this->date,
			'user_email'   => $this->email,
			'purchase_key' => $this->key,
			'form_title'   => $this->form_title,
			'form_id'      => $this->form_id,
			'donor_id'     => $this->donor_id,
			'price_id'     => $this->price_id,
			'currency'     => $this->currency,
			'user_info'    => array(
				'id'         => $this->user_id,
				'title'      => $this->title_prefix,
				'email'      => $this->email,
				'first_name' => $this->first_name,
				'last_name'  => $this->last_name,
				'address'    => $this->address,
			),
			'status'       => $this->status,
		);

		$args = apply_filters(
			'give_insert_payment_args',
			array(
				'post_title'    => $payment_title,
				'post_status'   => $this->status,
				'post_type'     => 'give_payment',
				'post_date'     => ! empty( $this->date ) ? $this->date : null,
				'post_date_gmt' => ! empty( $this->date ) ? get_gmt_from_date( $this->date ) : null,
				'post_parent'   => $this->parent_payment,
			),
			$payment_data
		);

		// Create a blank payment.
		$payment_id = wp_insert_post( $args );

		if ( ! empty( $payment_id ) ) {

			$this->ID  = $payment_id;
			$this->_ID = $payment_id;

			$donor = new stdClass();

			if ( did_action( 'give_pre_process_donation' ) && is_user_logged_in() ) {
				$donor = new Give_Donor( get_current_user_id(), true );

				// Donor is logged in but used a different email to purchase with so assign to their donor record.
				if ( ! empty( $donor->id ) && $this->email !== $donor->email ) {
					$donor->add_email( $this->email );
				}
			}

			if ( empty( $donor->id ) ) {
				$donor = new Give_Donor( $this->email );
			}

			if ( empty( $donor->id ) ) {

				$donor_data = array(
					'name'    => ! is_email( $payment_title ) ? $this->first_name . ' ' . $this->last_name : '',
					'email'   => $this->email,
					'user_id' => $this->user_id,
				);

				$donor->create( $donor_data );

			}

			/**
			 * Filters the donor object after donation is completed but before donor table is updated.
			 *
			 * @since 1.8.13
			 * @since 2.4.2  Moved location of filter to occur after donor is hydrated.
			 *
			 * @param Give_Donor $donor        Donor object.
			 * @param int        $payment_id   Payment ID.
			 * @param array      $payment_data Payment data array.
			 * @param array      $args         Payment args.
			 */
			$donor = apply_filters( 'give_update_donor_information', $donor, $payment_id, $payment_data, $args );

			// Update Donor Meta once donor is created.
			$donor->update_meta( '_give_donor_first_name', $this->first_name );
			$donor->update_meta( '_give_donor_last_name', $this->last_name );
			$donor->update_meta( '_give_donor_title_prefix', $this->title_prefix );

			$this->customer_id            = $donor->id;
			$this->pending['customer_id'] = $this->customer_id;
			$donor->attach_payment( $this->ID, false );

			$this->payment_meta = apply_filters( 'give_payment_meta', $this->payment_meta, $payment_data );

			/**
			 * _give_payment_meta backward compatibility.
			 *
			 * @since 2.0.1
			 */
			$custom_payment_meta = array_diff(
				array_map( 'maybe_serialize', $this->payment_meta ),
				array_map( 'maybe_serialize', $payment_data )
			);

			if ( ! empty( $custom_payment_meta ) ) {
				give_doing_it_wrong( '_give_payment_meta', __( 'This custom meta key has been deprecated for performance reasons. Your custom meta data will still be stored but we recommend updating your code to store meta keys individually from GiveWP 2.0.0.', 'give' ) );

				$this->update_meta( '_give_payment_meta', array_map( 'maybe_unserialize', $custom_payment_meta ) );
			}

			$give_company = ( ! empty( $_REQUEST['give_company_name'] ) ? give_clean( $_REQUEST['give_company_name'] ) : '' );

			// Check $page_url is not empty.
			if ( $give_company ) {
				give_update_meta( $payment_id, '_give_donation_company', $give_company );

				$donor_id = absint( $donor->id );
				if ( ! empty( $donor_id ) ) {
					Give()->donor_meta->update_meta( $donor_id, '_give_donor_company', $give_company );
				}
			}

			$this->new = true;
		} // End if().

		return $this->ID;

	}

	/**
	 * Save
	 *
	 * Once items have been set, an update is needed to save them to the database.
	 *
	 * @access public
	 *
	 * @return bool  True of the save occurred, false if it failed or wasn't needed
	 */
	public function save() {
		$saved = false;

		// Must have an ID.
		if ( empty( $this->ID ) ) {

			$payment_id = $this->insert_payment();

			if ( false === $payment_id ) {
				$saved = false;
			} else {
				$this->ID = $payment_id;
			}
		}

		// Set ID if not matching.
		if ( $this->ID !== $this->_ID ) {
			$this->ID = $this->_ID;
		}

		// If we have something pending, let's save it.
		if ( ! empty( $this->pending ) ) {

			$total_increase = 0;
			$total_decrease = 0;

			foreach ( $this->pending as $key => $value ) {

				switch ( $key ) {

					case 'donations':
						// Update totals for pending donations.
						foreach ( $this->pending[ $key ] as $item ) {

							$quantity = isset( $item['quantity'] ) ? $item['quantity'] : 1;
							$price_id = isset( $item['price_id'] ) ? $item['price_id'] : 0;

							switch ( $item['action'] ) {

								case 'add':
									$price = $item['price'];

									if ( 'publish' === $this->status || 'complete' === $this->status ) {

										// Add donation to logs.
										$log_date = date_i18n( 'Y-m-d G:i:s', current_time( 'timestamp' ) );
										give_record_donation_in_log( $item['id'], $this->ID, $price_id, $log_date );

										$form = new Give_Donate_Form( $item['id'] );
										$form->increase_sales( $quantity );
										$form->increase_earnings( $price, $this->ID );

										$total_increase += $price;
									}
									break;

								case 'remove':
									$this->delete_sales_logs();
									if ( 'publish' === $this->status || 'complete' === $this->status ) {
										$form = new Give_Donate_Form( $item['id'] );
										$form->decrease_sales( $quantity );
										$form->decrease_earnings( $item['amount'], $this->ID );

										$total_decrease += $item['amount'];
									}
									break;

							}// End switch().
						}// End foreach().
						break;

					case 'status':
						$this->update_status( $this->status );
						break;

					case 'gateway':
						$this->update_meta( '_give_payment_gateway', $this->gateway );
						break;

					case 'mode':
						$this->update_meta( '_give_payment_mode', $this->mode );
						break;

					case 'transaction_id':
						$this->update_meta( '_give_payment_transaction_id', $this->transaction_id );
						break;

					case 'ip':
						$this->update_meta( '_give_payment_donor_ip', $this->ip );
						break;

					case 'customer_id':
						$this->update_meta( '_give_payment_donor_id', $this->customer_id );
						break;

					case 'form_title':
						$this->update_meta( '_give_payment_form_title', $this->form_title );
						break;

					case 'form_id':
						$this->update_meta( '_give_payment_form_id', $this->form_id );
						break;

					case 'price_id':
						$this->update_meta( '_give_payment_price_id', $this->price_id );
						break;

					case 'first_name':
						$this->update_meta( '_give_donor_billing_first_name', $this->first_name );
						break;

					case 'last_name':
						$this->update_meta( '_give_donor_billing_last_name', $this->last_name );
						break;

					case 'currency':
						$this->update_meta( '_give_payment_currency', $this->currency );
						break;

					case 'address':
						if ( ! empty( $this->address ) ) {
							foreach ( $this->address as $address_name => $address ) {
								switch ( $address_name ) {
									case 'line1':
										$this->update_meta( '_give_donor_billing_address1', $address );
										break;

									case 'line2':
										$this->update_meta( '_give_donor_billing_address2', $address );
										break;

									default:
										$this->update_meta( "_give_donor_billing_{$address_name}", $address );
								}
							}
						}
						break;

					case 'email':
						$this->update_meta( '_give_payment_donor_email', $this->email );
						break;

					case 'title_prefix':
						$this->update_meta( '_give_payment_donor_title_prefix', $this->title_prefix );
						break;

					case 'key':
						$this->update_meta( '_give_payment_purchase_key', $this->key );
						break;

					case 'number':
						// @todo: remove unused meta data.
						// Core is using post_title to store donation serial code ( fi enabled ) instead this meta key.
						// Do not use this meta key in your logic, can be remove in future
						$this->update_meta( '_give_payment_number', $this->number );
						break;

					case 'date':
						$args = array(
							'ID'            => $this->ID,
							'post_date'     => date( 'Y-m-d H:i:s', strtotime( $this->date ) ),
							'post_date_gmt' => get_gmt_from_date( $this->date ),
							'edit_date'     => true,
						);

						wp_update_post( $args );
						break;

					case 'completed_date':
						$this->update_meta( '_give_completed_date', $this->completed_date );
						break;

					case 'parent_payment':
						$args = array(
							'ID'          => $this->ID,
							'post_parent' => $this->parent_payment,
						);

						wp_update_post( $args );
						break;

					case 'total':
						$this->update_meta( '_give_payment_total', give_sanitize_amount_for_db( $this->total ) );
						break;

					default:
						/**
						 * Fires while saving payment.
						 *
						 * @since 1.7
						 *
						 * @param Give_Payment $this Payment object.
						 */
						do_action( 'give_payment_save', $this, $key );
						break;
				} // End switch().
			} // End foreach().

			if ( 'pending' !== $this->status ) {

				$donor = new Give_Donor( $this->customer_id );

				$total_change = $total_increase - $total_decrease;
				if ( $total_change < 0 ) {

					$total_change = - ( $total_change );

					// Decrease the donor's donation stats.
					$donor->decrease_value( $total_change );
					give_decrease_total_earnings( $total_change );

					$donor->decrease_donation_count();

				} elseif ( $total_change > 0 ) {

					// Increase the donor's donation stats.
					$donor->increase_value( $total_change );
					give_increase_total_earnings( $total_change );

					$donor->increase_purchase_count();

				}

				// Verify and update form meta based on the form status.
				give_set_form_closed_status( $this->form_id );
			}

			$this->pending = array();
			$saved         = true;
		} // End if().

		if ( true === $saved ) {
			$this->setup_payment( $this->ID );
		}

		return $saved;
	}

	/**
	 * Add a donation to a given payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  int   $form_id The donation form to add.
	 * @param  array $args    Other arguments to pass to the function.
	 * @param  array $options List of donation options.
	 *
	 * @return bool           True when successful, false otherwise
	 */
	public function add_donation( $form_id = 0, $args = array(), $options = array() ) {

		$donation = new Give_Donate_Form( $form_id );

		// Bail if this post isn't a give donation form.
		if ( ! $donation || 'give_forms' !== $donation->post_type ) {
			return false;
		}

		// Set some defaults.
		$defaults = array(
			'price'    => false,
			'price_id' => false,
		);

		$args = wp_parse_args( apply_filters( 'give_payment_add_donation_args', $args, $donation->ID ), $defaults );

		// Allow overriding the price.
		if ( false !== $args['price'] ) {
			$donation_amount = $args['price'];
		} else {

			// Deal with variable pricing.
			if ( give_has_variable_prices( $donation->ID ) ) {
				$prices          = give_get_meta( $form_id, '_give_donation_levels', true );
				$donation_amount = '';

				// Loop through prices.
				foreach ( $prices as $price ) {
					// Find a match between price_id and level_id.
					// First verify array keys exists THEN make the match.
					if (
						isset( $args['price_id'] ) &&
						isset( $price['_give_id']['level_id'] ) &&
						$args['price_id'] === (int) $price['_give_id']['level_id']
					) {
						$donation_amount = $price['_give_amount'];
					}
				}

				// Fallback to the lowest price point.
				if ( '' === $donation_amount ) {
					$donation_amount  = give_get_lowest_price_option( $donation->ID );
					$args['price_id'] = give_get_lowest_price_id( $donation->ID );
				}
			} else {
				// Simple form price.
				$donation_amount = give_get_form_price( $donation->ID );
			}
		}

		// Sanitizing the price here so we don't have a dozen calls later.
		$donation_amount = give_maybe_sanitize_amount( $donation_amount );
		$total           = round( $donation_amount, give_get_price_decimals( $this->ID ) );

		// Add Options.
		$default_options = array();
		if ( false !== $args['price_id'] ) {
			$default_options['price_id'] = (int) $args['price_id'];
		}
		$options = wp_parse_args( $options, $default_options );

		// Do not allow totals to go negative.
		if ( $total < 0 ) {
			$total = 0;
		}

		$donation = array(
			'name'     => $donation->post_title,
			'id'       => $donation->ID,
			'price'    => round( $total, give_get_price_decimals( $this->ID ) ),
			'subtotal' => round( $total, give_get_price_decimals( $this->ID ) ),
			'price_id' => $args['price_id'],
			'action'   => 'add',
			'options'  => $options,
		);

		$this->pending['donations'][] = $donation;

		$this->increase_subtotal( $total );

		return true;

	}

	/**
	 * Remove a donation from the payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  int   $form_id The form ID to remove.
	 * @param  array $args    Arguments to pass to identify (quantity, amount, price_id).
	 *
	 * @return bool           If the item was removed or not
	 */
	public function remove_donation( $form_id, $args = array() ) {

		// Set some defaults.
		$defaults = array(
			'quantity' => 1,
			'price'    => false,
			'price_id' => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		$form = new Give_Donate_Form( $form_id );

		// Bail if this post isn't a valid give donation form.
		if ( ! $form || 'give_forms' !== $form->post_type ) {
			return false;
		}

		$pending_args             = $args;
		$pending_args['id']       = $form_id;
		$pending_args['amount']   = $this->total;
		$pending_args['price_id'] = false !== $args['price_id'] ? (int) $args['price_id'] : false;
		$pending_args['quantity'] = $args['quantity'];
		$pending_args['action']   = 'remove';

		$this->pending['donations'][] = $pending_args;

		$this->decrease_subtotal( $this->total );

		return true;
	}


	/**
	 * Add a note to a payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string|bool $note The note to add.
	 *
	 * @return bool           If the note was specified or not
	 */
	public function add_note( $note = false ) {
		// Bail if no note specified.
		if ( ! $note ) {
			return false;
		}

		give_insert_payment_note( $this->ID, $note );
	}

	/**
	 * Increase the payment's subtotal
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  float $amount The amount to increase the payment subtotal by.
	 *
	 * @return void
	 */
	private function increase_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal += $amount;

		$this->recalculate_total();
	}

	/**
	 * Decrease the payment's subtotal.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  float $amount The amount to decrease the payment subtotal by.
	 *
	 * @return void
	 */
	private function decrease_subtotal( $amount = 0.00 ) {
		$amount          = (float) $amount;
		$this->subtotal -= $amount;

		if ( $this->subtotal < 0 ) {
			$this->subtotal = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Set or update the total for a payment.
	 *
	 * @since  1.5
	 * @since  2.1.4 reset total in pending property
	 * @access private
	 *
	 * @return void
	 */
	private function recalculate_total() {
		$this->pending['total'] = $this->total = $this->subtotal;
	}

	/**
	 * Set the payment status and run any status specific changes necessary.
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string|bool $status The status to set the payment to.
	 *
	 * @return bool   $updated Returns if the status was successfully updated.
	 */
	public function update_status( $status = false ) {

		// standardize the 'complete(d)' status.
		if ( 'completed' === $status || 'complete' === $status ) {
			$status = 'publish';
		}

		$old_status = ! empty( $this->old_status ) ? $this->old_status : false;

		if ( $old_status === $status ) {
			return false; // Don't permit status changes that aren't changes.
		}

		$do_change = apply_filters( 'give_should_update_payment_status', true, $this->ID, $status, $old_status );

		$updated = false;

		if ( $do_change ) {

			/**
			 * Fires before changing payment status.
			 *
			 * @since 1.5
			 *
			 * @param int    $payment_id Payments ID.
			 * @param string $status     The new status.
			 * @param string $old_status The old status.
			 */
			do_action( 'give_before_payment_status_change', $this->ID, $status, $old_status );

			$update_fields = array(
				'ID'          => $this->ID,
				'post_status' => $status,
				'edit_date'   => current_time( 'mysql' ),
			);

			$updated = wp_update_post( apply_filters( 'give_update_payment_status_fields', $update_fields ) );

			$all_payment_statuses  = give_get_payment_statuses();
			$this->status_nicename = array_key_exists( $status, $all_payment_statuses ) ? $all_payment_statuses[ $status ] : ucfirst( $status );

			// Process any specific status functions.
			$this->process_status( $status );

			/**
			 * Fires after changing payment status.
			 *
			 * @since 1.5
			 *
			 * @param int    $payment_id Payment ID.
			 * @param string $status     The new status.
			 * @param string $old_status The old status.
			 */
			do_action( 'give_update_payment_status', $this->ID, $status, $old_status );

		} // End if().

		return $updated;

	}

	/**
	 * Change the status of the payment to refunded, and run the necessary changes
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @return void
	 */
	public function refund() {
		$this->old_status        = $this->status;
		$this->status            = 'refunded';
		$this->pending['status'] = $this->status;

		$this->save();
	}

	/**
	 * Get a post meta item for the payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string  $meta_key The Meta Key.
	 * @param  boolean $single   Return single item or array.
	 *
	 * @return mixed             The value from the post meta
	 */
	public function get_meta( $meta_key = '_give_payment_meta', $single = true ) {
		if (
			! has_filter( 'get_post_metadata', 'give_bc_v20_get_payment_meta' ) &&
			! doing_filter( 'get_post_metadata' )
		) {
			add_filter( 'get_post_metadata', 'give_bc_v20_get_payment_meta', 999, 4 );
		}

		$meta = give_get_meta( $this->ID, $meta_key, $single );

		/**
		 * Filter the specific meta key value.
		 *
		 * @since 1.5
		 */
		$meta = apply_filters( "give_get_payment_meta_{$meta_key}", $meta, $this->ID );

		// Security check.
		if ( is_serialized( $meta ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $meta, $matches );
			if ( ! empty( $matches ) ) {
				$meta = array();
			}
		}

		/**
		 * Filter the all meta keys.
		 *
		 * @since 1.5
		 */
		return apply_filters( 'give_get_payment_meta', $meta, $this->ID, $meta_key );
	}

	/**
	 * Update the post meta
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $meta_key   The meta key to update.
	 * @param  string $meta_value The meta value.
	 * @param  string $prev_value Previous meta value.
	 *
	 * @return int|bool           Meta ID if the key didn't exist, true on successful update, false on failure
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		/**
		 * Filter the single meta key while updating
		 *
		 * @since 1.5
		 */
		$meta_value = apply_filters( "give_update_payment_meta_{$meta_key}", $meta_value, $this->ID );

		return give_update_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Process Donation Status.
	 *
	 * @param string $status Donation Status.
	 *
	 * @since  2.0.2
	 * @access private
	 *
	 * @return void
	 */
	private function process_status( $status ) {
		$process = true;

		// Bailout, if changed from completed to preapproval/processing.
		// Bailout, if current status = previous status or status is publish.
		if (
			'preapproval' === $status ||
			'processing' === $status ||
			'publish' !== $this->old_status ||
			$status !== $this->status
		) {
			$process = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments.
		$process = apply_filters( "give_should_process_{$status}", $process, $this );

		if ( false === $process ) {
			return;
		}

		/**
		 * Fires before processing donation status.
		 *
		 * @param Give_Payment $this Payment object.
		 *
		 * @since 1.5
		 */
		do_action( "give_pre_{$status}_payment", $this );

		$decrease_earnings       = apply_filters( "give_decrease_earnings_on_{$status}", true, $this );
		$decrease_donor_value    = apply_filters( "give_decrease_donor_value_on_{$status}", true, $this );
		$decrease_donation_count = apply_filters( "give_decrease_donors_donation_count_on_{$status}", true, $this );

		$this->maybe_alter_stats( $decrease_earnings, $decrease_donor_value, $decrease_donation_count );
		$this->delete_sales_logs();

		// @todo: Refresh only range related stat cache
		give_delete_donation_stats();

		/**
		 * Fires after processing donation status.
		 *
		 * @param Give_Payment $this Payment object.
		 *
		 * @since 1.5
		 */
		do_action( "give_post_{$status}_payment", $this );
	}

	/**
	 * Used during the process of moving to refunded or pending, to decrement stats
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  bool $alter_store_earnings          If the method should alter the store earnings.
	 * @param  bool $alter_customer_value          If the method should reduce the donor value.
	 * @param  bool $alter_customer_purchase_count If the method should reduce the donor's purchase count.
	 *
	 * @return void
	 */
	private function maybe_alter_stats( $alter_store_earnings, $alter_customer_value, $alter_customer_purchase_count ) {

		give_undo_donation( $this->ID );

		// Decrease store earnings.
		if ( true === $alter_store_earnings ) {
			give_decrease_total_earnings( $this->total );
		}

		// Decrement the stats for the donor.
		if ( ! empty( $this->customer_id ) ) {

			$donor = new Give_Donor( $this->customer_id );

			if ( true === $alter_customer_value ) {
				$donor->decrease_value( $this->total );
			}

			if ( true === $alter_customer_purchase_count ) {
				$donor->decrease_donation_count();
			}
		}

	}

	/**
	 * Delete sales logs for this donation
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return void
	 */
	private function delete_sales_logs() {
		// Remove related sale log entries.
		Give()->logs->delete_logs( $this->ID );
	}

	/**
	 * Setup functions only, these are not to be used by developers.
	 * These functions exist only to allow the setup routine to be backwards compatible with our old
	 * helper functions.
	 *
	 * These will run whenever setup_payment is called, which should only be called once.
	 * To update an attribute, update it directly instead of re-running the setup routine
	 */

	/**
	 * Setup the payment completed date
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The date the payment was completed
	 */
	private function setup_completed_date() {
		$payment = get_post( $this->ID );

		if ( 'pending' === $payment->post_status || 'preapproved' === $payment->post_status ) {
			return false; // This payment was never completed.
		}

		$date = ( $date = $this->get_meta( '_give_completed_date', true ) ) ? $date : $payment->modified_date;

		return $date;
	}

	/**
	 * Setup the payment mode
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The payment mode
	 */
	private function setup_mode() {
		return $this->get_meta( '_give_payment_mode' );
	}

	/**
	 * Setup the payment import data
	 *
	 * @since  1.8.13
	 * @access private
	 *
	 * @return bool The payment import
	 */
	private function setup_import() {
		return (bool) $this->get_meta( '_give_payment_import' );
	}

	/**
	 * Setup the payment total
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return float The payment total
	 */
	private function setup_total() {
		$amount = $this->get_meta( '_give_payment_total', true );

		return round( floatval( $amount ), give_get_price_decimals( $this->ID ) );
	}

	/**
	 * Setup the payment subtotal
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return float The subtotal of the payment
	 */
	private function setup_subtotal() {
		$subtotal = $this->total;

		return $subtotal;
	}

	/**
	 * Setup the currency code
	 *
	 * @since  1.5
	 * @since  2.0 Set currency from _give_payment_currency meta key
	 * @access private
	 *
	 * @return string The currency for the payment
	 */
	private function setup_currency() {
		$currency = $this->get_meta( '_give_payment_currency', true );
		$currency = ! empty( $currency ) ?
			$currency :
			/**
			 * Filter the default donation currency
			 *
			 * @since 1.5
			 */
			apply_filters(
				'give_payment_currency_default',
				give_get_currency( $this->form_id, $this ),
				$this
			);

		return $currency;
	}

	/**
	 * Setup the gateway used for the payment
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The gateway
	 */
	private function setup_gateway() {
		$gateway = $this->get_meta( '_give_payment_gateway', true );

		return $gateway;
	}

	/**
	 * Setup the donation ID
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The donation ID
	 */
	private function setup_transaction_id() {
		$transaction_id = $this->get_meta( '_give_payment_transaction_id', true );

		if ( empty( $transaction_id ) ) {
			$gateway        = $this->gateway;
			$transaction_id = apply_filters( "give_get_payment_transaction_id-{$gateway}", $this->ID );
		}

		return $transaction_id;
	}

	/**
	 * Setup the IP Address for the payment
	 *
	 * @since  1.5
	 * @since  2.0 Set ip address from _give_payment_donor_ip meta key
	 * @access private
	 *
	 * @return string The IP address for the payment
	 */
	private function setup_ip() {
		$ip = $this->get_meta( '_give_payment_donor_ip', true );

		return $ip;
	}

	/**
	 * Setup the donor ID.
	 *
	 * @since  1.5
	 * @since  2.0 Set id from _give_payment_donor_id meta key
	 * @access private
	 *
	 * @return int The Donor ID.
	 */
	private function setup_donor_id() {
		$donor_id = $this->get_meta( '_give_payment_donor_id', true );

		return $donor_id;
	}

	/**
	 * Setup the User ID associated with the donation
	 *
	 * @since  1.5
	 * @since  2.0 Get user id connect to donor from donor table instead of payment meta.
	 *
	 * @access private
	 *
	 * @return int The User ID
	 */
	private function setup_user_id() {

		$donor   = Give()->donors->get_donor_by( 'id', $this->donor_id );
		$user_id = $donor ? absint( $donor->user_id ) : 0;

		return $user_id;
	}

	/**
	 * Setup the email address for the donation.
	 *
	 * @since  1.5
	 * @since  2.0 Set email from _give_payment_donor_email meta key
	 *
	 * @access private
	 *
	 * @return string The email address for the payment.
	 */
	private function setup_email() {
		$email = $this->get_meta( '_give_payment_donor_email', true );

		if ( empty( $email ) && $this->customer_id ) {
			$email = Give()->donors->get_column( 'email', $this->customer_id );
		}

		return $email;
	}

	/**
	 * Setup the user info.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array The user info associated with the payment.
	 */
	private function setup_user_info() {
		$defaults = array(
			'title'      => $this->title_prefix,
			'first_name' => $this->first_name,
			'last_name'  => $this->last_name,
		);

		$user_info = isset( $this->payment_meta['user_info'] ) ? $this->payment_meta['user_info'] : array();

		if ( is_serialized( $user_info ) ) {
			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				$user_info = array();
			}
		}

		$user_info = wp_parse_args( $user_info, $defaults );

		if ( empty( $user_info ) ) {
			// Get the donor, but only if it's been created.
			$donor = new Give_Donor( $this->customer_id );

			if ( $donor->id > 0 ) {
				$user_info = array(
					'first_name' => $donor->get_first_name(),
					'last_name'  => $donor->get_last_name(),
					'email'      => $donor->email,
					'discount'   => 'none',
				);
			}
		} else {
			// Get the donor, but only if it's been created.
			$donor = new Give_Donor( $this->customer_id );

			if ( $donor->id > 0 ) {
				foreach ( $user_info as $key => $value ) {
					if ( ! empty( $value ) ) {
						continue;
					}

					switch ( $key ) {
						case 'title':
							$user_info[ $key ] = Give()->donor_meta->get_meta( $donor->id, '_give_donor_title_prefix', true );
							break;

						case 'first_name':
							$user_info[ $key ] = $donor->get_first_name();
							break;

						case 'last_name':
							$user_info[ $key ] = $donor->get_last_name();
							break;

						case 'email':
							$user_info[ $key ] = $donor->email;
							break;
					}
				}
			}
		}// End if().

		return $user_info;

	}

	/**
	 * Setup the Address for the payment.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array The Address information for the payment.
	 */
	private function setup_address() {
		$address['line1']   = give_get_meta( $this->ID, '_give_donor_billing_address1', true, '' );
		$address['line2']   = give_get_meta( $this->ID, '_give_donor_billing_address2', true, '' );
		$address['city']    = give_get_meta( $this->ID, '_give_donor_billing_city', true, '' );
		$address['state']   = give_get_meta( $this->ID, '_give_donor_billing_state', true, '' );
		$address['zip']     = give_get_meta( $this->ID, '_give_donor_billing_zip', true, '' );
		$address['country'] = give_get_meta( $this->ID, '_give_donor_billing_country', true, '' );

		return $address;
	}

	/**
	 * Setup the form title.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The Form Title.
	 */
	private function setup_form_title() {

		$form_id = $this->get_meta( '_give_payment_form_title', true );

		return $form_id;
	}

	/**
	 * Setup the form ID.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int The Form ID
	 */
	private function setup_form_id() {

		$form_id = $this->get_meta( '_give_payment_form_id', true );

		return $form_id;
	}

	/**
	 * Setup the price ID.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int The Form Price ID.
	 */
	private function setup_price_id() {
		$price_id = $this->get_meta( '_give_payment_price_id', true );

		return $price_id;
	}

	/**
	 * Setup the payment key.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The Payment Key.
	 */
	private function setup_payment_key() {
		$key = $this->get_meta( '_give_payment_purchase_key', true );

		return $key;
	}

	/**
	 * Setup the payment number.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int|string Integer by default, or string if sequential order numbers is enabled.
	 */
	private function setup_payment_number() {
		return $this->get_serial_code();
	}

	/**
	 * Converts this object into an array for special cases.
	 *
	 * @access public
	 *
	 * @return array The payment object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
	}


	/**
	 * Flag to check if donation is completed or not.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @return bool
	 */
	public function is_completed() {
		return ( 'publish' === $this->status && $this->completed_date );
	}

	/**
	 * Retrieve payment completion date.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Date payment was completed.
	 */
	private function get_completed_date() {
		return apply_filters( 'give_payment_completed_date', $this->completed_date, $this->ID, $this );
	}

	/**
	 * Retrieve payment subtotal.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return float Payment subtotal.
	 */
	private function get_subtotal() {
		return apply_filters( 'give_get_payment_subtotal', $this->subtotal, $this->ID, $this );
	}

	/**
	 * Retrieve payment currency.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment currency code.
	 */
	private function get_currency() {
		return apply_filters( 'give_payment_currency_code', $this->currency, $this->ID, $this );
	}

	/**
	 * Retrieve payment gateway.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Gateway used.
	 */
	private function get_gateway() {
		return apply_filters( 'give_payment_gateway', $this->gateway, $this->ID, $this );
	}

	/**
	 * Retrieve donation ID.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Donation ID from merchant processor.
	 */
	private function get_transaction_id() {
		return apply_filters( 'give_get_payment_transaction_id', $this->transaction_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment IP
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment IP address
	 */
	private function get_ip() {
		return apply_filters( 'give_payment_user_ip', $this->ip, $this->ID, $this );
	}

	/**
	 * Retrieve payment donor ID.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int Payment donor ID.
	 */
	private function get_donor_id() {
		return apply_filters( 'give_payment_customer_id', $this->customer_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment user ID.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int Payment user ID.
	 */
	private function get_user_id() {
		return apply_filters( 'give_payment_user_id', $this->user_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment email.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment donor email.
	 */
	private function get_email() {
		return apply_filters( 'give_payment_user_email', $this->email, $this->ID, $this );
	}

	/**
	 * Retrieve payment user info.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array Payment user info.
	 */
	private function get_user_info() {
		return apply_filters( 'give_payment_meta_user_info', $this->user_info, $this->ID, $this );
	}

	/**
	 * Retrieve payment billing address.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array Payment billing address.
	 */
	private function get_address() {
		return apply_filters( 'give_payment_address', $this->address, $this->ID, $this );
	}

	/**
	 * Retrieve payment key.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment key.
	 */
	private function get_key() {
		return apply_filters( 'give_payment_key', $this->key, $this->ID, $this );
	}

	/**
	 * Retrieve payment form id
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment form id
	 */
	private function get_form_id() {
		return apply_filters( 'give_payment_form_id', $this->form_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment number
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int|string Payment number
	 */
	private function get_number() {
		return apply_filters( 'give_payment_number', $this->number, $this->ID, $this );
	}

	/**
	 * Get serial code
	 *
	 * @since 2.1
	 *
	 * @param array $args List of arguments.
	 *
	 * @return string
	 */
	public function get_serial_code( $args = array() ) {
		return Give()->seq_donation_number->get_serial_code( $this, $args );
	}
}
