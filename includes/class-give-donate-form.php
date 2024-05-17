<?php
/**
 * Donate Form
 *
 * @package     Give
 * @subpackage  Classes/Give_Donate_Form
 * @copyright   Copyright (c) 2015, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donate_Form Class.
 *
 * This class handles donation forms.
 *
 * @since 1.0
 *
 * @property $price
 * @property $minimum_price
 * @property $maximum_price
 * @property $prices
 * @property $goal
 * @property $sales
 * @property $earnings
 * @property $post_type
 */
#[\AllowDynamicProperties]
class Give_Donate_Form {

	/**
	 * The donation ID.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $ID = 0;

	/**
	 * The donation price.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    float
	 */
	private $price;

	/**
	 * The minimum donation price.
	 *
	 * @since  1.3.6
	 * @access private
	 *
	 * @var    float
	 */
	private $minimum_price;

	/**
	 * The maximum donation price.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @var    float
	 */
	private $maximum_price;

	/**
	 * The donation prices, if Price Levels are enabled.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    array
	 */
	private $prices;

	/**
	 * The donation goal.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    float
	 */
	private $goal;

	/**
	 * The form's sale count.
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    int
	 */
	private $sales;

	/**
	 * The form's total earnings
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @var    float
	 */
	private $earnings;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */

	/**
	 * The post author
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $post_author = 0;

	/**
	 * The post date
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_date = '0000-00-00 00:00:00';

	/**
	 * The post GTM date
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_date_gmt = '0000-00-00 00:00:00';

	/**
	 * The post content
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_content = '';

	/**
	 * The post title
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_title = '';

	/**
	 * The post excerpt
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_excerpt = '';

	/**
	 * The post status
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_status = 'publish';

	/**
	 * The comment status
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $comment_status = 'open';

	/**
	 * The ping status
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $ping_status = 'open';

	/**
	 * The post password
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_password = '';

	/**
	 * The post name
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_name = '';

	/**
	 * Ping
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $to_ping = '';

	/**
	 * Pinged
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $pinged = '';

	/**
	 * The post modified date
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_modified = '0000-00-00 00:00:00';

	/**
	 * The post modified GTM date
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_modified_gmt = '0000-00-00 00:00:00';

	/**
	 * The post filtered content
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_content_filtered = '';

	/**
	 * The post parent
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $post_parent = 0;

	/**
	 * The post GUID
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $guid = '';

	/**
	 * The menu order
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $menu_order = 0;

	/**
	 * The mime type0
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $post_mime_type = '';

	/**
	 * The comment count
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    int
	 */
	public $comment_count = 0;

	/**
	 * Filtered
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $filter;

	/**
	 * Class Constructor
	 *
	 * Set up the Give Donate Form Class.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int|bool $_id   Post id. Default is false.
	 * @param  array    $_args Arguments passed.
	 */
	public function __construct( $_id = false, $_args = [] ) {
		$donation_form = WP_Post::get_instance( $_id );

		$this->setup_donation_form( $donation_form );
	}

	/**
	 * Given the donation form data, let's set the variables
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  WP_Post $donation_form WP_Post Object for the donation form.
	 *
	 * @return bool                   If the setup was successful or not.
	 */
	private function setup_donation_form( $donation_form ) {

		// Bailout.
		if (
			! ( $donation_form instanceof WP_Post )
			|| 'give_forms' !== $donation_form->post_type
		) {
			return false;
		}

		Give_Forms_Query::update_meta_cache( [ $donation_form->ID ] );

		foreach ( $donation_form as $key => $value ) {
			$this->$key = $value;
		}

		return $donation_form->ID;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( method_exists( $this, "get_{$key}" ) ) {
			return $this->{"get_{$key}"}();
		}

		/* translators: %s: property key */
		return new WP_Error( 'give-form-invalid-property', sprintf( esc_html__( 'Can\'t get property %s.', 'give' ), $key ) );

	}

	/**
	 * Creates a donation form
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @param  array $data Array of attributes for a donation form.
	 *
	 * @return bool|int    False if data isn't passed and class not instantiated for creation, or New Form ID.
	 */
	public function create( $data = [] ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = [
			'post_type'   => 'give_forms',
			'post_status' => 'draft',
			'post_title'  => __( 'New Donation Form', 'give' ),
		];

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
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'give_form_post_create', $id, $args );

		return $this->setup_donation_form( $donation_form );

	}

	/**
	 * Retrieve the ID
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return int    Donation form ID.
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * Retrieve the donation form name
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @return string Donation form name.
	 */
	public function get_name() {
		return get_the_title( $this->ID );
	}

	/**
	 * Retrieve the price
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return float  Price.
	 */
	public function get_price() {

		if ( ! isset( $this->price ) ) {

			$this->price = give_maybe_sanitize_amount(
				give_get_meta(
					$this->ID,
					'_give_set_price',
					true
				)
			);

			if ( ! $this->price ) {
				$this->price = 0;
			}
		}

		/**
		 * Override the donation form set price.
		 *
		 * @since 1.0
		 *
		 * @param string     $price The donation form price.
		 * @param string|int $id    The form ID.
		 */
		return apply_filters( 'give_get_set_price', $this->price, $this->ID );
	}

	/**
	 * Retrieve the minimum price.
	 *
	 * @since  1.3.6
	 * @access public
	 *
	 * @return float  Minimum price.
	 */
	public function get_minimum_price() {

		if ( ! isset( $this->minimum_price ) ) {

			$this->minimum_price = give_get_meta( $this->ID, '_give_custom_amount_range_minimum', true );

			// Give backward < 2.1
			if ( empty( $this->minimum_price ) ) {
				$this->minimum_price = give_get_meta( $this->ID, '_give_custom_amount_minimum', true );
			}

			if ( ! $this->is_custom_price_mode() ) {
				$this->minimum_price = give_get_lowest_price_option( $this->ID );
			}
		}

		return apply_filters( 'give_get_set_minimum_price', $this->minimum_price, $this->ID );
	}

	/**
	 * Retrieve the maximum price.
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @return float  Maximum price.
	 */
	public function get_maximum_price() {

		if ( ! isset( $this->maximum_price ) ) {
			$this->maximum_price = give_get_meta( $this->ID, '_give_custom_amount_range_maximum', true, 999999.99 );

			if ( ! $this->is_custom_price_mode() ) {
				$this->maximum_price = give_get_highest_price_option( $this->ID );
			}
		}

		return apply_filters( 'give_get_set_maximum_price', $this->maximum_price, $this->ID );
	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Variable prices.
	 */
	public function get_prices() {

		if ( ! isset( $this->prices ) ) {

			$this->prices = give_get_meta( $this->ID, '_give_donation_levels', true );

		}

		/**
		 * Override multi-level prices
		 *
		 * @since 1.0
		 *
		 * @param array      $prices The array of mulit-level prices.
		 * @param int|string $ID     The ID of the form.
		 */
		return apply_filters( 'give_get_donation_levels', $this->prices, $this->ID );

	}

	/**
	 * Get donation form level info
	 *
	 * @since  2.0.6
	 * @access public
	 *
	 * @param string $price_id
	 *
	 * @return array|null
	 */
	public function get_level_info( $price_id ) {
		$level_info = [];

		// Bailout.
		if ( 'multi' !== $this->get_type() ) {
			return null;
		} elseif ( ! ( $levels = $this->get_prices() ) ) {
			return $level_info;
		}

		foreach ( $levels as $level ) {
			if ( $price_id === $level['_give_id']['level_id'] ) {
				$level_info = $level;
				break;
			}
		}

		return $level_info;
	}


	/**
	 * Retrieve the goal
	 *
	 * @since  1.0
     * @since 2.19.0 Set default goal value to zero to prevent fatal error on PHP8.0.
	 * @access public
	 *
	 * @return float  Goal.
	 */
	public function get_goal() {

		if ( ! isset( $this->goal ) ) {

			$goal_format = give_get_form_goal_format( $this->ID );

			if ( ! $this->has_goal() ) {
				$this->goal = '';

			} elseif ( 'donation' === $goal_format ) {
				$this->goal = give_get_meta( $this->ID, '_give_number_of_donation_goal', true );

			} elseif ( 'donors' === $goal_format ) {
				$this->goal = give_get_meta( $this->ID, '_give_number_of_donor_goal', true );

			} elseif ( in_array( $goal_format, [ 'amount', 'percentage' ] ) ) {
				$this->goal = give_get_meta( $this->ID, '_give_set_goal', true );
			}
		}

		return apply_filters( 'give_get_set_goal', $this->goal ?: 0, $this->ID );

	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_single_price_mode() {

		$option = give_get_meta( $this->ID, '_give_price_option', true );
		$ret    = 0;

		if ( empty( $option ) || $option === 'set' ) {
			$ret = 1;
		}

		/**
		 * Override the price mode for a donation when checking if is in single price mode.
		 *
		 * @since 1.0
		 *
		 * @param bool       $ret Is donation form in single price mode?
		 * @param int|string $ID  The ID of the donation form.
		 */
		return (bool) apply_filters( 'give_single_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if custom price mode is enabled or disabled
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool
	 */
	public function is_custom_price_mode() {

		$option = give_get_meta( $this->ID, '_give_custom_amount', true );
		$ret    = 0;

		if ( give_is_setting_enabled( $option ) ) {
			$ret = 1;
		}

		/**
		 * Override the price mode for a donation when checking if is in custom price mode.
		 *
		 * @since 1.6
		 *
		 * @param bool       $ret Is donation form in custom price mode?
		 * @param int|string $ID  The ID of the donation form.
		 */
		return (bool) apply_filters( 'give_custom_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if custom price mode is enabled or disabled
	 *
	 * @since  1.8.18
	 * @access public
	 *
	 * @param string|float $amount Donation Amount.
	 *
	 * @return bool
	 */
	public function is_custom_price( $amount ) {
		$result = false;
		$amount = give_maybe_sanitize_amount( $amount );

		if ( $this->is_custom_price_mode() ) {

			if ( 'set' === $this->get_type() ) {
				if ( $amount !== $this->get_price() ) {
					$result = true;
				}
			} elseif ( 'multi' === $this->get_type() ) {
				$level_amounts = array_map( 'give_maybe_sanitize_amount', wp_list_pluck( $this->get_prices(), '_give_amount' ) );
				$result        = ! in_array( $amount, $level_amounts );
			}
		}

		/**
		 * Filter to reset whether it is custom price or not.
		 *
		 * @param bool         $result True/False.
		 * @param string|float $amount Donation Amount.
		 * @param int          $this   ->ID Form ID.
		 *
		 * @since 1.8.18
		 */
		return (bool) apply_filters( 'give_is_custom_price', $result, $amount, $this->ID );
	}

	/**
	 * Has Variable Prices
	 *
	 * Determine if the donation form has variable prices enabled
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function has_variable_prices() {

		$option = give_get_meta( $this->ID, '_give_price_option', true );
		$ret    = 0;

		if ( $option === 'multi' ) {
			$ret = 1;
		}

		/**
		 * Filter: Override whether the donation form has variables prices.
		 *
		 * @param bool       $ret Does donation form have variable prices?
		 * @param int|string $ID  The ID of the donation form.
		 */
		return (bool) apply_filters( 'give_has_variable_prices', $ret, $this->ID );

	}

	/**
	 * Retrieve the donation form type, set or multi-level
	 *
	 * @since  1.5
	 * @access public
	 *
	 * @return string Type of donation form, either 'set' or 'multi'.
	 */
	public function get_type() {

		if ( ! isset( $this->type ) ) {

			$this->type = give_get_meta( $this->ID, '_give_price_option', true );

			if ( empty( $this->type ) ) {
				$this->type = 'set';
			}
		}

		return apply_filters( 'give_get_form_type', $this->type, $this->ID );

	}

	/**
	 * Get form tag classes.
	 *
	 * Provides the classes for the donation <form> html tag and filters for customization.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @param  $args
	 *
	 * @return string
	 */
	public function get_form_classes( $args ) {
        /**
         * @since 3.11.0 sanitize $args
         */
        $args = give_clean($args);

		$float_labels_option = give_is_float_labels_enabled( $args )
			? 'float-labels-enabled'
			: '';

		$form_classes_array = apply_filters(
			'give_form_classes',
			[
				'give-form',
				'give-form-' . $this->ID,
				'give-form-type-' . $this->get_type(),
				$float_labels_option,
			],
			$this->ID,
			$args
		);

		// Remove empty class names.
		$form_classes_array = array_filter( $form_classes_array );

        /**
         * @since 3.11.0 sanitize attributes
         */
        $form_classes_array = array_map('esc_attr', $form_classes_array);

		return implode( ' ', $form_classes_array );

	}

	/**
	 * Get form wrap Classes.
	 *
	 * Provides the classes for the donation form div wrapper and filters for customization.
	 *
	 * @access public
	 *
	 * @param  $args
	 *
	 * @return string
	 */
	public function get_form_wrap_classes( $args ) {
        /**
         * @since 3.11.0 sanitize $args
         */
        $args = give_clean($args);

		$custom_class = [
			'give-form-wrap',
		];

		if ( $this->is_close_donation_form() ) {
			$custom_class[] = 'give-form-closed';
		} else {
			$display_option = ( isset( $args['display_style'] ) && ! empty( $args['display_style'] ) )
				? $args['display_style']
				: give_get_meta( $this->ID, '_give_payment_display', true );

			$custom_class[] = "give-display-{$display_option}";

			// If admin want to show only button for form then user inbuilt modal functionality.
			if ( 'button' === $display_option ) {
				$custom_class[] = 'give-display-button-only';
			}
		}

		/**
		 * Filter the donation form classes.
		 *
		 * @since 1.0
		 */
		$form_wrap_classes_array = (array) apply_filters( 'give_form_wrap_classes', $custom_class, $this->ID, $args );

        /**
         * @since 3.11.0 sanitize attributes
         */
        $form_wrap_classes_array = array_map('esc_attr', $form_wrap_classes_array);

		return implode( ' ', $form_wrap_classes_array );

	}

	/**
	 * Get if form type set or not.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool
	 */
	public function is_set_type_donation_form() {
		$form_type = $this->get_type();

		return ( 'set' === $form_type ? true : false );
	}

	/**
	 * Get if form type multi or not.
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool True if form type is 'multi' and false otherwise.
	 */
	public function is_multi_type_donation_form() {
		$form_type = $this->get_type();

		return ( 'multi' === $form_type ? true : false );

	}

	/**
	 * Retrieve the sale count for the donation form
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return int    Donation form sale count.
	 */
	public function get_sales() {

		if ( ! isset( $this->sales ) ) {

			if ( '' == give_get_meta( $this->ID, '_give_form_sales', true ) ) {
                give_update_meta( $this->ID, '_give_form_sales', 0 );
			} // End if

			$this->sales = give_get_meta( $this->ID, '_give_form_sales', true );

			if ( $this->sales < 0 ) {
				// Never let sales be less than zero.
				$this->sales = 0;
			}
		}

		return $this->sales;

	}

	/**
	 * Increment the sale count by one
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $quantity The quantity to increase the donations by. Default is 1.
	 *
	 * @return int|false     New number of total sales.
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
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $quantity The quantity to decrease by. Default is 1.
	 *
	 * @return int|false     New number of total sales.
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
	 * @since  1.0
	 * @access public
	 *
	 * @return float  Donation form total earnings.
	 */
	public function get_earnings() {

		if ( ! isset( $this->earnings ) ) {

			if ( '' == give_get_meta( $this->ID, '_give_form_earnings', true ) ) {
                give_update_meta( $this->ID, '_give_form_earnings', 0 );
			}

			$this->earnings = give_get_meta( $this->ID, '_give_form_earnings', true );

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
	 * @since  1.0
	 * @since  2.1 Pass the donation ID.
	 *
	 * @access public
	 *
	 * @param  int $amount     Amount of donation. Default is 0.
	 * @param int $payment_id Donation ID.
	 *
	 * @return float|false
	 */
	public function increase_earnings( $amount = 0, $payment_id = 0 ) {

		$earnings = give_get_form_earnings_stats( $this->ID );

		/**
		 * Modify the earning amount when increasing.
		 *
		 * @since 2.1
		 *
		 * @param float $amount     Earning amount.
		 * @param int   $form_id    Donation form ID.
		 * @param int   $payment_id Donation ID.
		 */
		$amount = apply_filters( 'give_increase_form_earnings_amount', $amount, $this->ID, $payment_id );

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
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $amount     Amount of donation.
	 * @param int $payment_id Donation ID.
	 *
	 * @return float|false
	 */
	public function decrease_earnings( $amount, $payment_id = 0 ) {

		$earnings = give_get_form_earnings_stats( $this->ID );

		if ( $earnings > 0 ) {

			/**
			 * Modify the earning value when decreasing it.
			 *
			 * @since 2.1
			 *
			 * @param float $amount     Earning amount.
			 * @param int   $form_id    Donation Form ID.
			 * @param int   $payment_id Donation ID.
			 */
			$amount = apply_filters( 'give_decrease_form_earnings_amount', $amount, $this->ID, $payment_id );

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
	 * Determine if donation form closed or not
	 *
	 * Form will be close if:
	 *  a. form has fixed goal
	 *  b. close form when goal achieved cmb2 setting is set to 'Yes'
	 *  c. goal has been achieved
	 *
	 * @since  1.4.5
	 * @access public
	 *
	 * @return bool
	 */
	public function is_close_donation_form() {
		$is_closed = ( 'closed' === give_get_meta( $this->ID, '_give_form_status', true, 'open' ) );

		// If manual upgrade not completed, proceed with backward compatible code.
		if ( ! give_has_upgrade_completed( 'v210_verify_form_status_upgrades' ) ) {

			// Check for backward compatibility.
			$is_closed = $this->bc_210_is_close_donation_form();
		}

		/**
		 * Filter the close form result.
		 *
		 * @since 1.8
		 */
		return apply_filters(
			'give_is_close_donation_form',
			$is_closed,
			$this
		);

	}


	/**
	 * Check whether donation form has goal or not
	 *
	 * @since  2.4.2
	 * @access public
	 *
	 * @return bool
	 */
	public function has_goal() {
		return give_is_setting_enabled(
			Give()->form_meta->get_meta(
				$this->ID,
				'_give_goal_option',
				true
			)
		);
	}

	/**
	 * Updates a single meta entry for the donation form
	 *
	 * @since  1.5
	 * @access private
	 *
	 * @param  string              $meta_key   The meta_key to update.
	 * @param  string|array|object $meta_value The value to put into the meta.
	 *
	 * @return bool                            The result of the update query.
	 */
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		/* @var WPDB $wpdb */
		global $wpdb;

		// Bailout.
		if ( empty( $meta_key ) ) {
			return false;
		}

		if ( give_update_meta( $this->ID, $meta_key, $meta_value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Backward Compatible function for is_close_donation_form()
	 *
	 * @since 2.1.0
	 *
	 * @return bool
	 */
	private function bc_210_is_close_donation_form() {

		$close_form      = false;
		$is_goal_enabled = give_is_setting_enabled( give_get_meta( $this->ID, '_give_goal_option', true, 'disabled' ) );

		// Proceed, if the form goal is enabled.
		if ( $is_goal_enabled ) {

			$close_form_when_goal_achieved = give_is_setting_enabled( give_get_meta( $this->ID, '_give_close_form_when_goal_achieved', true, 'disabled' ) );

			// Proceed, if close form when goal achieved option is enabled.
			if ( $close_form_when_goal_achieved ) {

				$form        = new Give_Donate_Form( $this->ID );
				$goal_format = give_get_form_goal_format( $this->ID );

				// Verify whether the form is closed or not after processing data based on goal format.
				switch ( $goal_format ) {
					case 'donation':
						$closed = $form->get_goal() <= $form->get_sales();
						break;
					case 'donors':
						$closed = $form->get_goal() <= give_get_form_donor_count( $this->ID );
						break;
					default:
						$closed = $form->get_goal() <= $form->get_earnings();
						break;
				}

				if ( $closed ) {
					$close_form = true;
				}
			}
		}

		return $close_form;
	}

}
