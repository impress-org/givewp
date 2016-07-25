<?php
/**
 * Donate Form Object
 *
 * @package     Give
 * @subpackage  Classes/Forms
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * Give_Donate_Form Class
 *
 * @since 1.0
 */
class Give_Donate_Form {

	/**
	 * The donation ID
	 *
	 * @since 1.0
	 */
	public $ID = 0;

	/**
	 * The donation price
	 *
	 * @since 1.0
	 */
	private $price;

	/**
	 * The minimum donation price
	 *
	 * @since 1.3.6
	 */
	private $minimum_price;

	/**
	 * The donation goal
	 *
	 * @since 1.0
	 */
	private $goal;

	/**
	 * The donation prices, if Price Levels are enabled
	 *
	 * @since 1.0
	 */
	private $prices;

	/**
	 * The form's sale count
	 *
	 * @since 1.0
	 */
	private $sales;

	/**
	 * The form's total earnings
	 *
	 * @since 1.0
	 */
	private $earnings;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * Give_Donate_Form constructor.
	 *
	 * @since 1.0
	 *
	 * @param bool $_id
	 * @param array $_args
	 */
	public function __construct( $_id = false, $_args = array() ) {


		$donation_form = WP_Post::get_instance( $_id );

		return $this->setup_donation_form( $donation_form );
	}

	/**
	 * Given the donation form data, let's set the variables
	 *
	 * @since  1.5
	 *
	 * @access private
	 *
	 * @param  WP_Post $donation_form WP_Post Object
	 *
	 * @return bool             If the setup was successful or not
	 */
	private function setup_donation_form( $donation_form ) {

		if ( ! is_object( $donation_form ) ) {
			return false;
		}

		if ( ! is_a( $donation_form, 'WP_Post' ) ) {
			return false;
		}

		if ( 'give_forms' !== $donation_form->post_type ) {
			return false;
		}

		foreach ( $donation_form as $key => $value ) {

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}

		}

		return true;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 1.0
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __get( $key ) {

		if ( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			/* translators: %s: property key */
			return new WP_Error( 'give-form-invalid-property', sprintf( esc_html__( 'Can\'t get property %s.', 'give' ), $key ) );

		}

	}


	/**
	 * Creates a donation form
	 *
	 * @since  1.5
	 *
	 * @param  array $data Array of attributes for a donation form
	 *
	 * @return mixed  false if data isn't passed and class not instantiated for creation, or New Form ID
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'give_forms',
			'post_status' => 'draft',
			'post_title'  => esc_html__( 'New Donation Form', 'give' )
		);

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a donation form is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'give_form_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$donation_form = WP_Post::get_instance( $id );

		/**
		 * Fired after a donation form is created
		 *
		 * @param int $id The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'give_form_post_create', $id, $args );

		return $this->setup_donation_form( $donation_form );

	}

	/**
	 * Retrieve the ID
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_ID() {

		return $this->ID;

	}

	/**
	 * Retrieve the donation form name
	 *
	 * @since 1.5
	 * @return string Name of the donation form
	 */
	public function get_name() {
		return get_the_title( $this->ID );
	}

	/**
	 * Retrieve the price
	 *
	 * @since 1.0
	 * @return float
	 */
	public function get_price() {

		if ( ! isset( $this->price ) ) {

			$this->price = get_post_meta( $this->ID, '_give_set_price', true );

			if ( $this->price ) {

				$this->price = give_sanitize_amount( $this->price );

			} else {

				$this->price = 0;

			}

		}

		/**
		 * Override the donation form set price.
		 *
		 * @since 1.0
		 *
		 * @param string $price The donation form price.
		 * @param string|int $id The form ID.
		 */
		return apply_filters( 'give_get_set_price', $this->price, $this->ID );
	}

	/**
	 * Retrieve the minimum price
	 *
	 * @since 1.3.6
	 * @return float
	 */
	public function get_minimum_price() {

		if ( ! isset( $this->minimum_price ) ) {

			$allow_custom_amount = get_post_meta( $this->ID, '_give_custom_amount', true );
			$this->minimum_price = get_post_meta( $this->ID, '_give_custom_amount_minimum', true );

			if ( $allow_custom_amount != 'no' && $this->minimum_price ) {

				$this->minimum_price = give_sanitize_amount( $this->minimum_price );

			} else {

				$this->minimum_price = 0;

			}

		}

		return apply_filters( 'give_get_set_minimum_price', $this->minimum_price, $this->ID );
	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_prices() {

		if ( ! isset( $this->prices ) ) {

			$this->prices = get_post_meta( $this->ID, '_give_donation_levels', true );

		}

		/**
		 * Override multi-level prices
		 *
		 * @since 1.0
		 *
		 * @param array $prices The array of mulit-level prices.
		 * @param int|string The ID of the form.
		 */
		return apply_filters( 'give_get_donation_levels', $this->prices, $this->ID );

	}

	/**
	 * Retrieve the goal
	 *
	 * @since 1.0
	 * @return float
	 */
	public function get_goal() {

		if ( ! isset( $this->goal ) ) {

			$this->goal = get_post_meta( $this->ID, '_give_set_goal', true );

			if ( $this->goal ) {

				$this->goal = give_sanitize_amount( $this->goal );

			} else {

				$this->goal = 0;

			}

		}

		return apply_filters( 'give_get_set_goal', $this->goal, $this->ID );

	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_single_price_mode() {

		$option = get_post_meta( $this->ID, '_give_price_options_mode', true );
		$ret    = 0;

		if ( empty( $option ) || $option === 'set' ) {
			$ret = 1;
		}
		
		/**
		 * Override the price mode for a donation when checking if is in single price mode.
		 *
		 * @since 1.0
		 *
		 * @param bool $ret Is donation form in single price mode?
		 * @param int|string The ID of the donation form.
		 */
		return (bool) apply_filters( 'give_single_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if custom price mode is enabled or disabled
	 *
	 * @since 1.6
	 * @return bool
	 */
	public function is_custom_price_mode() {

		$option = get_post_meta( $this->ID, '_give_custom_amount', true );
		$ret    = 0;

		if ( $option === 'yes' ) {
			$ret = 1;
		}

		/**
		 * Override the price mode for a donation when checking if is in custom price mode.
		 *
		 * @since 1.6
		 *
		 * @param bool $ret Is donation form in custom price mode?
		 * @param int|string The ID of the donation form.
		 */
		return (bool) apply_filters( 'give_custom_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Has Variable Prices
	 *
	 * Determine if the donation form has variable prices enabled
	 *
	 * @since       1.0
	 * @return bool
	 */
	public function has_variable_prices() {

		$option = get_post_meta( $this->ID, '_give_price_option', true );
		$ret    = 0;

		if ( $option === 'multi' ) {
			$ret = 1;
		}

		/**
		 * Filter: Override whether the donation form has variables prices.
		 *
		 * @param bool $ret Does donation form have variable prices?
		 * @param int|string The ID of the donation form.
		 */
		return (bool) apply_filters( 'give_has_variable_prices', $ret, $this->ID );

	}

	/**
	 * Retrieve the donation form type, set or multi-level
	 *
	 * @since 1.5
	 * @return string Type of donation form, either 'set' or 'multi'
	 */
	public function get_type() {

		if ( ! isset( $this->type ) ) {

			$this->type = get_post_meta( $this->ID, '_give_price_option', true );

			if ( empty( $this->type ) ) {
				$this->type = 'set';
			}

		}

		return apply_filters( 'give_get_form_type', $this->type, $this->ID );

	}

    /**
     * Get if form type set or not.
     *
     * @since 1.6
     * @return bool true if form type is 'multi' and false otherwise.
     */
    public function is_set_type_donation_form() {
        $form_type = $this->get_type();

        return ( 'set' === $form_type ? true : false );

    }

    /**
     * Get if form type multi or not.
     *
     * @since 1.6
     * @return bool true if form type is 'multi' and false otherwise.
     */
    public function is_multi_type_donation_form() {
        $form_type = $this->get_type();

        return ( 'multi' === $form_type ? true : false );

    }

	/**
	 * Retrieve the sale count for the donation form
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_sales() {

		if ( ! isset( $this->sales ) ) {

			if ( '' == get_post_meta( $this->ID, '_give_form_sales', true ) ) {
				add_post_meta( $this->ID, '_give_form_sales', 0 );
			} // End if

			$this->sales = get_post_meta( $this->ID, '_give_form_sales', true );

			if ( $this->sales < 0 ) {
				// Never let sales be less than zero
				$this->sales = 0;
			}

		}

		return $this->sales;

	}

	/**
	 * Increment the sale count by one
	 *
	 * @since 1.0
	 *
	 * @param int $quantity The quantity to increase the donations by
	 *
	 * @return int|false  New number of total sales
	 */
	public function increase_sales( $quantity = 1 ) {

		$sales       = give_get_form_sales_stats( $this->ID );
		$quantity    = absint( $quantity );
		$total_sales = $sales + $quantity;

		if ( $this->update_meta( '_give_form_sales', $total_sales ) ) {

			$this->sales = $total_sales;

			return $this->sales;

		}

		return false;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since 1.0
	 *
	 * @param int $quantity The quantity to decrease by
	 *
	 * @return int|false  New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		$sales = give_get_form_sales_stats( $this->ID );

		// Only decrease if not already zero
		if ( $sales > 0 ) {

			$quantity    = absint( $quantity );
			$total_sales = $sales - $quantity;

			if ( $this->update_meta( '_give_form_sales', $total_sales ) ) {

				$this->sales = $sales;

				return $sales;

			}

		}

		return false;

	}

	/**
	 * Retrieve the total earnings for the form
	 *
	 * @since 1.0
	 * @return float
	 */
	public function get_earnings() {

		if ( ! isset( $this->earnings ) ) {

			if ( '' == get_post_meta( $this->ID, '_give_form_earnings', true ) ) {
				add_post_meta( $this->ID, '_give_form_earnings', 0 );
			}

			$this->earnings = get_post_meta( $this->ID, '_give_form_earnings', true );

			if ( $this->earnings < 0 ) {
				// Never let earnings be less than zero
				$this->earnings = 0;
			}

		}

		return $this->earnings;

	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since 1.0
	 * 
	 * @param int $amount Amount of donation
	 * 
	 * @return float|false
	 */
	public function increase_earnings( $amount = 0 ) {

		$earnings   = give_get_form_earnings_stats( $this->ID );
		$new_amount = $earnings + (float) $amount;

		if ( $this->update_meta( '_give_form_earnings', $new_amount ) ) {

			$this->earnings = $new_amount;

			return $this->earnings;

		}

		return false;

	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since 1.0
	 * @return float|false
	 */
	public function decrease_earnings( $amount ) {

		$earnings = give_get_form_earnings_stats( $this->ID );

		if ( $earnings > 0 ) {
			// Only decrease if greater than zero
			$new_amount = $earnings - (float) $amount;


			if ( $this->update_meta( '_give_form_earnings', $new_amount ) ) {

				$this->earnings = $new_amount;

				return $this->earnings;

			}

		}

		return false;

	}

	/**
	 * Determine if the donation is free or if the given price ID is free
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function is_free( $price_id = false ) {

		$is_free          = false;
		$variable_pricing = give_has_variable_prices( $this->ID );

		if ( $variable_pricing && ! is_null( $price_id ) && $price_id !== false ) {
			$price = give_get_price_option_amount( $this->ID, $price_id );
		} elseif ( ! $variable_pricing ) {
			$price = get_post_meta( $this->ID, '_give_set_price', true );
		}

		if ( isset( $price ) && (float) $price == 0 ) {
			$is_free = true;
		}

		return (bool) apply_filters( 'give_is_free_donation', $is_free, $this->ID, $price_id );

	}


	/**
	 * Determine if donation form closed or not
	 * Form will be close if:
	 *  a. form has fixed goal
	 *  b. close form when goal achieved cmb2 setting is set to 'Yes'
	 *  c. goal has been achieved
	 *
	 * @since 1.4.5
	 * @return bool
	 */
	public function is_close_donation_form() {
		return (
				'yes' === get_post_meta( $this->ID, '_give_goal_option', true ) )
				&& ( 'yes' === get_post_meta( $this->ID, '_give_close_form_when_goal_achieved', true ) )
				&& ( $this->get_goal() <= $this->get_earnings()
		);
	}


	/**
	 * Updates a single meta entry for the donation form
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  string               $meta_key   The meta_key to update
	 * @param  string|array|object  $meta_value The value to put into the meta
	 *
	 * @return bool                             The result of the update query
	 */
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		/* @var WPDB $wpdb */
		global $wpdb;

		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize( $meta_value );

		if ( is_numeric( $meta_value ) ) {
			$value_type = is_float( $meta_value ) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key );

		if ( $wpdb->query( $sql ) ) {

			clean_post_cache( $this->ID );

			return true;

		}

		return false;
	}


}
