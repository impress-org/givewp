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
	 * Tags arguments
	 *
	 * @since 2.0
	 */
	private $tag_args;

	/**
	 * Add an email tag.
	 *
	 * @since 1.0
	 *
	 * @param string   $tag         Email tag to be replace in email
	 * @param string   $description Email tag description text
	 * @param callable $func        Hook to run when email tag is found
	 * @param string   $context     Email tag category
	 */
	public function add( $tag, $description, $func, $context = '' ) {
		if ( is_callable( $func ) ) {
			$this->tags[ $tag ] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func,
				'context'     => give_check_variable( $context, 'empty', 'general' ),
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
	 * @since 2.0 Add $context_type param to get specific  contect email tags.
	 *
	 * @param string $context_type
	 * @param string $field
	 *
	 * @return array
	 */
	public function get_tags( $context_type = 'all', $field = '' ) {
		$tags = $this->tags;

		if ( 'all' !== $context_type ) {
			$tags = array();

			foreach ( $this->tags as $tag ) {
				if ( empty( $tag['context'] ) || $context_type !== $tag['context'] ) {
					continue;
				}

				$tags[ $tag['tag'] ] = $tag;
			}
		}

		if ( ! empty( $tags ) && ! empty( $field ) ) {
			$tags = wp_list_pluck( $tags, $field );
		}

		return $tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks.
	 *
	 * @param string $content  Content to search for email tags.
	 * @param array  $tag_args Email template tag arguments.
	 *
	 * @since 1.0
	 * @since 2.0 $payment_id deprecated.
	 * @since 2.0 $tag_args added.
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $tag_args ) {

		// Check if there is at least one tag added.
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->tag_args = $tag_args;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->tag_args = null;

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

		return call_user_func( $this->tags[ $tag ]['func'], $this->tag_args, $tag );
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
 * @param string   $context     Email tag category
 */
function give_add_email_tag( $tag, $description, $func, $context = '' ) {
	Give()->email_tags->add( $tag, $description, $func, $context );
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
 * @param string    $content  Content to search for email tags.
 * @param array|int $tag_args Email template tag arguments.
 *
 * @since 1.0
 * @since 2.0 $payment_id deprecated.
 * @since 2.0 $tag_args added.
 *
 * @return string Content with email tags filtered out.
 */
function give_do_email_tags( $content, $tag_args ) {
	// Backward compatibility < 2.0
	if ( ! is_array( $tag_args ) ) {
		$tag_args = array( 'payment_id' => $tag_args );
	}

	// Replace all tags
	$content = Give()->email_tags->do_tags( $content, $tag_args );

	/**
	 * Filter the filtered content text.
	 *
	 * @since 1.0
	 * @since 2.0 $payment_meta, $payment_id removed and $tag_args added.
	 */
	$content = apply_filters( 'give_email_template_tags', $content, $tag_args );

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
		/*	Donation Payment */
		array(
			'tag'         => 'donation',
			'description' => esc_html__( 'The donation form name, and the donation level (if applicable).', 'give' ),
			'function'    => 'give_email_tag_donation',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'amount',
			'description' => esc_html__( 'The total donation amount with currency sign.', 'give' ),
			'function'    => 'give_email_tag_amount',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'price',
			'description' => esc_html__( 'The total donation amount with currency sign.', 'give' ),
			'function'    => 'give_email_tag_price',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'billing_address',
			'description' => esc_html__( 'The donor\'s billing address.', 'give' ),
			'function'    => 'give_email_tag_billing_address',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'date',
			'description' => esc_html__( 'The date of the donation.', 'give' ),
			'function'    => 'give_email_tag_date',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'payment_id',
			'description' => esc_html__( 'The unique ID number for this donation.', 'give' ),
			'function'    => 'give_email_tag_payment_id',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'payment_method',
			'description' => esc_html__( 'The method of payment used for this donation.', 'give' ),
			'function'    => 'give_email_tag_payment_method',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'payment_total',
			'description' => esc_html__( 'The payment total for this donation.', 'give' ),
			'function'    => 'give_email_tag_payment_total',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'receipt_id',
			'description' => esc_html__( 'The unique ID number for this donation receipt.', 'give' ),
			'function'    => 'give_email_tag_receipt_id',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'receipt_link',
			'description' => esc_html__( 'The donation receipt direct link, to view the receipt on the website.', 'give' ),
			'function'    => 'give_email_tag_receipt_link',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'receipt_link_url',
			'description' => esc_html__( 'The donation receipt direct URL, to view the receipt on the website.', 'give' ),
			'function'    => 'give_email_tag_receipt_link_url',
			'context'     => 'donation',
		),

		/* Donation Form */
		array(
			'tag'         => 'form_title',
			'description' => esc_html__( 'The donation form name.', 'give' ),
			'function'    => 'give_email_tag_form_title',
			'context'     => 'form',
		),

		/* Donor */
		array(
			'tag'         => 'name',
			'description' => esc_html__( 'The donor\'s first name.', 'give' ),
			'function'    => 'give_email_tag_first_name',
			'context'     => 'donor',
		),
		array(
			'tag'         => 'fullname',
			'description' => esc_html__( 'The donor\'s full name, first and last.', 'give' ),
			'function'    => 'give_email_tag_fullname',
			'context'     => 'donor',
		),
		array(
			'tag'         => 'username',
			'description' => esc_html__( 'The donor\'s user name on the site, if they registered an account.', 'give' ),
			'function'    => 'give_email_tag_username',
			'context'     => 'donor',
		),
		array(
			'tag'         => 'company_name',
			'description' => esc_html__( 'Company name.', 'give' ),
			'function'    => 'give_email_tag_company_name',
			'context'     => 'donation',
		),
		array(
			'tag'         => 'user_email',
			'description' => esc_html__( 'The donor\'s email address.', 'give' ),
			'function'    => 'give_email_tag_user_email',
			'context'     => 'donor',
		),
		array(
			'tag'         => 'email_access_link',
			'description' => esc_html__( 'The donor\'s email access link.', 'give' ),
			'function'    => 'give_email_tag_email_access_link',
			'context'     => 'donor',
		),

		/* General */
		array(
			'tag'         => 'sitename',
			'description' => esc_html__( 'The name of your site.', 'give' ),
			'function'    => 'give_email_tag_sitename',
			'context'     => 'general',
		),

		array(
			'tag'         => 'reset_password_link',
			'description' => esc_html__( 'The reset password link for user.', 'give' ),
			'function'    => 'give_email_tag_reset_password_link',
			'context'     => 'general',
		),

	);

	// Apply give_email_tags filter
	$email_tags = apply_filters( 'give_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		give_add_email_tag(
			$email_tag['tag'],
			$email_tag['description'],
			$email_tag['function'],
			( ! empty( $email_tag['context'] ) ? $email_tag['context'] : '' )
		);
	}

}

add_action( 'give_add_email_tags', 'give_setup_email_tags' );


/**
 * Email template tag: {name}
 *
 * The donor's first name.
 *
 * @param array $tag_args Email template tag arguments.
 *
 * @return string $firstname
 */
function give_email_tag_first_name( $tag_args ) {
	$user_info = array();
	$firstname = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$donor_info  = give_get_payment_meta_user_info( $tag_args['payment_id'] );
			$email_names = give_get_email_names( $donor_info );
			$firstname   = $email_names['name'];

			break;

		case give_check_variable( $tag_args, 'isset', 0, 'user_id' ):
			$firstname = Give()->donor_meta->get_meta(
				Give()->donors->get_column_by( 'id', 'user_id', $tag_args['user_id'] ),
				'_give_donor_first_name',
				true
			);
			break;

		/**
		 * Get Donor First Name from donor id
		 *
		 * @since 2.0
		 */
		case give_check_variable( $tag_args, 'isset', 0, 'donor_id' ):
			$firstname = Give()->donor_meta->get_meta( $tag_args['donor_id'], '_give_donor_first_name', true );
			break;
	}

	/**
	 * Filter the {firstname} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $firstname
	 * @param array  $tag_args
	 */
	$firstname = apply_filters( 'give_email_tag_first_name', $firstname, $tag_args );

	return $firstname;
}

/**
 * Email template tag: {fullname}
 *
 * The donor's full name, first and last.
 *
 * @param array $tag_args
 *
 * @return string $fullname
 */
function give_email_tag_fullname( $tag_args ) {
	$fullname = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$donor_info  = give_get_payment_meta_user_info( $tag_args['payment_id'] );
			$email_names = give_get_email_names( $donor_info );
			$fullname    = $email_names['fullname'];
			break;

		case give_check_variable( $tag_args, 'isset', 0, 'user_id' ):
			$fullname = Give()->donors->get_column_by( 'name', 'user_id', $tag_args['user_id'] );
			break;

		/**
		 * Get Donor Full Name from donor id
		 *
		 * @since 2.0
		 */
		case give_check_variable( $tag_args, 'isset', 0, 'donor_id' ):
			$fullname = Give()->donors->get_column( 'name', $tag_args['donor_id'] );
			break;
	}

	/**
	 * Filter the {fullname} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $fullname
	 * @param array  $tag_args
	 */
	$fullname = apply_filters( 'give_email_tag_fullname', $fullname, $tag_args );

	return $fullname;
}

/**
 * Email template tag: {username}
 *
 * The donor's user name on the site, if they registered an account.
 *
 * @param array $tag_args
 *
 * @return string username.
 */
function give_email_tag_username( $tag_args ) {
	$username = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$donor_info  = give_get_payment_meta_user_info( $tag_args['payment_id'] );
			$email_names = give_get_email_names( $donor_info );
			$username    = $email_names['username'];
			break;

		case give_check_variable( $tag_args, 'isset', 0, 'user_id' ):
			$user_info = get_user_by( 'id', $tag_args['user_id'] );
			$username  = $user_info->user_login;
			break;

		/**
		 * Get Donor Username from donor id
		 *
		 * @since 2.0
		 */
		case give_check_variable( $tag_args, 'isset', 0, 'donor_id' ):
			if ( $user_id = Give()->donors->get_column( 'user_id', $tag_args['donor_id'] ) ) {
				$user_info = get_user_by( 'id', $user_id );
				$username  = $user_info->user_login;
			}
			break;
	}

	/**
	 * Filter the {username} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $username
	 * @param array  $tag_args
	 */
	$username = apply_filters( 'give_email_tag_username', $username, $tag_args );

	return $username;
}

/**
 * Email template tag: {user_email}
 *
 * The donor's email address
 *
 * @param array $tag_args
 *
 * @return string user_email
 */
function give_email_tag_user_email( $tag_args ) {
	$email = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$email = give_get_donation_donor_email( $tag_args['payment_id'] );
			break;

		case give_check_variable( $tag_args, 'isset', 0, 'user_id' ):
			$user_info = get_user_by( 'id', $tag_args['user_id'] );
			$email     = $user_info->user_email;
			break;

		/**
		 * Get Donor Email from donor id
		 *
		 * @since 2.0
		 */
		case give_check_variable( $tag_args, 'isset', 0, 'donor_id' ):
			$email = Give()->donors->get_column( 'email', $tag_args['donor_id'] );
			break;
	}

	/**
	 * Filter the {email} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $email
	 * @param array  $tag_args
	 */
	$email = apply_filters( 'give_email_tag_user_email', $email, $tag_args );

	return $email;
}

/**
 * Email template tag: {billing_address}
 *
 * The donor's billing address
 *
 * @param array $tag_args
 *
 * @return string billing_address
 */
function give_email_tag_billing_address( $tag_args ) {
	$address = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$donation_address = give_get_donation_address( $tag_args['payment_id'] );
			$address  = $donation_address['line1'] . "\n";

			if ( ! empty( $donation_address['line2'] ) ) {
				$address .= $donation_address['line2'] . "\n";
			}

			$address .= $donation_address['city'] . ' ' . $donation_address['zip'] . ' ' . $donation_address['state'] . "\n";
			$address .= $donation_address['country'];
			break;
	}

	/**
	 * Filter the {billing_address} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $address
	 * @param array  $tag_args
	 */
	$address = apply_filters( 'give_email_tag_billing_address', $address, $tag_args );

	return $address;
}

/**
 * Email template tag: {date}
 *
 * Date of donation
 *
 * @param array $tag_args
 *
 * @return string date
 */
function give_email_tag_date( $tag_args ) {
	$date = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$date = date_i18n( give_date_format(), strtotime( get_the_date( $tag_args['payment_id'] ) ) );
			break;
	}

	/**
	 * Filter the {date} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $date
	 * @param array  $tag_args
	 */
	$date = apply_filters( 'give_email_tag_date', $date, $tag_args );

	return $date;
}

/**
 * Email template tag: give_amount.
 *
 * The total amount of the donation given.
 *
 * @param array $tag_args
 *
 * @return string amount
 */
function give_email_tag_amount( $tag_args ) {
	$amount = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$give_amount = give_donation_amount( $tag_args['payment_id'], true );
			$amount      = html_entity_decode( $give_amount, ENT_COMPAT, 'UTF-8' );
			break;
	}

	/**
	 * Filter the {amount} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $amount
	 * @param array  $tag_args
	 */
	$amount = apply_filters( 'give_email_tag_amount', $amount, $tag_args );

	return $amount;
}

/**
 * Email template tag: {price}
 *
 * The total price of the donation.
 *
 * @param array $tag_args
 *
 * @return string price
 */
function give_email_tag_price( $tag_args ) {
	return give_email_tag_amount( $tag_args );
}

/**
 * Email template tag: {payment_id}
 *
 * The unique ID number for this donation.
 *
 * @param array $tag_args
 *
 * @return int payment_id
 */
function give_email_tag_payment_id( $tag_args ) {
	$payment_id = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$payment_id = Give()->seq_donation_number->get_serial_code( $tag_args['payment_id'] );
			break;
	}

	/**
	 * Filter the {payment_id} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $payment_id
	 * @param array  $tag_args
	 */
	return apply_filters( 'give_email_tag_payment_id', $payment_id, $tag_args );
}

/**
 * Email template tag: {receipt_id}
 *
 * The unique ID number for this donation receipt
 *
 * @param array $tag_args
 *
 * @return string receipt_id
 */
function give_email_tag_receipt_id( $tag_args ) {
	$receipt_id = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$receipt_id = give_get_payment_key( $tag_args['payment_id'] );
			break;
	}

	/**
	 * Filter the {receipt_id} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $receipt_id
	 * @param array  $tag_args
	 */
	return apply_filters( 'give_email_tag_receipt_id', $receipt_id, $tag_args );
}

/**
 * Email template tag: {donation}
 *
 * Output the donation form name, and the donation level (if applicable).
 *
 * @param array $tag_args
 *
 * @return string $form_title
 */
function give_email_tag_donation( $tag_args ) {
	$donation_form_title = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$level_title         = give_has_variable_prices( give_get_payment_form_id( $tag_args['payment_id'] ) );
			$separator           = $level_title ? '-' : '';
			$donation_form_title = strip_tags(
				give_check_variable(
					give_get_donation_form_title(
						$tag_args['payment_id'],
						array( 'separator' => $separator, )
					),
					'empty',
					''
				)
			);
			break;
	}

	/**
	 * Filter the {donation_form_title} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $donation_form_title
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_donation',
		$donation_form_title,
		$tag_args
	);
}

/**
 * Email template tag: {form_title}
 *
 * Output the donation form name.
 *
 * @param array $tag_args
 *
 * @return string $form_title
 */
function give_email_tag_form_title( $tag_args ) {
	$donation_form_title = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$donation_form_title = give_get_payment_meta( $tag_args['payment_id'], '_give_payment_form_title' );
			break;
	}

	/**
	 * Filter the {form_title} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $form_title
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_form_title',
		$donation_form_title,
		$tag_args
	);
}

/**
 * Email template tag: {company_name}
 * Output the donation form company name filed.
 *
 * @since 2.0.7
 *
 * @param array $tag_args
 *
 * @return string $company_name
 */
function give_email_tag_company_name( $tag_args ) {
	$company_name = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$company_name = give_get_payment_meta( $tag_args['payment_id'], '_give_donation_company', true );
			break;
	}

	/**
	 * Filter the {company_name} email template tag output.
	 *
	 * @since 2.0.7
	 *
	 * @param string $company_name
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_company_name',
		$company_name,
		$tag_args
	);
}

/**
 * Email template tag: {payment_method}
 *
 * The method of payment used for this donation.
 *
 * @param array $tag_args
 *
 * @return string gateway
 */
function give_email_tag_payment_method( $tag_args ) {
	$payment_method = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$payment_method = give_get_gateway_checkout_label( give_get_payment_gateway( $tag_args['payment_id'] ) );
			break;
	}

	/**
	 * Filter the {payment_method} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $payment_method
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_payment_method',
		$payment_method,
		$tag_args
	);

}

/**
 * Email template tag: {payment_total}
 *
 * The payment donation for this donation.
 *
 * @since 1.8
 *
 * @param array $tag_args
 *
 * @return string
 */
function give_email_tag_payment_total( $tag_args ) {
	$payment_total = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$payment_total = give_currency_filter( give_get_payment_total( $tag_args['payment_id'] ) );
			break;
	}

	/**
	 * Filter the {payment_total} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $payment_total
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_payment_total',
		$payment_total,
		$tag_args
	);
}

/**
 * Email template tag: {sitename}
 *
 * The name of the site.
 *
 * @param array $tag_args
 *
 * @return string
 */
function give_email_tag_sitename( $tag_args = array() ) {
	$sitename = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	/**
	 * Filter the {sitename} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $sitename
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_sitename',
		$sitename,
		$tag_args
	);
}

/**
 * Email template tag: {receipt_link}
 *
 * The donation receipt direct link, to view the receipt on the website.
 *
 * @param array $tag_args
 *
 * @return string receipt_link
 */
function give_email_tag_receipt_link( $tag_args ) {
	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	$receipt_url = give_get_receipt_url( give_check_variable( $tag_args, 'empty', 0, 'payment_id' ) );

	// Bailout.
	if ( give_get_option( 'email_template' ) === 'none' ) {
		return $receipt_url;
	}


	$receipt_url = esc_url( add_query_arg( array(
		'payment_key' => give_get_payment_key( $tag_args['payment_id'] ),
	), give_get_history_page_uri() ) );

	$formatted = sprintf(
		'<a href="%1$s">%2$s</a>',
		$receipt_url,
		__( 'View it in your browser &raquo;', 'give' )
	);

	/**
	 * Filter the {receipt_link} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $formatted
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_receipt_link',
		$formatted,
		$tag_args
	);
}

/**
 * Email template tag: {receipt_link_url}
 *
 * The donation receipt direct URL, to view the receipt on the website.
 *
 * @since 1.7
 *
 * @param array $tag_args
 *
 * @return string receipt_url
 */
function give_email_tag_receipt_link_url( $tag_args ) {
	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	$receipt_link_url = give_get_receipt_url( give_check_variable( $tag_args, 'empty', 0, 'payment_id' ) );

	/**
	 * Filter the {receipt_link_url} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $receipt_link_url
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_receipt_link_url',
		$receipt_link_url,
		$tag_args
	);
}


/**
 * Get receipt_url
 *
 * @since 2.0
 *
 * @param int $payment_id
 *
 * @return string
 */
function give_get_receipt_url( $payment_id ) {
	$receipt_url = '';

	if ( $payment_id ) {
		$receipt_url = esc_url( add_query_arg( array(
			'payment_key' => give_get_payment_key( $payment_id ),
		), give_get_history_page_uri() ) );
	}

	return $receipt_url;
}


/**
 * Email template tag: {email_access_link}
 *
 * @since 2.0
 *
 * @param array $tag_args
 *
 * @return string
 */
function give_email_tag_email_access_link( $tag_args ) {
	$donor_id          = 0;
	$donor             = array();
	$email_access_link = '';

	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );

	switch ( true ) {
		case ! empty( $tag_args['donor_id'] ):
			$donor_id = $tag_args['donor_id'];
			$donor    = Give()->donors->get_by( 'id', $tag_args['donor_id'] );
			break;

		case ! empty( $tag_args['user_id'] ):
			$donor    = Give()->donors->get_by( 'user_id', $tag_args['user_id'] );
			$donor_id = $donor->id;
			break;

		default:
			$email_access_link = '';
	}

	// Set email access link if donor exist.
	if ( $donor_id ) {
		$verify_key = wp_generate_password( 20, false );

		// Generate a new verify key
		Give()->email_access->set_verify_key( $donor_id, $donor->email, $verify_key );

		$access_url = add_query_arg(
			array(
				'give_nl' => $verify_key,
			),
			give_get_history_page_uri()
		);

		// Add Payment Key to email access url, if it exists.
		if ( ! empty( $_GET['payment_key'] ) ) {
			$access_url = add_query_arg(
				array(
					'payment_key' => give_clean( $_GET['payment_key'] ),
				),
				$access_url
			);
		}

		if ( empty( $tag_args['email_content_type'] ) || 'text/html' === $tag_args['email_content_type'] ) {
			$email_access_link = sprintf(
				'<a href="%1$s" target="_blank">%2$s</a>',
				esc_url( $access_url ),
				__( 'View your donation history &raquo;', 'give' )
			);

		} else {

			$email_access_link = sprintf(
				'%1$s: %2$s',
				__( 'View your donation history', 'give' ),
				esc_url( $access_url )
			);
		}
	}

	/**
	 * Filter the {email_access_link} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $receipt_link_url
	 * @param array  $tag_args
	 */
	return apply_filters(
		'give_email_tag_email_access_link',
		$email_access_link,
		$tag_args
	);
}


/**
 * Backward compatibility for email tag param.
 *
 * Note: from 2.0 email tag render function will start accepting array values.
 *
 * @since 2.0
 *
 * @param $tag_args
 *
 * @return array
 */
function __give_20_bc_str_type_email_tag_param( $tag_args ) {
	if ( ! is_array( $tag_args ) ) {
		switch ( true ) {
			case ( 'give_payment' === get_post_type( $tag_args ) ):
				$tag_args = array( 'payment_id' => $tag_args );
				break;

			case ( ! is_wp_error( get_user_by( 'id', $tag_args ) ) ):
				$tag_args = array( 'user_id' => $tag_args );
				break;

			case ( Give()->donors->get_by( 'id', $tag_args ) ):
				$tag_args = array( 'donor_id' => $tag_args );
				break;

			case ( Give()->donors->get_by( 'user_id', $tag_args ) ):
				$tag_args = array( 'user_id' => $tag_args );
				break;
		}
	}

	return $tag_args;
}

/**
 * Email template tag: {reset_password_link}
 *
 * @param array $tag_args   Array of arguments for email tags.
 * @param int   $payment_id Donation ID.
 *
 * @since 2.0
 *
 * @return array
 */
function give_email_tag_reset_password_link( $tag_args, $payment_id ) {

	$reset_password_url = '';

	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$payment_id = Give()->seq_donation_number->get_serial_code( $tag_args['payment_id'] );
			break;

		case give_check_variable( $tag_args, 'isset', 0, 'user_id' ):
			$reset_password_url = give_get_reset_password_url( $tag_args['user_id'] );
			break;

		case give_check_variable( $tag_args, 'isset', 0, 'donor_id' ):
			$reset_password_url = give_get_reset_password_url( Give()->donors->get_column( 'user_id', $tag_args['donor_id'] ) );
			break;
	}

	if ( empty( $tag_args['email_content_type'] ) || 'text/html' === $tag_args['email_content_type'] ) {
		// Generate link, if Email content type is html.
		$reset_password_link = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( $reset_password_url ),
			__( 'Reset your password &raquo;', 'give' )
		);
	} else {
		$reset_password_link = sprintf(
			'%1$s: %2$s',
			__( 'Reset your password', 'give' ),
			esc_url( $reset_password_url )
		);
	}

	/**
	 * Filter the {reset_password_link} email template tag output.
	 *
	 * @param int   $payment_id Donation ID.
	 * @param array $tag_args   Email Tag arguments.
	 *
	 * @since 2.0
	 */
	return apply_filters(
		'give_email_tag_reset_password_link',
		$reset_password_link,
		$payment_id,
		$tag_args
	);
}

/**
 * Get Reset Password URL.
 *
 * @param $user_id
 *
 * @since 2.0
 *
 * @return mixed|string
 */
function give_get_reset_password_url( $user_id ) {
	$reset_password_url = '';

	// Proceed further only, if user_id exists.
	if ( $user_id ) {

		// Get User Object Details.
		$user = get_user_by( 'ID', $user_id );

		// Prepare Reset Password URL.
		$reset_password_url = esc_url( add_query_arg( array(
			'action' => 'rp',
			'key'    => get_password_reset_key( $user ),
			'login'  => $user->user_login,
		), wp_login_url() ) );
	}

	return $reset_password_url;
}


/**
 * This function helps to render meta data with from dynamic meta data email tag.
 * Note: meta data email tag must be in given format {meta_*}
 *
 * @since 2.0.3
 * @see   https://github.com/WordImpress/Give/issues/2801#issuecomment-365136602
 *
 * @param $content
 * @param $tag_args
 *
 * @return mixed
 */
function __give_render_metadata_email_tag( $content, $tag_args ) {
	preg_match_all( "/{meta_([A-z0-9\-\_\ ]+)}/s", $content, $matches );

	if ( ! empty( $matches[0] ) ) {
		$search = $replace = array();
		foreach ( $matches[0] as $index => $meta_tag ) {
			if ( in_array( $meta_tag, $search ) ) {
				continue;
			}

			$search[] = $meta_tag;

			$meta_tag     = str_replace( array( '{', 'meta_', '}' ), '', $meta_tag );
			$meta_tag_arr = array_map( 'trim', explode( ' ', $meta_tag, 2 ) );
			$meta_tag     = current( $meta_tag_arr );

			$meta_tag  = str_replace( array( '{', 'meta_', '}' ), '', $meta_tag );
			$type      = current( explode( '_', $meta_tag, 2 ) );
			$meta_name = preg_replace( "/^{$type}_/", '', $meta_tag );

			switch ( $type ) {
				case 'donation':

					//Bailout.
					if ( ! isset( $tag_args['payment_id'] ) ) {
						$replace[] = '';
						continue;
					}

					$meta_data = give_get_meta( absint( $tag_args['payment_id'] ), $meta_name, true, '' );

					if ( ! isset( $meta_tag_arr[1] ) || ! is_array( $meta_data ) ) {
						$replace[] = $meta_data;
					} elseif ( in_array( $meta_tag_arr[1], array_keys( $meta_data ) ) ) {
						$replace[] = $meta_data[ $meta_tag_arr[1] ];
					}

					break;

				case 'form':
					$form_id = isset( $tag_args['form_id'] ) ? absint( $tag_args['form_id'] ) : 0;

					// Bailout.
					if ( ! $form_id && isset( $tag_args['payment_id'] ) ) {
						$form_id = give_get_payment_form_id( $tag_args['payment_id'] );
					}

					$meta_data = give_get_meta( $form_id, $meta_name, true, '' );
					if ( ! isset( $meta_tag_arr[1] ) || ! is_array( $meta_data ) ) {
						$replace[] = $meta_data;
					} elseif ( in_array( $meta_tag_arr[1], array_keys( $meta_data ) ) ) {
						$replace[] = $meta_data[ $meta_tag_arr[1] ];
					}
					break;

				case 'donor':
					$donor_id = isset( $tag_args['donor_id'] ) ? absint( $tag_args['donor_id'] ) : 0;

					// Bailout.
					if ( ! $donor_id ) {
						if ( isset( $tag_args['payment_id'] ) ) {
							$donor_id = give_get_payment_donor_id( $tag_args['payment_id'] );
						} elseif ( isset( $tag_args['user_id'] ) ) {
							$donor_id = Give()->donors->get_column_by( 'id', 'user_id', $tag_args['user_id'] );
						}
					}

					$meta_data = Give()->donor_meta->get_meta( $donor_id, $meta_name, true );

					if( empty( $meta_data ) ) {
						$meta_data = Give()->donors->get_column_by( $meta_name, 'id', $donor_id );
					}

					if ( ! isset( $meta_tag_arr[1] ) || ! is_array( $meta_data ) ) {
						$replace[] = $meta_data;
					} elseif ( in_array( $meta_tag_arr[1], array_keys( $meta_data ) ) ) {
						$replace[] = $meta_data[ $meta_tag_arr[1] ];
					}

					break;

				default:
					$replace[] = end( $search );
			}
		}

		if ( ! empty( $search ) && ! empty( $replace ) ) {
			$content = str_replace( $search, $replace, $content );
		}
	}


	return $content;
}

add_filter( 'give_email_template_tags', '__give_render_metadata_email_tag', 10, 2 );
