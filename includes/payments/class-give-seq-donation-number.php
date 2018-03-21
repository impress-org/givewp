<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Seq_Donation_Number {
	/**
	 * Instance.
	 *
	 * @since  2.1.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.1.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.1.0
	 * @access static
	 * @return Give_Seq_Donation_Number
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin, bailing if any required conditions are not met,
	 * including minimum WooCommerce version
	 *
	 * @since 2.1.0
	 */
	public function init() {
		if ( give_is_setting_enabled( give_get_option( 'sequential-donation_status', 'disabled' ) ) ) {
			add_action( 'wp_insert_post', array( $this, '__save_donation_title' ), 10, 3 );
		}
	}

	/**
	 * Set serialize donation number as donation title.
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int     $donation_id
	 * @param WP_Post $donation_post_data
	 * @param bool    $existing_donation_updated
	 *
	 * @return void
	 */
	public function __save_donation_title( $donation_id, $donation_post_data, $existing_donation_updated ) {
		// Bailout
		if ( $existing_donation_updated ) {
			return;
		}

		$serial_number = $this->set_donation_number( $donation_id );

		$serial_code = $this->__set_number_padding( $serial_number );

		// Add prefix.
		if ( $prefix = give_get_option( 'sequential-donation_number_prefix', '' ) ) {
			$serial_code = $prefix . $serial_code;
		}

		// Add suffix.
		if ( $suffix = give_get_option( 'sequential-donation_number_suffix', '' ) ) {
			$serial_code = $serial_code . $suffix;
		}

		$serial_code = give_time_do_tags( $serial_code );

		try {
			/* @var WP_Error $wp_error */
			$wp_error = wp_update_post(
				array(
					'ID'         => $donation_id,
					'post_title' => $serial_code
				)
			);

			if ( is_wp_error( $wp_error ) ) {
				throw new Exception( $wp_error->get_error_message() );
			}
		} catch ( Exception $e ) {
			error_log( "Give caught exception: {$e->getMessage()}" );
		}
	}

	/**
	 * Set donation number
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int $donation_id
	 *
	 * @return int
	 */
	public function set_donation_number( $donation_id ) {
		// Customize sequential donation number starting point if needed.
		if (
			get_option( '_give_reset_sequential_number' ) &&
			( $number = give_get_option( 'sequential-donation_number', 0 ) )
		) {
			delete_option( '_give_reset_sequential_number' );

			return Give()->sequential_donation_db->insert( array(
				'id'         => $number,
				'payment_id' => $donation_id
			) );
		}

		return Give()->sequential_donation_db->insert( array(
			'payment_id' => $donation_id
		) );
	}

	/**
	 * Set number padding in serial code.
	 *
	 * @since
	 * @access private
	 *
	 * @param $serial_number
	 *
	 * @return string
	 */
	private function __set_number_padding( $serial_number ) {
		if ( $number_padding = give_get_option( 'sequential-donation_number_padding', 0 ) ) {
			$serial_number = str_pad( $serial_number, $number_padding, '0', STR_PAD_LEFT );
		}

		return $serial_number;
	}

	/**
	 * Get donation number serial code
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int|Give_Payment $donation
	 * @param array            $args
	 *
	 * @return string
	 */
	public function get_serial_code( $donation, $args = array() ) {
		$donation = $donation instanceof Give_Payment ? $donation : new Give_Payment( $donation );

		// Bailout.
		if ( empty( $donation->ID ) ) {
			return '';
		}

		// Set default params.
		$args = wp_parse_args(
			$args,
			array(
				'with_hash' => false,
				'default'   => true
			)
		);

		$serial_code = $args['default'] ? $donation->ID : '';

		if ( $donation_number = $this->get_serial_number( $donation->ID ) ) {
			$serial_code = get_the_title( $donation->ID );
		}

		$serial_code = $args['with_hash'] ? "#{$serial_code}" : $serial_code;

		/**
		 * Filter the donation serial code
		 *
		 * @since 2.1.0
		 */
		return apply_filters( 'give_get_donation_serial_code', $serial_code, $donation, $args, $donation_number );
	}

	/**
	 * Get serial number
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int $donation_id
	 *
	 * @return string
	 */
	public function get_serial_number( $donation_id ) {
		return Give()->sequential_donation_db->get_column_by( 'id', 'payment_id', $donation_id );
	}


	/**
	 * Get donation id with donation number or serial code
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param string $donation_number_or_serial_code\
	 */
	public function get_donation_id( $donation_number_or_serial_code ) {}
}

// @todo: add post_title support in Give_Payment
// @todo: resolve caching issue: donation listing is not updating when updating donation
// @todo: test custom sequential donation number.
// @todo update logic for __set_number_padding
