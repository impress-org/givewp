<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Sequential_Donation_Number {
	/**
	 * Instance.
	 *
	 * @since  2.1.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Donation tile prefix
	 *
	 * @since 2.1.0
	 * @var string
	 */
	private $donation_title_prefix = 'give-donation-';

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
	 * @access public
	 * @return Give_Sequential_Donation_Number
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
		add_action( 'wp_insert_post', array( $this, '__save_donation_title' ), 10, 3 );
		add_action( 'after_delete_post', array( $this, '__remove_serial_number' ), 10, 1 );
	}

	/**
	 * Set serialize donation number as donation title.
	 * Note: only for internal use
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int     $donation_id
	 * @param WP_Post $post
	 * @param bool    $existing_donation_updated
	 *
	 * @return void
	 */
	public function __save_donation_title( $donation_id, $post, $existing_donation_updated ) {
		// Bailout
		if (
			! give_is_setting_enabled( give_get_option( 'sequential-ordering_status', 'disabled' ) )
			|| $existing_donation_updated
			|| 'give_payment' !== $post->post_type
		) {
			return;
		}

		$serial_number = $this->__set_donation_number( $donation_id );
		$serial_code   = $this->set_number_padding( $serial_number );

		// Add prefix.
		if ( $prefix = give_get_option( 'sequential-ordering_number_prefix', '' ) ) {
			$serial_code = $prefix . $serial_code;
		}

		// Add suffix.
		if ( $suffix = give_get_option( 'sequential-ordering_number_suffix', '' ) ) {
			$serial_code = $serial_code . $suffix;
		}

		/**
		 * Filter the donation number
		 *
		 * @since 2.1.0
		 */
		$serial_code = apply_filters(
			'give_set_sequential_donation_title',
			give_time_do_tags( $serial_code ),
			$donation_id,
			$post,
			$existing_donation_updated,
			array(
				$serial_number,
				$prefix,
				$suffix
			)
		);

		try {
			/* @var WP_Error $wp_error */
			$wp_error = wp_update_post(
				array(
					'ID'         => $donation_id,
					'post_name'  => "{$this->donation_title_prefix}-{$serial_number}",
					'post_title' => trim( $serial_code )
				)
			);

			if ( is_wp_error( $wp_error ) ) {
				throw new Exception( $wp_error->get_error_message() );
			}

			give_update_option( 'sequential-ordering_number', ( $serial_number + 1 ) );
		} catch ( Exception $e ) {
			error_log( "GiveWP caught exception: {$e->getMessage()}" );
		}
	}

	/**
	 * Set donation number
	 * Note: only for internal use
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int $donation_id
	 *
	 * @return int
	 */
	public function __set_donation_number( $donation_id ) {
		$table_data = array(
			'payment_id' => $donation_id
		);

		// Customize sequential donation number starting point if needed.
		if (
			get_option( '_give_reset_sequential_number' ) &&
			( $number = give_get_option( 'sequential-ordering_number', 0 ) )
		) {
			if ( Give()->sequential_donation_db->get_id_auto_increment_val() <= $number ) {
				delete_option( '_give_reset_sequential_number' );
			}

			$table_data['id'] = $number;
		}


		/**
		 * Filter the donation number
		 *
		 * @since 2.1
		 */
		return apply_filters(
			'give_set_sequential_donation_number',
			Give()->sequential_donation_db->insert( $table_data ),
			$table_data
		);
	}


	/**
	 * Remove sequential donation data
	 * Note: only internal use.
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param $donation_id
	 *
	 * @return bool
	 */
	public function __remove_serial_number( $donation_id ) {
		return Give()->sequential_donation_db->delete( $this->get_serial_number( $donation_id ) );
	}

	/**
	 * Set number padding in serial code.
	 *
	 * @since
	 * @access public
	 *
	 * @param $serial_number
	 *
	 * @return string
	 */
	public function set_number_padding( $serial_number ) {
		if ( $number_padding = give_get_option( 'sequential-ordering_number_padding', 0 ) ) {
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
	 * @param int|Give_Payment|WP_Post $donation
	 * @param array            $args
	 *
	 * @return string
	 */
	public function get_serial_code( $donation, $args = array() ) {
		// Get id from object.
		if( ! is_numeric( $donation ) ) {
			if( $donation instanceof Give_Payment ) {
				$donation = $donation->ID;
			} elseif ( $donation instanceof WP_Post ){
				$donation = $donation->ID;
			}
		}

		// Set default params.
		$args = wp_parse_args(
			$args,
			array(
				'with_hash' => false,
				'default'   => true
			)
		);

		$serial_code = $args['default'] ? $donation : '';

		if ( $donation_number = $this->get_serial_number( $donation ) ) {
			$serial_code = get_the_title( $donation );
		}

		$serial_code = $args['with_hash'] ? "#{$serial_code}" : $serial_code;

		/**
		 * Filter the donation serial code
		 *
		 * @since 2.1.0
		 *
		 * @param string $serial_code
		 * @param string $donation Donation ID
		 * @param array $args
		 * @param string $donation_number
		 */
		return apply_filters( 'give_get_donation_serial_code', $serial_code, $donation, $args, $donation_number );
	}

	/**
	 * Get serial number
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param int $donation_id_or_serial_code
	 *
	 * @return string
	 */
	public function get_serial_number( $donation_id_or_serial_code ) {
		if ( is_numeric( $donation_id_or_serial_code ) ) {
			return Give()->sequential_donation_db->get_column_by( 'id', 'payment_id', $donation_id_or_serial_code );
		}

		return $this->get_serial_number( $this->get_donation_id( $donation_id_or_serial_code ) );
	}


	/**
	 * Get donation id with donation number or serial code
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @param string $donation_number_or_serial_code
	 *
	 * @return string
	 */
	public function get_donation_id( $donation_number_or_serial_code ) {
		global $wpdb;

		if ( is_numeric( $donation_number_or_serial_code ) ) {
			return Give()->sequential_donation_db->get_column_by(
				'payment_id',
				'id',
				$donation_number_or_serial_code
			);
		}

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title=%s
				",
				$donation_number_or_serial_code
			)
		);
	}

	/**
	 * Get maximum donation number
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_max_number() {
		global $wpdb;
		$table_name = Give()->sequential_donation_db->table_name;

		return absint(
			$wpdb->get_var(
				"
				SELECT ID
				FROM {$table_name}
				ORDER BY id DESC 
				LIMIT 1
				"
			)
		);
	}

	/**
	 * Get maximum donation id
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_max_donation_id() {
		global $wpdb;

		return absint(
			$wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_type=%s
					AND post_status=%s
					ORDER BY id DESC 
					LIMIT 1
					",
					'give_payment',
					'publish'
				)
			)
		);
	}

	/**
	 * Get maximum donation number
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_next_number() {
		$donation_id = $this->get_max_donation_id();
		$next_number = $this->get_max_number();

		if ( ! $this->get_serial_number( $donation_id ) ) {
			$next_number = $donation_id && ( $next_number < $donation_id ) ?
				$donation_id :
				$this->get_max_number();
		}

		return ( $next_number + 1 );
	}
}
