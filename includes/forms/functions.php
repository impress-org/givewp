<?php
/**
 * Give Form Functions
 *
 * @package     WordImpress
 * @subpackage  Includes/Forms
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter: Do not show the Give shortcut button on Give Forms or Campaign posts
 *
 * @return bool
 */
function give_shortcode_button_condition() {

	global $typenow;

	if ( $typenow != 'give_forms' && $typenow != 'give_campaigns' ) {
		return true;
	}

	return false;
}

add_filter( 'give_shortcode_button_condition', 'give_shortcode_button_condition' );


/**
 * Get the form ID from the form $args
 *
 * @param $args
 *
 * @return int|false
 */
function get_form_id_from_args( $args ) {

	if ( isset( $args['form_id'] ) && $args['form_id'] != 0 ) {

		return intval( $args['form_id'] );
	}

	return false;
}

/**
 * Checks whether floating labels is enabled for the form ID in $args
 *
 * @since 1.1
 *
 * @param array $args
 *
 * @return bool
 */
function give_is_float_labels_enabled( $args ) {

	$float_labels = '';

	if ( ! empty( $args['float_labels'] ) ) {
		$float_labels = $args['float_labels'];
	}

	if ( empty( $float_labels ) ) {
		$float_labels = get_post_meta( $args['form_id'], '_give_form_floating_labels', true );
	}

	if ( empty( $float_labels ) ) {
		$float_labels = give_get_option( 'enable_floatlabels' ) ? 'enabled' : 'disabled';
	}

	return ( $float_labels == 'enabled' ) ? true : false;
}

/**
 * Determines if a user can checkout or not
 *
 * @since 1.0
 * @global $give_options Array of all the Give Options
 * @return bool Can user checkout?
 */
function give_can_checkout() {
	global $give_options;

	$can_checkout = true; // Always true for now

	return (bool) apply_filters( 'give_can_checkout', $can_checkout );
}

/**
 * Retrieve the Success page URI
 *
 * @access      public
 * @since       1.0
 * @return      string
 */
function give_get_success_page_uri() {
	global $give_options;

	$success_page = isset( $give_options['success_page'] ) ? get_permalink( absint( $give_options['success_page'] ) ) : get_bloginfo( 'url' );

	return apply_filters( 'give_get_success_page_uri', $success_page );
}

/**
 * Determines if we're currently on the Success page.
 *
 * @since 1.0
 * @return bool True if on the Success page, false otherwise.
 */
function give_is_success_page() {
	global $give_options;
	$is_success_page = isset( $give_options['success_page'] ) ? is_page( $give_options['success_page'] ) : false;

	return apply_filters( 'give_is_success_page', $is_success_page );
}

/**
 * Send To Success Page
 *
 * Sends the user to the succes page.
 *
 * @param string $query_string
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function give_send_to_success_page( $query_string = null ) {
	global $give_options;

	$redirect = give_get_success_page_uri();

	if ( $query_string ) {
		$redirect .= $query_string;
	}

	$gateway = isset( $_REQUEST['give-gateway'] ) ? $_REQUEST['give-gateway'] : '';

	wp_redirect( apply_filters( 'give_success_page_redirect', $redirect, $gateway, $query_string ) );
	give_die();
}


/**
 * Send back to checkout.
 *
 * Used to redirect a user back to the purchase
 * page if there are errors present.
 *
 * @param array $args
 *
 * @access public
 * @since  1.0
 * @return Void
 */
function give_send_back_to_checkout( $args = array() ) {

	$redirect = ( isset( $_POST['give-current-url'] ) ) ? $_POST['give-current-url'] : '';
	$form_id  = isset( $_POST['give-form-id'] ) ? $_POST['give-form-id'] : 0;

	$defaults = array(
		'form-id' => (int) $form_id
	);

	// Check for backward compatibility
	if ( is_string( $args ) ) {
		$args = str_replace( '?', '', $args );
	}

	$args = wp_parse_args( $args, $defaults );

	$redirect = add_query_arg( $args, $redirect ) . '#give-form-' . $form_id . '-wrap';

	wp_redirect( apply_filters( 'give_send_back_to_checkout', $redirect, $args ) );
	give_die();
}

/**
 * Get Success Page URL
 *
 * Gets the success page URL.
 *
 * @param string $query_string
 *
 * @access      public
 * @since       1.0
 * @return      string
 */
function give_get_success_page_url( $query_string = null ) {
	global $give_options;

	$success_page = get_permalink( $give_options['success_page'] );
	if ( $query_string ) {
		$success_page .= $query_string;
	}

	return apply_filters( 'give_success_page_url', $success_page );
}

/**
 * Get the URL of the Transaction Failed page
 *
 * @since 1.0
 * @global     $give_options Array of all the Give Options
 *
 * @param bool $extras       Extras to append to the URL
 *
 * @return mixed|void Full URL to the Transaction Failed page, if present, home page if it doesn't exist
 */
function give_get_failed_transaction_uri( $extras = false ) {
	global $give_options;

	$uri = ! empty( $give_options['failure_page'] ) ? trailingslashit( get_permalink( $give_options['failure_page'] ) ) : home_url();
	if ( $extras ) {
		$uri .= $extras;
	}

	return apply_filters( 'give_get_failed_transaction_uri', $uri );
}

/**
 * Determines if we're currently on the Failed Transaction page.
 *
 * @since 1.0
 * @return bool True if on the Failed Transaction page, false otherwise.
 */
function give_is_failed_transaction_page() {
	global $give_options;
	$ret = isset( $give_options['failure_page'] ) ? is_page( $give_options['failure_page'] ) : false;

	return apply_filters( 'give_is_failure_page', $ret );
}

/**
 * Mark payments as Failed when returning to the Failed Transaction page
 *
 * @access      public
 * @since       1.0
 * @return      void
 */
function give_listen_for_failed_payments() {

	$failed_page = give_get_option( 'failure_page', 0 );

	if ( ! empty( $failed_page ) && is_page( $failed_page ) && ! empty( $_GET['payment-id'] ) ) {

		$payment_id = absint( $_GET['payment-id'] );
		give_update_payment_status( $payment_id, 'failed' );

	}

}

add_action( 'template_redirect', 'give_listen_for_failed_payments' );


/**
 * Check if a field is required
 *
 * @param string $field
 * @param int    $form_id
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function give_field_is_required( $field = '', $form_id ) {

	$required_fields = give_purchase_form_required_fields( $form_id );

	return array_key_exists( $field, $required_fields );
}

/**
 * Retrieve an array of banned_emails
 *
 * @since       1.0
 * @return      array
 */
function give_get_banned_emails() {
	$emails = array_map( 'trim', give_get_option( 'banned_emails', array() ) );

	return apply_filters( 'give_get_banned_emails', $emails );
}

/**
 * Determines if an email is banned
 *
 * @since       2.0
 * @return      bool
 */
function give_is_email_banned( $email = '' ) {

	if ( empty( $email ) ) {
		return false;
	}

	$ret = in_array( trim( $email ), give_get_banned_emails() );

	return apply_filters( 'give_is_email_banned', $ret, $email );
}

/**
 * Determines if secure checkout pages are enforced
 *
 * @since       1.0
 * @return      bool True if enforce SSL is enabled, false otherwise
 */
function give_is_ssl_enforced() {
	$ssl_enforced = give_get_option( 'enforce_ssl', false );

	return (bool) apply_filters( 'give_is_ssl_enforced', $ssl_enforced );
}

/**
 * Handle redirections for SSL enforced checkouts
 *
 * @since 1.0
 * @global $give_options Array of all the Give Options
 * @return void
 */
function give_enforced_ssl_redirect_handler() {
	if ( ! give_is_ssl_enforced() || is_admin() || is_ssl() ) {
		return;
	}

	if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
		return;
	}

	$uri = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	wp_safe_redirect( $uri );
	exit;
}

//add_action( 'template_redirect', 'give_enforced_ssl_redirect_handler' );

/**
 * Handle rewriting asset URLs for SSL enforced checkouts
 *
 * @since 1.0
 * @return void
 */
function give_enforced_ssl_asset_handler() {
	if ( ! give_is_ssl_enforced() || is_admin() ) {
		return;
	}

	$filters = array(
		'post_thumbnail_html',
		'wp_get_attachment_url',
		'wp_get_attachment_image_attributes',
		'wp_get_attachment_url',
		'option_stylesheet_url',
		'option_template_url',
		'script_loader_src',
		'style_loader_src',
		'template_directory_uri',
		'stylesheet_directory_uri',
		'site_url'
	);

	$filters = apply_filters( 'give_enforced_ssl_asset_filters', $filters );

	foreach ( $filters as $filter ) {
		add_filter( $filter, 'give_enforced_ssl_asset_filter', 1 );
	}
}

//add_action( 'template_redirect', 'give_enforced_ssl_asset_handler' );

/**
 * Filter filters and convert http to https
 *
 * @since 1.0
 *
 * @param mixed $content
 *
 * @return mixed
 */
function give_enforced_ssl_asset_filter( $content ) {

	if ( is_array( $content ) ) {

		$content = array_map( 'give_enforced_ssl_asset_filter', $content );

	} else {

		// Detect if URL ends in a common domain suffix. We want to only affect assets
		$suffixes = array(
			'br',
			'ca',
			'cn',
			'com',
			'de',
			'dev',
			'edu',
			'fr',
			'in',
			'info',
			'jp',
			'local',
			'mobi',
			'name',
			'net',
			'nz',
			'org',
			'ru',
		);

	}

	return $content;
}


/**
 * Record Sale In Log
 *
 * Stores log information for a form sale.
 *
 * @since 1.0
 * @global            $give_logs
 *
 * @param int         $give_form_id Give Form ID
 * @param int         $payment_id   Payment ID
 * @param bool|int    $price_id     Price ID, if any
 * @param string|null $sale_date    The date of the sale
 *
 * @return void
 */
function give_record_sale_in_log( $give_form_id = 0, $payment_id, $price_id = false, $sale_date = null ) {
	global $give_logs;

	$log_data = array(
		'post_parent'   => $give_form_id,
		'log_type'      => 'sale',
		'post_date'     => isset( $sale_date ) ? $sale_date : null,
		'post_date_gmt' => isset( $sale_date ) ? $sale_date : null
	);

	$log_meta = array(
		'payment_id' => $payment_id,
		'price_id'   => (int) $price_id
	);

	$give_logs->insert_log( $log_data, $log_meta );
}


/**
 *
 * Increases the sale count of a download.
 *
 * @since 1.0
 *
 * @param int $give_form_id Give Form ID
 *
 * @return bool|int
 */
function give_increase_purchase_count( $give_form_id = 0 ) {
	$form = new Give_Donate_Form( $give_form_id );

	return $form->increase_sales();
}

/**
 * Decreases the sale count of a form. Primarily for when a donation is refunded.
 *
 * @since 1.0
 *
 * @param int $give_form_id Give Form ID
 *
 * @return bool|int
 */
function give_decrease_purchase_count( $give_form_id = 0 ) {
	$form = new Give_Donate_Form( $give_form_id );

	return $form->decrease_sales();
}

/**
 * Increases the total earnings of a form.
 *
 * @since 1.0
 *
 * @param int $give_form_id Give Form ID
 * @param int $amount       Earnings
 *
 * @return bool|int
 */
function give_increase_earnings( $give_form_id = 0, $amount ) {
	$form = new Give_Donate_Form( $give_form_id );

	return $form->increase_earnings( $amount );
}

/**
 * Decreases the total earnings of a form. Primarily for when a purchase is refunded.
 *
 * @since 1.0
 *
 * @param int $give_form_id Give Form ID
 * @param int $amount       Earnings
 *
 * @return bool|int
 */
function give_decrease_earnings( $give_form_id = 0, $amount ) {
	$form = new Give_Donate_Form( $give_form_id );

	return $form->decrease_earnings( $amount );
}


/**
 * Returns the total earnings for a form.
 *
 * @since 1.0
 *
 * @param int $give_form_id Give Form ID
 *
 * @return int $earnings Earnings for a certain form
 */
function give_get_form_earnings_stats( $give_form_id = 0 ) {
	$give_form = new Give_Donate_Form( $give_form_id );

	return $give_form->earnings;
}


/**
 * Return the sales number for a form.
 *
 * @since 1.0
 *
 * @param int $give_form_id Give Form ID
 *
 * @return int $sales Amount of sales for a certain form
 */
function give_get_form_sales_stats( $give_form_id = 0 ) {
	$give_form = new Give_Donate_Form( $give_form_id );

	return $give_form->sales;
}


/**
 * Retrieves the average monthly sales for a specific donation form
 *
 * @since 1.0
 *
 * @param int $form_id Form ID
 *
 * @return float $sales Average monthly sales
 */
function give_get_average_monthly_form_sales( $form_id = 0 ) {
	$sales        = give_get_form_sales_stats( $form_id );
	$release_date = get_post_field( 'post_date', $form_id );

	$diff = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 ) {
		$sales = ( $sales / $months );
	}

	return $sales;
}


/**
 * Retrieves the average monthly earnings for a specific form
 *
 * @since 1.0
 *
 * @param int $form_id Form ID
 *
 * @return float $earnings Average monthly earnings
 */
function give_get_average_monthly_form_earnings( $form_id = 0 ) {
	$earnings     = give_get_form_earnings_stats( $form_id );
	$release_date = get_post_field( 'post_date', $form_id );

	$diff = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 ) {
		$earnings = ( $earnings / $months );
	}

	return $earnings < 0 ? 0 : $earnings;
}


/**
 * Get Price Option Name (Text)
 *
 * @description Retrieves the name of a variable price option
 *
 * @since       1.0
 *
 * @param int $form_id    ID of the download
 * @param int $price_id   ID of the price option
 * @param int $payment_id optional payment ID for use in filters
 *
 * @return string $price_name Name of the price option
 */
function give_get_price_option_name( $form_id = 0, $price_id = 0, $payment_id = 0 ) {

	$prices     = give_get_variable_prices( $form_id );
	$price_name = '';

	foreach ( $prices as $price ) {

		if ( $price['_give_id']['level_id'] === $price_id ) {
			$price_name = isset( $price['_give_text'] ) ? $price['_give_text'] : '';
		}

	}


	return apply_filters( 'give_get_price_option_name', $price_name, $form_id, $payment_id, $price_id );
}


/**
 * Retrieves a price from from low to high of a variable priced form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the form
 *
 * @return string $range A fully formatted price range
 */
function give_price_range( $form_id = 0 ) {
	$low   = give_get_lowest_price_option( $form_id );
	$high  = give_get_highest_price_option( $form_id );
	$range = '<span class="give_price_range_low">' . give_currency_filter( give_format_amount( $low ) ) . '</span>';
	$range .= '<span class="give_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
	$range .= '<span class="give_price_range_high">' . give_currency_filter( give_format_amount( $high ) ) . '</span>';

	return apply_filters( 'give_price_range', $range, $form_id, $low, $high );
}


/**
 * Retrieves cheapest price option of a variable priced form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the form
 *
 * @return float Amount of the lowest price
 */
function give_get_lowest_price_option( $form_id = 0 ) {
	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	if ( ! give_has_variable_prices( $form_id ) ) {
		return give_get_form_price( $form_id );
	}

	$prices = give_get_variable_prices( $form_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['_give_amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['_give_amount'];
			} else {
				$min = min( $min, give_sanitize_amount( $price['_give_amount'] ) );
			}

			if ( $price['_give_amount'] == $min ) {
				$min_id = $key;
			}
		}

		$low = $prices[ $min_id ]['_give_amount'];

	}

	return give_sanitize_amount( $low );
}

/**
 * Retrieves most expensive price option of a variable priced form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the form
 *
 * @return float Amount of the highest price
 */
function give_get_highest_price_option( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	if ( ! give_has_variable_prices( $form_id ) ) {
		return give_get_form_price( $form_id );
	}

	$prices = give_get_variable_prices( $form_id );

	$high = 0.00;

	if ( ! empty( $prices ) ) {

		$max = 0;

		foreach ( $prices as $key => $price ) {
			if ( empty( $price['_give_amount'] ) ) {
				continue;
			}
			$give_amount = give_sanitize_amount( $price['_give_amount'] );

			$max = max( $max, $give_amount );

			if ( $give_amount == $max ) {
				$max_id = $key;
			}
		}

		$high = $prices[ $max_id ]['_give_amount'];
	}

	return give_sanitize_amount( $high );
}

/**
 * Returns the price of a form, but only for non-variable priced forms.
 *
 * @since 1.0
 *
 * @param int $form_id ID number of the form to retrieve a price for
 *
 * @return mixed string|int Price of the form
 */
function give_get_form_price( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->price;
}


/**
 * Displays a formatted price for a donation form
 *
 * @since 1.0
 *
 * @param int  $form_id  ID of the form price to show
 * @param bool $echo     Whether to echo or return the results
 * @param int  $price_id Optional price id for variable pricing
 *
 * @return int $formatted_price
 */
function give_price( $form_id = 0, $echo = true, $price_id = false ) {

	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	if ( give_has_variable_prices( $form_id ) ) {

		$prices = give_get_variable_prices( $form_id );

		if ( false !== $price_id ) {

			//loop through multi-prices to see which is default
			foreach ( $prices as $price ) {
				//this is the default price
				if ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) {
					$price = (float) $price['_give_amount'];
				};
			}

		} else {

			$price = give_get_lowest_price_option( $form_id );
		}

		$price = give_sanitize_amount( $price );

	} else {

		$price = give_get_form_price( $form_id );

	}

	$price           = apply_filters( 'give_form_price', give_sanitize_amount( $price ), $form_id );
	$formatted_price = '<span class="give_price" id="give_price_' . $form_id . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'give_form_price_after_html', $formatted_price, $form_id, $price );

	if ( $echo ) {
		echo $formatted_price;
	} else {
		return $formatted_price;
	}
}

add_filter( 'give_form_price', 'give_format_amount', 10 );
add_filter( 'give_form_price', 'give_currency_filter', 20 );


/**
 * Retrieves the amount of a variable price option
 *
 * @since 1.0
 *
 * @param int $form_id  ID of the form
 * @param int $price_id ID of the price option
 * @param     int       @payment_id ID of the payment
 *
 * @return float $amount Amount of the price option
 */
function give_get_price_option_amount( $form_id = 0, $price_id = 0 ) {
	$prices = give_get_variable_prices( $form_id );

	$amount = 0.00;

	foreach ( $prices as $price ) {
		if ( isset( $price['_give_id']['level_id'] ) && $price['_give_id']['level_id'] === $price_id ) {
			$amount = isset( $price['_give_amount'] ) ? $price['_give_amount'] : 0.00;
		};
	}

	return apply_filters( 'give_get_price_option_amount', give_sanitize_amount( $amount ), $form_id, $price_id );
}

/**
 * Returns the goal of a form
 *
 * @since 1.0
 *
 * @param int $form_id ID number of the form to retrieve a goal for
 *
 * @return mixed string|int Goal of the form
 */
function give_get_form_goal( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->goal;

}

/**
 * Displays a formatted goal for a donation form
 *
 * @since 1.0
 *
 * @param int  $form_id ID of the form price to show
 * @param bool $echo    Whether to echo or return the results
 *
 * @return string $formatted_goal
 */
function give_goal( $form_id = 0, $echo = true ) {

	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	$goal = give_get_form_goal( $form_id );

	$goal           = apply_filters( 'give_form_goal', give_sanitize_amount( $goal ), $form_id );
	$formatted_goal = '<span class="give_price" id="give_price_' . $form_id . '">' . $goal . '</span>';
	$formatted_goal = apply_filters( 'give_form_price_after_html', $formatted_goal, $form_id, $goal );

	if ( $echo ) {
		echo $formatted_goal;
	} else {
		return $formatted_goal;
	}
}

add_filter( 'give_form_goal', 'give_format_amount', 10 );
add_filter( 'give_form_goal', 'give_currency_filter', 20 );
