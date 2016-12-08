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
 * in a function hooked to the 'give_email_tags' action.
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Email_Template_Tags
 */
class Give_Email_Template_Tags {

	/**
	 * Container for storing all tags.
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
	 * Add an email tag.
	 *
	 * @since 1.0
	 *
	 * @param string   $tag         Email tag to be replace in email
	 * @param string   $description Email tag description text
	 * @param callable $func        Hook to run when email tag is found
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
	 * Remove an email tag.
	 *
	 * @since 1.0
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[ $tag ] );
	}

	/**
	 * Check if $tag is a registered email tag.
	 *
	 * @since 1.0
	 *
	 * @param string $tag Email tag that will be searched.
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
	 * Search content for email tags and filter email tags through their hooks.
	 *
	 * @param string $content    Content to search for email tags.
	 * @param int    $payment_id The payment id.
	 *
	 * @since 1.0
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $payment_id ) {

		// Check if there is at least one tag added.
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
	 * @param $m array
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
 * Add an email tag.
 *
 * @since 1.0
 *
 * @param string   $tag         Email tag to be replace in email
 * @param string   $description Description of the email tag added
 * @param callable $func        Hook to run when email tag is found
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

	// Get all email tags.
	$email_tags = give_get_email_tags();

	ob_start();
	if ( count( $email_tags ) > 0 ) : ?>
		<div class="give-email-tags-wrap">
			<?php foreach ( $email_tags as $email_tag ) : ?>
				<span class="give_<?php echo $email_tag['tag']; ?>_tag">
					<code>{<?php echo $email_tag['tag']; ?>}</code> - <?php echo $email_tag['description']; ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif;

	// Return the list.
	return ob_get_clean();
}

/**
 * Search content for email tags and filter email tags through their hooks.
 *
 * @param string $content    Content to search for email tags.
 * @param int    $payment_id The payment id.
 *
 * @since 1.0
 *
 * @return string Content with email tags filtered out.
 */
function give_do_email_tags( $content, $payment_id ) {

	// Replace all tags
	$content = Give()->email_tags->do_tags( $content, $payment_id );

	$content = apply_filters( 'give_email_template_tags', $content, give_get_payment_meta( $payment_id ), $payment_id );

	// Return content
	return $content;
}

/**
 * Load email tags.
 *
 * @since 1.0
 */
function give_load_email_tags() {
	/**
	 * Fires when loading email tags.
	 *
	 * Allows you to add new email tags.
	 *
	 * @since 1.0
	 */
	do_action( 'give_add_email_tags' );
}

add_action( 'init', 'give_load_email_tags', - 999 );

/**
 * Add default Give email template tags.
 *
 * @since 1.0
 */
function give_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'donation',
			'description' => esc_html__( 'The donation form name, and the donation level (if applicable).', 'give' ),
			'function'    => 'give_email_tag_donation'
		),
		array(
			'tag'         => 'form_title',
			'description' => esc_html__( 'The donation form name.', 'give' ),
			'function'    => 'give_email_tag_form_title'
		),
		array(
			'tag'         => 'amount',
			'description' => esc_html__( 'The total donation amount with currency sign.', 'give' ),
			'function'    => 'give_email_tag_amount'
		),
		array(
			'tag'         => 'price',
			'description' => esc_html__( 'The total donation amount with currency sign.', 'give' ),
			'function'    => 'give_email_tag_price'
		),
		array(
			'tag'         => 'name',
			'description' => esc_html__( 'The donor\'s first name.', 'give' ),
			'function'    => 'give_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => esc_html__( 'The donor\'s full name, first and last.', 'give' ),
			'function'    => 'give_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => esc_html__( 'The donor\'s user name on the site, if they registered an account.', 'give' ),
			'function'    => 'give_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => esc_html__( 'The donor\'s email address.', 'give' ),
			'function'    => 'give_email_tag_user_email'
		),
		array(
			'tag'         => 'billing_address',
			'description' => esc_html__( 'The donor\'s billing address.', 'give' ),
			'function'    => 'give_email_tag_billing_address'
		),
		array(
			'tag'         => 'date',
			'description' => esc_html__( 'The date of the donation.', 'give' ),
			'function'    => 'give_email_tag_date'
		),
		array(
			'tag'         => 'payment_id',
			'description' => esc_html__( 'The unique ID number for this donation.', 'give' ),
			'function'    => 'give_email_tag_payment_id'
		),
		array(
			'tag'         => 'receipt_id',
			'description' => esc_html__( 'The unique ID number for this donation receipt.', 'give' ),
			'function'    => 'give_email_tag_receipt_id'
		),
		array(
			'tag'         => 'payment_method',
			'description' => esc_html__( 'The method of payment used for this donation.', 'give' ),
			'function'    => 'give_email_tag_payment_method'
		),
		array(
			'tag'         => 'sitename',
			'description' => esc_html__( 'The name of your site.', 'give' ),
			'function'    => 'give_email_tag_sitename'
		),
		array(
			'tag'         => 'receipt_link',
			'description' => esc_html__( 'The donation receipt direct link, to view the receipt on the website.', 'give' ),
			'function'    => 'give_email_tag_receipt_link'
		),
		array(
			'tag'         => 'receipt_link_url',
			'description' => esc_html__( 'The donation receipt direct URL, to view the receipt on the website.', 'give' ),
			'function'    => 'give_email_tag_receipt_link_url'
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
 * Email template tag: {name}
 *
 * The donor's first name.
 *
 * @param int $payment_id
 *
 * @return string name
 */
function give_email_tag_first_name( $payment_id ) {
	$payment   = new Give_Payment( $payment_id );
	$user_info = $payment->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name = give_get_email_names( $user_info );

	return $email_name['name'];
}

/**
 * Email template tag: {fullname}
 *
 * The donor's full name, first and last.
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function give_email_tag_fullname( $payment_id ) {
	$payment   = new Give_Payment( $payment_id );
	$user_info = $payment->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name = give_get_email_names( $user_info );

	return $email_name['fullname'];
}

/**
 * Email template tag: {username}
 *
 * The donor's user name on the site, if they registered an account.
 *
 * @param int $payment_id
 *
 * @return string username.
 */
function give_email_tag_username( $payment_id ) {
	$payment   = new Give_Payment( $payment_id );
	$user_info = $payment->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name = give_get_email_names( $user_info );

	return $email_name['username'];
}

/**
 * Email template tag: {user_email}
 *
 * The donor's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function give_email_tag_user_email( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->email;
}

/**
 * Email template tag: {billing_address}
 *
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
 * Email template tag: {date}
 *
 * Date of donation
 *
 * @param int $payment_id
 *
 * @return string date
 */
function give_email_tag_date( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return date_i18n( give_date_format(), strtotime( $payment->date ) );
}

/**
 * Email template tag: give_amount.
 *
 * The total amount of the donation given.
 *
 * @param int $payment_id
 *
 * @return string amount
 */
function give_email_tag_amount( $payment_id ) {
	$payment     = new Give_Payment( $payment_id );
	$give_amount = give_currency_filter( give_format_amount( $payment->total ), $payment->currency );

	return html_entity_decode( $give_amount, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: {price}
 *
 * The total price of the donation.
 *
 * @param int $payment_id
 *
 * @return string price
 */
function give_email_tag_price( $payment_id ) {
	return give_email_tag_amount( $payment_id );
}

/**
 * Email template tag: {payment_id}
 *
 * The unique ID number for this donation.
 *
 * @param int $payment_id
 *
 * @return int payment_id
 */
function give_email_tag_payment_id( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->number;
}

/**
 * Email template tag: {receipt_id}
 *
 * The unique ID number for this donation receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function give_email_tag_receipt_id( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->key;
}

/**
 * Email template tag: {donation}
 *
 * Output the donation form name, and the donation level (if applicable).
 *
 * @param int $payment_id
 *
 * @return string $form_title
 */
function give_email_tag_donation( $payment_id ) {
	$payment      = new Give_Payment( $payment_id );
	$payment_meta = $payment->payment_meta;
	$level_title  = give_has_variable_prices( $payment->form_id );
	$separator    = $level_title ? '-' : '';
	$form_title   = strip_tags( give_get_payment_form_title( $payment_meta, false, $separator ) );

	return ! empty( $form_title ) ? $form_title : '';

}

/**
 * Email template tag: {form_title}
 *
 * Output the donation form name.
 *
 * @param int $payment_id
 *
 * @return string $form_title
 */
function give_email_tag_form_title( $payment_id ) {
	$payment      = new Give_Payment( $payment_id );
	$payment_meta = $payment->payment_meta;

	return isset( $payment_meta['form_title'] ) ? strip_tags( $payment_meta['form_title'] ) : '';

}

/**
 * Email template tag: {payment_method}
 *
 * The method of payment used for this donation.
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function give_email_tag_payment_method( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return give_get_gateway_checkout_label( $payment->gateway );
}

/**
 * Email template tag: {sitename}
 *
 * The name of the site.
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function give_email_tag_sitename( $payment_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Email template tag: {receipt_link}
 *
 * The donation receipt direct link, to view the receipt on the website.
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
	$formatted   = sprintf(
		'<a href="%1$s">%2$s</a>',
		$receipt_url,
		esc_html__( 'View it in your browser', 'give' )
	);

	if ( give_get_option( 'email_template' ) !== 'none' ) {
		return $formatted;
	} else {
		return $receipt_url;
	}

}

/**
 * Email template tag: {receipt_link_url}
 *
 * The donation receipt direct URL, to view the receipt on the website.
 *
 * @since 1.7
 *
 * @param int $payment_id
 *
 * @return string receipt_url
 */
function give_email_tag_receipt_link_url( $payment_id ) {

	$receipt_url = esc_url( add_query_arg( array(
		'payment_key' => give_get_payment_key( $payment_id ),
		'give_action' => 'view_receipt'
	), home_url() ) );

	return $receipt_url;

}
