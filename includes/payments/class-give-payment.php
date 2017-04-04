<?php
/**
 * Payments
 *
 * @package     Give
 * @subpackage  Classes/Give_Payment
 * @copyright   Copyright (c) 2016, WordImpress
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
 * @property string     $postdate
 * @property string     $status
 * @property string     $email
 * @property string     $payment_meta
 * @property string     $customer_id
 * @property string     $completed_date
 * @property string     $currency
 * @property string     $ip
 * @property array      $user_info
 * @property string     $gateway
 * @property string     $user_id
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
	 * Includes donation amount and fees.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    float
	 */
	protected $total = 0.00;

	/**
	 * The Subtotal fo the payment before fees
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    float
	 */
	protected $subtotal = 0;

	/**
	 * Array of global fees for this payment
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    array
	 */
	protected $fees = array();

	/**
	 * The sum of the fee amounts
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    float
	 */
	protected $fees_total = 0;

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
	 * @var string
	 */
	protected $post_status = 'pending'; // Same as $status but here for backwards compat

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
	 * The customer ID that made the payment
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @var    integer
	 */
	protected $customer_id = null;

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
	 * @param  int|bool $payment_id A given payment
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
	 * @param  string $key   The property name
	 * @param  mixed  $value The value of the property
	 */
	public function __set( $key, $value ) {
		$ignore = array( '_ID' );

		if ( $key === 'status' ) {
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
	 * @param  string $name The attribute to get
	 *
	 * @return boolean       If the item is set or not
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
	 * @param  int $payment_id The payment ID
	 *
	 * @return bool            If the setup was successful or not
	 */
	private function setup_payment( $payment_id ) {
		$this->pending = array();

		if ( empty( $payment_id ) ) {
			return false;
		}

		$payment = get_post( $payment_id );

		if ( ! $payment || is_wp_error( $payment ) ) {
			return false;
		}

		if ( 'give_payment' !== $payment->post_type ) {
			return false;
		}

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
		$this->parent_payment = $payment->post_parent;

		$all_payment_statuses  = give_get_payment_statuses();
		$this->status_nicename = array_key_exists( $this->status, $all_payment_statuses ) ? $all_payment_statuses[ $this->status ] : ucfirst( $this->status );

		// Items.
		$this->fees = $this->setup_fees();

		// Currency Based.
		$this->total      = $this->setup_total();
		$this->fees_total = $this->setup_fees_total();
		$this->subtotal   = $this->setup_subtotal();
		$this->currency   = $this->setup_currency();

		// Gateway based.
		$this->gateway        = $this->setup_gateway();
		$this->transaction_id = $this->setup_transaction_id();

		// User based.
		$this->ip          = $this->setup_ip();
		$this->customer_id = $this->setup_customer_id();
		$this->user_id     = $this->setup_user_id();
		$this->email       = $this->setup_email();
		$this->user_info   = $this->setup_user_info();
		$this->address     = $this->setup_address();
		$this->first_name  = $this->user_info['first_name'];
		$this->last_name   = $this->user_info['last_name'];

		// Other Identifiers.
		$this->form_title = $this->setup_form_title();
		$this->form_id    = $this->setup_form_id();
		$this->price_id   = $this->setup_price_id();
		$this->key        = $this->setup_payment_key();
		$this->number     = $this->setup_payment_number();

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
			$this->key            = strtolower( md5( $this->email . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'give', true ) ) );  // Unique key
			$this->pending['key'] = $this->key;
		}

		// Set IP.
		if ( empty( $this->ip ) ) {

			$this->ip            = give_get_ip();
			$this->pending['ip'] = $this->ip;

		}

		$payment_data = array(
			'price'        => $this->total,
			'date'         => $this->date,
			'user_email'   => $this->email,
			'purchase_key' => $this->key,
			'form_title'   => $this->form_title,
			'form_id'      => $this->form_id,
			'price_id'     => $this->price_id,
			'currency'     => $this->currency,
			'user_info'    => array(
				'id'         => $this->user_id,
				'email'      => $this->email,
				'first_name' => $this->first_name,
				'last_name'  => $this->last_name,
				'address'    => $this->address,
			),
			'status'       => $this->status,
			'fees'         => $this->fees,
		);

		$args = apply_filters( 'give_insert_payment_args', array(
			'post_title'    => $payment_title,
			'post_status'   => $this->status,
			'post_type'     => 'give_payment',
			'post_date'     => ! empty( $this->date ) ? $this->date : null,
			'post_date_gmt' => ! empty( $this->date ) ? get_gmt_from_date( $this->date ) : null,
			'post_parent'   => $this->parent_payment,
		), $payment_data );

		// Create a blank payment
		$payment_id = wp_insert_post( $args );

		if ( ! empty( $payment_id ) ) {

			$this->ID  = $payment_id;
			$this->_ID = $payment_id;

			$customer = new stdClass;

			if ( did_action( 'give_pre_process_donation' ) && is_user_logged_in() ) {
				$customer = new Give_Customer( get_current_user_id(), true );

				// Customer is logged in but used a different email to purchase with so assign to their customer record
				if ( ! empty( $customer->id ) && $this->email != $customer->email ) {
					$customer->add_email( $this->email );
				}
			}

			if ( empty( $customer->id ) ) {
				$customer = new Give_Customer( $this->email );
			}

			if ( empty( $customer->id ) ) {

				$customer_data = array(
					'name'    => ! is_email( $payment_title ) ? $this->first_name . ' ' . $this->last_name : '',
					'email'   => $this->email,
					'user_id' => $this->user_id,
				);

				$customer->create( $customer_data );

			}

			$this->customer_id            = $customer->id;
			$this->pending['customer_id'] = $this->customer_id;
			$customer->attach_payment( $this->ID, false );

			$this->payment_meta = apply_filters( 'give_payment_meta', $this->payment_meta, $payment_data );
			if ( ! empty( $this->payment_meta['fees'] ) ) {
				$this->fees = array_merge( $this->fees, $this->payment_meta['fees'] );
				foreach ( $this->fees as $fee ) {
					$this->increase_fees( $fee['amount'] );
				}
			}

			$this->update_meta( '_give_payment_meta', $this->payment_meta );
			$this->new = true;
		}

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

										// Add sales logs.
										$log_date = date_i18n( 'Y-m-d G:i:s', current_time( 'timestamp' ) );

										$y = 0;
										while ( $y < $quantity ) {

											give_record_sale_in_log( $item['id'], $this->ID, $price_id, $log_date );
											$y ++;
										}

										$form = new Give_Donate_Form( $item['id'] );
										$form->increase_sales( $quantity );
										$form->increase_earnings( $price );

										$total_increase += $price;
									}
									break;

								case 'remove':
									$log_args = array(
										'post_type'   => 'give_log',
										'post_parent' => $item['id'],
										'numberposts' => $quantity,
										'meta_query'  => array(
											array(
												'key'     => '_give_log_payment_id',
												'value'   => $this->ID,
												'compare' => '=',
											),
											array(
												'key'     => '_give_log_price_id',
												'value'   => $price_id,
												'compare' => '=',
											),
										),
									);

									$found_logs = get_posts( $log_args );
									foreach ( $found_logs as $log ) {
										wp_delete_post( $log->ID, true );
									}

									if ( 'publish' === $this->status || 'complete' === $this->status ) {
										$form = new Give_Donate_Form( $item['id'] );
										$form->decrease_sales( $quantity );
										$form->decrease_earnings( $item['amount'] );

										$total_decrease += $item['amount'];
									}
									break;

							}
						}
						break;

					case 'fees':

						if ( 'publish' !== $this->status && 'complete' !== $this->status ) {
							break;
						}

						if ( empty( $this->pending[ $key ] ) ) {
							break;
						}

						foreach ( $this->pending[ $key ] as $fee ) {

							switch ( $fee['action'] ) {

								case 'add':
									$total_increase += $fee['amount'];
									break;

								case 'remove':
									$total_decrease += $fee['amount'];
									break;

							}
						}

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
						$this->update_meta( '_give_payment_user_ip', $this->ip );
						break;

					case 'customer_id':
						$this->update_meta( '_give_payment_customer_id', $this->customer_id );
						break;

					case 'user_id':
						$this->update_meta( '_give_payment_user_id', $this->user_id );
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
						$this->user_info['first_name'] = $this->first_name;
						break;

					case 'last_name':
						$this->user_info['last_name'] = $this->last_name;
						break;

					case 'address':
						$this->user_info['address'] = $this->address;
						break;

					case 'email':
						$this->update_meta( '_give_payment_user_email', $this->email );
						break;

					case 'key':
						$this->update_meta( '_give_payment_purchase_key', $this->key );
						break;

					case 'number':
						$this->update_meta( '_give_payment_number', $this->number );
						break;

					case 'date':
						$args = array(
							'ID'        => $this->ID,
							'post_date' => $this->date,
							'edit_date' => true,
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
				}
			}

			if ( 'pending' !== $this->status ) {

				$customer = new Give_Customer( $this->customer_id );

				$total_change = $total_increase - $total_decrease;
				if ( $total_change < 0 ) {

					$total_change = - ( $total_change );
					// Decrease the customer's donation stats.
					$customer->decrease_value( $total_change );
					give_decrease_total_earnings( $total_change );

				} elseif ( $total_change > 0 ) {

					// Increase the customer's donation stats.
					$customer->increase_value( $total_change );
					give_increase_total_earnings( $total_change );

				}
			}

			$this->update_meta( '_give_payment_total', $this->total );

			$new_meta = array(
				'form_title' => $this->form_title,
				'form_id'    => $this->form_id,
				'price_id'   => $this->price_id,
				'fees'       => $this->fees,
				'currency'   => $this->currency,
				'user_info'  => $this->user_info,
			);

			$meta        = $this->get_meta();
			$merged_meta = array_merge( $meta, $new_meta );

			// Only save the payment meta if it's changed.
			if ( md5( serialize( $meta ) ) !== md5( serialize( $merged_meta ) ) ) {
				$updated = $this->update_meta( '_give_payment_meta', $merged_meta );
				if ( false !== $updated ) {
					$saved = true;
				}
			}

			$this->pending = array();
			$saved         = true;
		}

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
	 * @param  int   $form_id The donation form to add
	 * @param  array $args    Other arguments to pass to the function
	 * @param  array $options List of donation options
	 *
	 * @return bool           True when successful, false otherwise
	 */
	public function add_donation( $form_id = 0, $args = array(), $options = array() ) {

		$donation = new Give_Donate_Form( $form_id );

		// Bail if this post isn't a give donation form.
		if ( ! $donation || $donation->post_type !== 'give_forms' ) {
			return false;
		}

		// Set some defaults.
		$defaults = array(
			'price'    => false,
			'price_id' => false,
			'fees'     => array(),
		);

		$args = wp_parse_args( apply_filters( 'give_payment_add_donation_args', $args, $donation->ID ), $defaults );

		// Allow overriding the price.
		if ( false !== $args['price'] ) {
			$item_price = $args['price'];
		} else {

			// Deal with variable pricing.
			if ( give_has_variable_prices( $donation->ID ) ) {
				$prices     = maybe_unserialize( get_post_meta( $form_id, '_give_donation_levels', true ) );
				$item_price = '';
				// Loop through prices.
				foreach ( $prices as $price ) {
					// Find a match between price_id and level_id.
					// First verify array keys exists THEN make the match.
					if ( ( isset( $args['price_id'] ) && isset( $price['_give_id']['level_id'] ) )
					     && $args['price_id'] == $price['_give_id']['level_id']
					) {
						$item_price = $price['_give_amount'];
					}
				}
				// Fallback to the lowest price point.
				if ( $item_price == '' ) {
					$item_price       = give_get_lowest_price_option( $donation->ID );
					$args['price_id'] = give_get_lowest_price_id( $donation->ID );
				}
			} else {
				// Simple form price.
				$item_price = give_get_form_price( $donation->ID );
			}
		}

		// Sanitizing the price here so we don't have a dozen calls later.
		$item_price = give_sanitize_amount( $item_price );
		$total      = round( $item_price, give_currency_decimal_filter() );

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
			'price'    => round( $total, give_currency_decimal_filter() ),
			'subtotal' => round( $total, give_currency_decimal_filter() ),
			'fees'     => $args['fees'],
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
	 * @param  int   $form_id The form ID to remove
	 * @param  array $args    Arguments to pass to identify (quantity, amount, price_id)
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
		if ( ! $form || $form->post_type !== 'give_forms' ) {
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
	 * Add a fee to a given payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  array $args Array of arguments for the fee to add
	 * @param  bool  $global
	 *
	 * @return bool          If the fee was added
	 */
	public function add_fee( $args, $global = true ) {

		$default_args = array(
			'label'    => '',
			'amount'   => 0,
			'type'     => 'fee',
			'id'       => '',
			'price_id' => 0,
		);

		$fee          = wp_parse_args( $args, $default_args );
		$this->fees[] = $fee;

		$added_fee               = $fee;
		$added_fee['action']     = 'add';
		$this->pending['fees'][] = $added_fee;
		reset( $this->fees );

		$this->increase_fees( $fee['amount'] );

		return true;
	}

	/**
	 * Remove a fee from the payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  int $key The array key index to remove
	 *
	 * @return bool     If the fee was removed successfully
	 */
	public function remove_fee( $key ) {
		$removed = false;

		if ( is_numeric( $key ) ) {
			$removed = $this->remove_fee_by( 'index', $key );
		}

		return $removed;
	}

	/**
	 * Remove a fee by the defined attributed
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string     $key    The key to remove by
	 * @param  int|string $value  The value to search for
	 * @param  boolean    $global False - removes the first value it fines,
	 *                            True - removes all matches.
	 *
	 * @return boolean            If the item is removed
	 */
	public function remove_fee_by( $key, $value, $global = false ) {

		$allowed_fee_keys = apply_filters( 'give_payment_fee_keys', array(
			'index',
			'label',
			'amount',
			'type',
		) );

		if ( ! in_array( $key, $allowed_fee_keys ) ) {
			return false;
		}

		$removed = false;
		if ( 'index' === $key && array_key_exists( $value, $this->fees ) ) {

			$removed_fee             = $this->fees[ $value ];
			$removed_fee['action']   = 'remove';
			$this->pending['fees'][] = $removed_fee;

			$this->decrease_fees( $removed_fee['amount'] );

			unset( $this->fees[ $value ] );
			$removed = true;

		} elseif ( 'index' !== $key ) {

			foreach ( $this->fees as $index => $fee ) {

				if ( isset( $fee[ $key ] ) && $fee[ $key ] == $value ) {

					$removed_fee             = $fee;
					$removed_fee['action']   = 'remove';
					$this->pending['fees'][] = $removed_fee;

					$this->decrease_fees( $removed_fee['amount'] );

					unset( $this->fees[ $index ] );
					$removed = true;

					if ( false === $global ) {
						break;
					}
				}
			}
		}

		if ( true === $removed ) {
			$this->fees = array_values( $this->fees );
		}

		return $removed;
	}

	/**
	 * Get the fees, filterable by type
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $type All, item, fee
	 *
	 * @return array        The Fees for the type specified
	 */
	public function get_fees( $type = 'all' ) {
		$fees = array();

		if ( ! empty( $this->fees ) && is_array( $this->fees ) ) {

			foreach ( $this->fees as $fee_id => $fee ) {

				if ( 'all' != $type && ! empty( $fee['type'] ) && $type != $fee['type'] ) {
					continue;
				}

				$fee['id'] = $fee_id;
				$fees[]    = $fee;

			}
		}

		return apply_filters( 'give_get_payment_fees', $fees, $this->ID, $this );
	}

	/**
	 * Add a note to a payment
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $note The note to add
	 *
	 * @return void
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
		$amount = (float) $amount;
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
		$amount = (float) $amount;
		$this->subtotal -= $amount;

		if ( $this->subtotal < 0 ) {
			$this->subtotal = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Increase the payment's subtotal.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  float $amount The amount to increase the payment subtotal by.
	 *
	 * @return void
	 */
	private function increase_fees( $amount = 0.00 ) {
		$amount = (float) $amount;
		$this->fees_total += $amount;

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
	private function decrease_fees( $amount = 0.00 ) {
		$amount = (float) $amount;
		$this->fees_total -= $amount;

		if ( $this->fees_total < 0 ) {
			$this->fees_total = 0;
		}

		$this->recalculate_total();
	}

	/**
	 * Set or update the total for a payment.
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return void
	 */
	private function recalculate_total() {
		$this->total = $this->subtotal + $this->fees_total;
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
		if ( $status == 'completed' || $status == 'complete' ) {
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
			switch ( $status ) {
				case 'refunded':
					$this->process_refund();
					break;
				case 'failed':
					$this->process_failure();
					break;
				case 'pending':
					$this->process_pending();
					break;
				case 'cancelled':
					$this->process_cancelled();
					break;
				case 'revoked':
					$this->process_revoked();
					break;
			}

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

		}

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
	 * @param  string  $meta_key The Meta Key
	 * @param  boolean $single   Return single item or array
	 *
	 * @return mixed             The value from the post meta
	 */
	public function get_meta( $meta_key = '_give_payment_meta', $single = true ) {

		$meta = get_post_meta( $this->ID, $meta_key, $single );

		if ( $meta_key === '_give_payment_meta' ) {
			$meta = (array) $meta;

			if ( empty( $meta['key'] ) ) {
				$meta['key'] = $this->setup_payment_key();
			}

			if ( empty( $meta['form_title'] ) ) {
				$meta['form_title'] = $this->setup_form_title();
			}

			if ( empty( $meta['email'] ) ) {
				$meta['email'] = $this->setup_email();
			}

			if ( empty( $meta['date'] ) ) {
				$meta['date'] = get_post_field( 'post_date', $this->ID );
			}
		}

		$meta = apply_filters( "give_get_payment_meta_{$meta_key}", $meta, $this->ID );

		return apply_filters( 'give_get_payment_meta', $meta, $this->ID, $meta_key );
	}

	/**
	 * Update the post meta
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  string $meta_key   The meta key to update
	 * @param  string $meta_value The meta value
	 * @param  string $prev_value Previous meta value
	 *
	 * @return int|bool           Meta ID if the key didn't exist, true on successful update, false on failure
	 */
	public function update_meta( $meta_key = '', $meta_value = '', $prev_value = '' ) {
		if ( empty( $meta_key ) ) {
			return false;
		}

		if ( $meta_key == 'key' || $meta_key == 'date' ) {

			$current_meta              = $this->get_meta();
			$current_meta[ $meta_key ] = $meta_value;

			$meta_key   = '_give_payment_meta';
			$meta_value = $current_meta;

		} elseif ( $meta_key == 'email' || $meta_key == '_give_payment_user_email' ) {

			$meta_value = apply_filters( "give_give_update_payment_meta_{$meta_key}", $meta_value, $this->ID );
			update_post_meta( $this->ID, '_give_payment_user_email', $meta_value );

			$current_meta                       = $this->get_meta();
			$current_meta['user_info']['email'] = $meta_value;

			$meta_key   = '_give_payment_meta';
			$meta_value = $current_meta;

		}

		$meta_value = apply_filters( "give_update_payment_meta_{$meta_key}", $meta_value, $this->ID );

		return update_post_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * When a payment is set to a status of 'refunded' process the necessary actions to reduce stats
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return void
	 */
	private function process_refund() {
		$process_refund = true;

		// If the payment was not in publish or revoked status, don't decrement stats as they were never incremented.
		if ( 'publish' != $this->old_status || 'refunded' != $this->status ) {
			$process_refund = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments.
		$process_refund = apply_filters( 'give_should_process_refund', $process_refund, $this );

		if ( false === $process_refund ) {
			return;
		}

		/**
		 * Fires before refunding payment.
		 *
		 * @since 1.5
		 *
		 * @param Give_Payment $this Payment object.
		 */
		do_action( 'give_pre_refund_payment', $this );

		$decrease_store_earnings = apply_filters( 'give_decrease_store_earnings_on_refund', true, $this );
		$decrease_customer_value = apply_filters( 'give_decrease_customer_value_on_refund', true, $this );
		$decrease_purchase_count = apply_filters( 'give_decrease_customer_purchase_count_on_refund', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );
		$this->delete_sales_logs();

		// Clear the This Month earnings (this_monththis_month is NOT a typo).
		delete_transient( md5( 'give_earnings_this_monththis_month' ) );

		/**
		 * Fires after refunding payment.
		 *
		 * @since 1.5
		 *
		 * @param Give_Payment $this Payment object.
		 */
		do_action( 'give_post_refund_payment', $this );
	}

	/**
	 * Process when a payment is set to failed
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return void
	 */
	private function process_failure() {

	}

	/**
	 * Process when a payment moves to pending
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return void
	 */
	private function process_pending() {
		$process_pending = true;

		// If the payment was not in publish or revoked status, don't decrement stats as they were never incremented.
		if ( 'publish' != $this->old_status || 'pending' != $this->status ) {
			$process_pending = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments.
		$process_pending = apply_filters( 'give_should_process_pending', $process_pending, $this );

		if ( false === $process_pending ) {
			return;
		}

		$decrease_store_earnings = apply_filters( 'give_decrease_store_earnings_on_pending', true, $this );
		$decrease_customer_value = apply_filters( 'give_decrease_customer_value_on_pending', true, $this );
		$decrease_purchase_count = apply_filters( 'give_decrease_customer_purchase_count_on_pending', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );
		$this->delete_sales_logs();

		$this->completed_date = false;
		$this->update_meta( '_give_completed_date', '' );

		// Clear the This Month earnings (this_monththis_month is NOT a typo).
		delete_transient( md5( 'give_earnings_this_monththis_month' ) );
	}

	/**
	 * Process when a payment moves to cancelled
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return void
	 */
	private function process_cancelled() {
		$process_cancelled = true;

		// If the payment was not in publish or revoked status, don't decrement stats as they were never incremented.
		if ( 'publish' != $this->old_status || 'cancelled' != $this->status ) {
			$process_cancelled = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments.
		$process_cancelled = apply_filters( 'give_should_process_cancelled', $process_cancelled, $this );

		if ( false === $process_cancelled ) {
			return;
		}

		$decrease_store_earnings = apply_filters( 'give_decrease_store_earnings_on_cancelled', true, $this );
		$decrease_customer_value = apply_filters( 'give_decrease_customer_value_on_cancelled', true, $this );
		$decrease_purchase_count = apply_filters( 'give_decrease_customer_purchase_count_on_cancelled', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );
		$this->delete_sales_logs();

		$this->completed_date = false;
		$this->update_meta( '_give_completed_date', '' );

		// Clear the This Month earnings (this_monththis_month is NOT a typo).
		delete_transient( md5( 'give_earnings_this_monththis_month' ) );
	}

	/**
	 * Process when a payment moves to revoked
	 *
	 * @since  1.5
	 * @return void
	 */
	private function process_revoked() {
		$process_revoked = true;

		// If the payment was not in publish, don't decrement stats as they were never incremented.
		if ( 'publish' != $this->old_status || 'revoked' != $this->status ) {
			$process_revoked = false;
		}

		// Allow extensions to filter for their own payment types, Example: Recurring Payments.
		$process_revoked = apply_filters( 'give_should_process_revoked', $process_revoked, $this );

		if ( false === $process_revoked ) {
			return;
		}

		$decrease_store_earnings = apply_filters( 'give_decrease_store_earnings_on_revoked', true, $this );
		$decrease_customer_value = apply_filters( 'give_decrease_customer_value_on_revoked', true, $this );
		$decrease_purchase_count = apply_filters( 'give_decrease_customer_purchase_count_on_revoked', true, $this );

		$this->maybe_alter_stats( $decrease_store_earnings, $decrease_customer_value, $decrease_purchase_count );
		$this->delete_sales_logs();

		$this->completed_date = false;
		$this->update_meta( '_give_completed_date', '' );

		// Clear the This Month earnings (this_monththis_month is NOT a typo).
		delete_transient( md5( 'give_earnings_this_monththis_month' ) );
	}

	/**
	 * Used during the process of moving to refunded or pending, to decrement stats
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  bool $alter_store_earnings          If the method should alter the store earnings
	 * @param  bool $alter_customer_value          If the method should reduce the customer value
	 * @param  bool $alter_customer_purchase_count If the method should reduce the customer's purchase count
	 *
	 * @return void
	 */
	private function maybe_alter_stats( $alter_store_earnings, $alter_customer_value, $alter_customer_purchase_count ) {

		give_undo_purchase( false, $this->ID );

		// Decrease store earnings.
		if ( true === $alter_store_earnings ) {
			give_decrease_total_earnings( $this->total );
		}

		// Decrement the stats for the customer.
		if ( ! empty( $this->customer_id ) ) {

			$customer = new Give_Customer( $this->customer_id );

			if ( true === $alter_customer_value ) {
				$customer->decrease_value( $this->total );
			}

			if ( true === $alter_customer_purchase_count ) {
				$customer->decrease_purchase_count();
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
		global $give_logs;

		// Remove related sale log entries.
		$give_logs->delete_logs(
			null,
			'sale',
			array(
				array(
					'key'   => '_give_log_payment_id',
					'value' => $this->ID,
				),
			)
		);
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

		if ( 'pending' == $payment->post_status || 'preapproved' == $payment->post_status ) {
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
	 * Setup the payment total
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return float The payment total
	 */
	private function setup_total() {
		$amount = $this->get_meta( '_give_payment_total', true );

		if ( empty( $amount ) && '0.00' != $amount ) {
			$meta = $this->get_meta( '_give_payment_meta', true );
			$meta = maybe_unserialize( $meta );

			if ( isset( $meta['amount'] ) ) {
				$amount = $meta['amount'];
			}
		}


		return round( floatval( $amount ), give_currency_decimal_filter() );
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
	 * Setup the payment fees
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return float The fees total for the payment
	 */
	private function setup_fees_total() {
		$fees_total = (float) 0.00;

		$payment_fees = isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();
		if ( ! empty( $payment_fees ) ) {
			foreach ( $payment_fees as $fee ) {
				$fees_total += (float) $fee['amount'];
			}
		}

		return $fees_total;

	}

	/**
	 * Setup the currency code
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The currency for the payment
	 */
	private function setup_currency() {
		$currency = isset( $this->payment_meta['currency'] ) ? $this->payment_meta['currency'] : apply_filters( 'give_payment_currency_default', give_get_currency(), $this );

		return $currency;
	}

	/**
	 * Setup any fees associated with the payment
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array The Fees
	 */
	private function setup_fees() {
		$payment_fees = isset( $this->payment_meta['fees'] ) ? $this->payment_meta['fees'] : array();

		return $payment_fees;
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
	 * @access private
	 *
	 * @return string The IP address for the payment
	 */
	private function setup_ip() {
		$ip = $this->get_meta( '_give_payment_user_ip', true );

		return $ip;
	}

	/**
	 * Setup the customer ID
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int The Customer ID
	 */
	private function setup_customer_id() {
		$customer_id = $this->get_meta( '_give_payment_customer_id', true );

		return $customer_id;
	}

	/**
	 * Setup the User ID associated with the donation
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int The User ID
	 */
	private function setup_user_id() {
		$user_id = $this->get_meta( '_give_payment_user_id', true );

		return $user_id;
	}

	/**
	 * Setup the email address for the donation
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The email address for the payment
	 */
	private function setup_email() {
		$email = $this->get_meta( '_give_payment_user_email', true );

		if ( empty( $email ) ) {
			$email = Give()->customers->get_column( 'email', $this->customer_id );
		}

		return $email;
	}

	/**
	 * Setup the user info
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array The user info associated with the payment
	 */
	private function setup_user_info() {
		$defaults = array(
			'first_name' => $this->first_name,
			'last_name'  => $this->last_name,
		);

		$user_info = isset( $this->payment_meta['user_info'] ) ? maybe_unserialize( $this->payment_meta['user_info'] ) : array();
		$user_info = wp_parse_args( $user_info, $defaults );

		if ( empty( $user_info ) ) {
			// Get the customer, but only if it's been created.
			$customer = new Give_Customer( $this->customer_id );

			if ( $customer->id > 0 ) {
				$name      = explode( ' ', $customer->name, 2 );
				$user_info = array(
					'first_name' => $name[0],
					'last_name'  => $name[1],
					'email'      => $customer->email,
					'discount'   => 'none',
				);
			}
		} else {
			// Get the customer, but only if it's been created.
			$customer = new Give_Customer( $this->customer_id );
			if ( $customer->id > 0 ) {
				foreach ( $user_info as $key => $value ) {
					if ( ! empty( $value ) ) {
						continue;
					}

					switch ( $key ) {
						case 'first_name':
							$name = explode( ' ', $customer->name, 2 );

							$user_info[ $key ] = $name[0];
							break;

						case 'last_name':
							$name      = explode( ' ', $customer->name, 2 );
							$last_name = ! empty( $name[1] ) ? $name[1] : '';

							$user_info[ $key ] = $last_name;
							break;

						case 'email':
							$user_info[ $key ] = $customer->email;
							break;
					}
				}
			}
		}

		return $user_info;

	}

	/**
	 * Setup the Address for the payment
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array The Address information for the payment
	 */
	private function setup_address() {

		$address = ! empty( $this->payment_meta['user_info']['address'] ) ? $this->payment_meta['user_info']['address'] : array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'country' => '',
			'state'   => '',
			'zip'     => '',
		);

		return $address;
	}

	/**
	 * Setup the form title
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The Form Title
	 */
	private function setup_form_title() {

		$form_id = $this->get_meta( '_give_payment_form_title', true );

		return $form_id;
	}

	/**
	 * Setup the form ID
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
	 * Setup the price ID
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int The Form Price ID
	 */
	private function setup_price_id() {
		$price_id = $this->get_meta( '_give_payment_price_id', true );

		return $price_id;
	}

	/**
	 * Setup the payment key
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string The Payment Key
	 */
	private function setup_payment_key() {
		$key = $this->get_meta( '_give_payment_purchase_key', true );

		return $key;
	}

	/**
	 * Setup the payment number
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int|string Integer by default, or string if sequential order numbers is enabled
	 */
	private function setup_payment_number() {
		$number = $this->ID;

		if ( give_get_option( 'enable_sequential' ) ) {

			$number = $this->get_meta( '_give_payment_number', true );

			if ( ! $number ) {

				$number = $this->ID;

			}
		}

		return $number;
	}

	/**
	 * Converts this object into an array for special cases
	 *
	 * @access public
	 *
	 * @return array The payment object as an array
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
	 * Retrieve payment completion date
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Date payment was completed
	 */
	private function get_completed_date() {
		return apply_filters( 'give_payment_completed_date', $this->completed_date, $this->ID, $this );
	}

	/**
	 * Retrieve payment subtotal
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return float Payment subtotal
	 */
	private function get_subtotal() {
		return apply_filters( 'give_get_payment_subtotal', $this->subtotal, $this->ID, $this );
	}

	/**
	 * Retrieve payment currency
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment currency code
	 */
	private function get_currency() {
		return apply_filters( 'give_payment_currency_code', $this->currency, $this->ID, $this );
	}

	/**
	 * Retrieve payment gateway
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Gateway used
	 */
	private function get_gateway() {
		return apply_filters( 'give_payment_gateway', $this->gateway, $this->ID, $this );
	}

	/**
	 * Retrieve donation ID
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Donation ID from merchant processor
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
	 * Retrieve payment customer ID
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int Payment customer ID
	 */
	private function get_customer_id() {
		return apply_filters( 'give_payment_customer_id', $this->customer_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment user ID
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return int Payment user ID
	 */
	private function get_user_id() {
		return apply_filters( 'give_payment_user_id', $this->user_id, $this->ID, $this );
	}

	/**
	 * Retrieve payment email
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment customer email
	 */
	private function get_email() {
		return apply_filters( 'give_payment_user_email', $this->email, $this->ID, $this );
	}

	/**
	 * Retrieve payment user info
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array Payment user info
	 */
	private function get_user_info() {
		return apply_filters( 'give_payment_meta_user_info', $this->user_info, $this->ID, $this );
	}

	/**
	 * Retrieve payment billing address
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return array Payment billing address
	 */
	private function get_address() {
		return apply_filters( 'give_payment_address', $this->address, $this->ID, $this );
	}

	/**
	 * Retrieve payment key
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @return string Payment key
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

}
