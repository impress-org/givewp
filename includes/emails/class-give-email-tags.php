<?php
/**
 * Give API for creating Email template tags
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: give_do_email_tags( $content, payment_id );
 *
 * To add tags, use: give_add_email_tag( $tag, $description, $func ). Be sure to wrap give_add_email_tag()
 * in a function hooked to the 'give_email_tags' action
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.0
	 */
	private $tags;

	/**
	 * Payment ID
	 *
	 * @since 1.0
	 */
	private $payment_id;

	/**
	 * Add an email tag
	 *
	 * @since 1.0
	 *
	 * @param string $tag Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[ $tag ] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove an email tag
	 *
	 * @since 1.0
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[ $tag ] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.0
	 *
	 * @param string $tag Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 * @param int $payment_id The payment id
	 *
	 * @since 1.0
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $payment_id ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->payment_id = $payment_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->payment_id = null;

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use give_do_email_tags instead.
	 *
	 * @since 1.0
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[ $tag ]['func'], $this->payment_id, $tag );
	}

}

/**
 * Add an email tag
 *
 * @since 1.0
 *
 * @param string $tag Email tag to be replace in email
 * @param string $description Description of the email tag added
 * @param callable $func Hook to run when email tag is found
 */
function give_add_email_tag( $tag, $description, $func ) {
	Give()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since 1.0
 *
 * @param string $tag Email tag to remove hook from
 */
function give_remove_email_tag( $tag ) {
	Give()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since 1.0
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function give_email_tag_exists( $tag ) {
	return Give()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since 1.0
 *
 * @return array
 */
function give_get_email_tags() {
	return Give()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since 1.0
 *
 * @return string
 */
function give_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = give_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '<code>{' . $email_tag['tag'] . '}</code> - ' . $email_tag['description'] . '<br/>';

		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $payment_id The payment id
 *
 * @since 1.0
 *
 * @return string Content with email tags filtered out.
 */
function give_do_email_tags( $content, $payment_id ) {

	// Replace all tags
	$content = Give()->email_tags->do_tags( $content, $payment_id );

	// Maintaining backwards compatibility
	$content = apply_filters( 'give_email_template_tags', $content, give_get_payment_meta( $payment_id ), $payment_id );

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since 1.0
 */
function give_load_email_tags() {
	do_action( 'give_add_email_tags' );
}

add_action( 'init', 'give_load_email_tags', - 999 );

/**
 * Add default Give email template tags
 *
 * @since 1.0
 */
function give_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'donation',
			'description' => __( 'The name of completed donation form', 'give' ),
			'function'    => 'give_email_tag_donation'
		),
		array(
			'tag'         => 'name',
			'description' => __( 'The donor\'s first name', 'give' ),
			'function'    => 'give_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( 'The donor\'s full name, first and last', 'give' ),
			'function'    => 'give_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( 'The donor\'s user name on the site, if they registered an account', 'give' ),
			'function'    => 'give_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( 'The donor\'s email address', 'give' ),
			'function'    => 'give_email_tag_user_email'
		),
		array(
			'tag'         => 'billing_address',
			'description' => __( 'The donor\'s billing address', 'give' ),
			'function'    => 'give_email_tag_billing_address'
		),
		array(
			'tag'         => 'date',
			'description' => __( 'The date of the donation', 'give' ),
			'function'    => 'give_email_tag_date'
		),
		array(
			'tag'         => 'price',
			'description' => __( 'The total price of the donation', 'give' ),
			'function'    => 'give_email_tag_price'
		),
		array(
			'tag'         => 'payment_id',
			'description' => __( 'The unique ID number for this donation', 'give' ),
			'function'    => 'give_email_tag_payment_id'
		),
		array(
			'tag'         => 'receipt_id',
			'description' => __( 'The unique ID number for this donation receipt', 'give' ),
			'function'    => 'give_email_tag_receipt_id'
		),
		array(
			'tag'         => 'payment_method',
			'description' => __( 'The method of payment used for this donation', 'give' ),
			'function'    => 'give_email_tag_payment_method'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'give' ),
			'function'    => 'give_email_tag_sitename'
		),
		array(
			'tag'         => 'receipt_link',
			'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'give' ),
			'function'    => 'give_email_tag_receipt_link'
		),
	);

	// Apply give_email_tags filter
	$email_tags = apply_filters( 'give_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		give_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

}

add_action( 'give_add_email_tags', 'give_setup_email_tags' );


/**
 * Email template tag: name
 * The donor's first name
 *
 * @param int $payment_id
 *
 * @return string name
 */
function give_email_tag_first_name( $payment_id ) {
	$payment_data = give_get_payment_meta( $payment_id );
	if ( empty( $payment_data['user_info'] ) ) {
		return '';
	}
	$email_name = give_get_email_names( $payment_data['user_info'] );

	return $email_name['name'];
}

/**
 * Email template tag: fullname
 * The donor's full name, first and last
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function give_email_tag_fullname( $payment_id ) {
	$payment_data = give_get_payment_meta( $payment_id );
	if ( empty( $payment_data['user_info'] ) ) {
		return '';
	}
	$email_name = give_get_email_names( $payment_data['user_info'] );

	return $email_name['fullname'];
}

/**
 * Email template tag: username
 * The donor's user name on the site, if they registered an account
 *
 * @param int $payment_id
 *
 * @return string username
 */
function give_email_tag_username( $payment_id ) {
	$payment_data = give_get_payment_meta( $payment_id );
	if ( empty( $payment_data['user_info'] ) ) {
		return '';
	}
	$email_name = give_get_email_names( $payment_data['user_info'] );

	return $email_name['username'];
}

/**
 * Email template tag: user_email
 * The donor's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function give_email_tag_user_email( $payment_id ) {
	return give_get_payment_user_email( $payment_id );
}

/**
 * Email template tag: billing_address
 * The donor's billing address
 *
 * @param int $payment_id
 *
 * @return string billing_address
 */
function give_email_tag_billing_address( $payment_id ) {

	$user_info    = give_get_payment_meta_user_info( $payment_id );
	$user_address = ! empty( $user_info['address'] ) ? $user_info['address'] : array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'country' => '',
		'state'   => '',
		'zip'     => ''
	);

	$return = $user_address['line1'] . "\n";
	if ( ! empty( $user_address['line2'] ) ) {
		$return .= $user_address['line2'] . "\n";
	}
	$return .= $user_address['city'] . ' ' . $user_address['zip'] . ' ' . $user_address['state'] . "\n";
	$return .= $user_address['country'];

	return $return;
}

/**
 * Email template tag: date
 * Date of donation
 *
 * @param int $payment_id
 *
 * @return string date
 */
function give_email_tag_date( $payment_id ) {
	$payment_data = give_get_payment_meta( $payment_id );

	return date_i18n( get_option( 'date_format' ), strtotime( $payment_data['date'] ) );
}

/**
 * Email template tag: price
 * The total price of the donation
 *
 * @param int $payment_id
 *
 * @return string price
 */
function give_email_tag_price( $payment_id ) {
	$price = give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ), give_get_payment_currency_code( $payment_id ) );

	return html_entity_decode( $price, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: payment_id
 * The unique ID number for this donation
 *
 * @param int $payment_id
 *
 * @return int payment_id
 */
function give_email_tag_payment_id( $payment_id ) {
	return give_get_payment_number( $payment_id );
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this donation receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function give_email_tag_receipt_id( $payment_id ) {
	return give_get_payment_key( $payment_id );
}

/**
 * Email template tag: donation
 * The form submitted to make the donation
 *
 * @param int $payment_id
 *
 * @return string $form_title
 */
function give_email_tag_donation( $payment_id ) {
	$payment_data = give_get_payment_meta( $payment_id );
	$form_title   = ( ! empty( $payment_data['form_title'] ) ? $payment_data['form_title'] : __( 'There was an error retrieving this donation title', 'give' ) );

	return $form_title;
}

/**
 * Email template tag: payment_method
 * The method of payment used for this donation
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function give_email_tag_payment_method( $payment_id ) {
	return give_get_gateway_checkout_label( give_get_payment_gateway( $payment_id ) );
}

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function give_email_tag_sitename( $payment_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Email template tag: receipt_link
 *
 * @description: Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param int $payment_id
 *
 * @return string receipt_link
 */
function give_email_tag_receipt_link( $payment_id ) {

	$receipt_url = esc_url( add_query_arg( array(
		'payment_key' => give_get_payment_key( $payment_id ),
		'give_action' => 'view_receipt'
	), home_url() ) );
	$formatted   = sprintf( __( '%1$sView it in your browser %2$s', 'give' ), '<a href="' . $receipt_url . '">', '&raquo;</a>' );

	if ( give_get_option( 'email_template' ) !== 'none' ) {
		return $formatted;
	} else {
		return $receipt_url;
	}

}