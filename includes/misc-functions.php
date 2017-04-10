<?php
/**
 * Misc Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is Test Mode Enabled.
 *
 * @since 1.0
 *
 * @return bool $ret True if return mode is enabled, false otherwise
 */
function give_is_test_mode() {

	$ret = give_is_setting_enabled( give_get_option( 'test_mode' ) );

	return (bool) apply_filters( 'give_is_test_mode', $ret );

}

/**
 * Get the set currency
 *
 * @since 1.0
 * @return string The currency code
 */
function give_get_currency() {

	$currency = give_get_option( 'currency', 'USD' );

	return apply_filters( 'give_currency', $currency );
}

/**
 * Get the set currency position
 *
 * @since 1.3.6
 *
 * @return string The currency code
 */
function give_get_currency_position() {

	$currency_pos = give_get_option( 'currency_position', 'before' );

	return apply_filters( 'give_currency_position', $currency_pos );
}


/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */

function give_get_currencies() {
	$currencies = array(
		'USD'  => esc_html__( 'US Dollars ($)', 'give' ),
		'EUR'  => esc_html__( 'Euros (€)', 'give' ),
		'GBP'  => esc_html__( 'Pounds Sterling (£)', 'give' ),
		'AUD'  => esc_html__( 'Australian Dollars ($)', 'give' ),
		'BRL'  => esc_html__( 'Brazilian Real (R$)', 'give' ),
		'CAD'  => esc_html__( 'Canadian Dollars ($)', 'give' ),
		'CZK'  => esc_html__( 'Czech Koruna (Kč)', 'give' ),
		'DKK'  => esc_html__( 'Danish Krone (kr)', 'give' ),
		'HKD'  => esc_html__( 'Hong Kong Dollar ($)', 'give' ),
		'HUF'  => esc_html__( 'Hungarian Forint (Ft)', 'give' ),
		'ILS'  => esc_html__( 'Israeli Shekel (₪)', 'give' ),
		'JPY'  => esc_html__( 'Japanese Yen (¥)', 'give' ),
		'MYR'  => esc_html__( 'Malaysian Ringgits (RM)', 'give' ),
		'MXN'  => esc_html__( 'Mexican Peso ($)', 'give' ),
		'MAD'  => esc_html__( 'Moroccan Dirham (&#x2e;&#x62f;&#x2e;&#x645;)', 'give' ),
		'NZD'  => esc_html__( 'New Zealand Dollar ($)', 'give' ),
		'NOK'  => esc_html__( 'Norwegian Krone (Kr.)', 'give' ),
		'PHP'  => esc_html__( 'Philippine Pesos (₱)', 'give' ),
		'PLN'  => esc_html__( 'Polish Zloty (zł)', 'give' ),
		'SGD'  => esc_html__( 'Singapore Dollar ($)', 'give' ),
		'KRW'  => esc_html__( 'South Korean Won (₩)', 'give' ),
		'ZAR'  => esc_html__( 'South African Rand (R)', 'give' ),
		'SEK'  => esc_html__( 'Swedish Krona (kr)', 'give' ),
		'CHF'  => esc_html__( 'Swiss Franc (CHF)', 'give' ),
		'TWD'  => esc_html__( 'Taiwan New Dollars (NT$)', 'give' ),
		'THB'  => esc_html__( 'Thai Baht (฿)', 'give' ),
		'INR'  => esc_html__( 'Indian Rupee (₹)', 'give' ),
		'TRY'  => esc_html__( 'Turkish Lira (₺)', 'give' ),
		'RIAL' => esc_html__( 'Iranian Rial (﷼)', 'give' ),
		'RUB'  => esc_html__( 'Russian Rubles (руб)', 'give' )
	);

	return apply_filters( 'give_currencies', $currencies );
}


/**
 * Give Currency Symbol
 *
 * Given a currency determine the symbol to use. If no currency given, site default is used. If no symbol is determine,
 * the currency string is returned.
 *
 * @since      1.0
 *
 * @param  string $currency The currency string
 *
 * @return string           The symbol to use for the currency
 */
function give_currency_symbol( $currency = '' ) {

	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}
	switch ( $currency ) :
		case 'GBP' :
			$symbol = '£';
			break;
		case 'BRL' :
			$symbol = 'R$';
			break;
		case 'EUR' :
			$symbol = '€';
			break;
		case 'NOK' :
			$symbol = 'Kr.';
			break;
		case 'INR' :
			$symbol = '₹';
			break;
		case 'USD' :
		case 'AUD' :
		case 'CAD' :
		case 'HKD' :
		case 'MXN' :
		case 'SGD' :
			$symbol = '$';
			break;
		case 'JPY' :
			$symbol = '¥';
			break;
		case 'THB' :
			$symbol = '฿';
			break;
		case 'TRY' :
			$symbol = '₺';
			break;
		case 'TWD' :
			$symbol = 'NT$';
			break;
		case 'ILS' :
			$symbol = '₪';
			break;
		case 'RIAL' :
			$symbol = '﷼';
			break;
		case 'RUB' :
			$symbol = 'руб';
			break;
		case 'DKK' :
		case 'SEK' :
			$symbol = 'kr';
			break;
		case 'PLN' :
			$symbol = 'zł';
			break;
		case 'PHP' :
			$symbol = '₱';
			break;
		case 'MYR' :
			$symbol = 'RM';
			break;
		case 'HUF' :
			$symbol = 'Ft';
			break;
		case 'CZK' :
			$symbol = 'Kč';
			break;
		case 'KRW' :
			$symbol = '₩';
			break;
		case 'ZAR' :
			$symbol = 'R';
			break;
		case 'MAD' :
			$symbol = '&#x2e;&#x62f;&#x2e;&#x645;';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;


	return apply_filters( 'give_currency_symbol', $symbol, $currency );
}


/**
 * Get the current page URL
 *
 * @since 1.0
 * @return string $current_url Current page URL
 */
function give_get_current_page_url() {

	global $wp;

	if ( get_option( 'permalink_structure' ) ) {
		$base = trailingslashit( home_url( $wp->request ) );
	} else {
		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );
	}

	$scheme      = is_ssl() ? 'https' : 'http';
	$current_uri = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$current_uri = home_url( '/' );
	}

	return apply_filters( 'give_get_current_page_url', $current_uri );

}


/**
 * Verify credit card numbers live?
 *
 * @since 1.0
 *
 * @return bool $ret True is verify credit cards is live
 */
function give_is_cc_verify_enabled() {

	$ret = true;

	/*
	 * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards
	 * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
	 */
	$gateways = give_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
		$ret = true;
	} else if ( count( $gateways ) == 1 ) {
		$ret = false;
	} else if ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
		$ret = false;
	}

	return (bool) apply_filters( 'give_verify_credit_cards', $ret );
}

/**
 * Retrieve timezone
 *
 * @since 1.0
 * @return string $timezone The timezone ID
 */
function give_get_timezone_id() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) ) {
		return 'UTC';
	}

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually
	if ( $timezone === false ) {

		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}
	}

	// fallback
	return 'UTC';
}


/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0
 * @return string $ip User's IP address
 */
function give_get_ip() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return apply_filters( 'give_get_ip', $ip );
}


/**
 * Store Donation Data in Sessions
 *
 * Used for storing info about donation
 *
 * @since 1.0
 *
 * @param $purchase_data
 *
 * @uses  Give()->session->set()
 */
function give_set_purchase_session( $purchase_data = array() ) {
	Give()->session->set( 'give_purchase', $purchase_data );
	Give()->session->set( 'give_email', $purchase_data['user_email'] );
}

/**
 * Retrieve Donation Data from Session
 *
 * Used for retrieving info about donation
 * after completing a donation
 *
 * @since 1.0
 * @uses  Give()->session->get()
 * @return mixed array | false
 */
function give_get_purchase_session() {
	return Give()->session->get( 'give_purchase' );
}

/**
 * Get Donation Summary
 *
 * Retrieves the donation summary.
 *
 * @since       1.0
 *
 * @param array $purchase_data
 * @param bool  $email
 *
 * @return string
 */
function give_get_purchase_summary( $purchase_data, $email = true ) {
	$summary = '';

	if ( $email ) {
		$summary .= $purchase_data['user_email'] . ' - ';
	}

	$summary .= get_the_title( $purchase_data['post_data']['give-form-id'] );

	return $summary;
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 1.0
 * @return string $host if detected, false otherwise
 */
function give_get_host() {
	$host = false;

	if ( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif ( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif ( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif ( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif ( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif ( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}


/**
 * Check site host
 *
 * @since 1.0
 *
 * @param bool /string $host The host to check
 *
 * @return bool true if host matches, false if not
 */
function give_is_host( $host = false ) {

	$return = false;

	if ( $host ) {
		$host = str_replace( ' ', '', strtolower( $host ) );

		switch ( $host ) {
			case 'wpengine':
				if ( defined( 'WPE_APIKEY' ) ) {
					$return = true;
				}
				break;
			case 'pagely':
				if ( defined( 'PAGELYBIN' ) ) {
					$return = true;
				}
				break;
			case 'icdsoft':
				if ( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
					$return = true;
				}
				break;
			case 'networksolutions':
				if ( DB_HOST == 'mysqlv5' ) {
					$return = true;
				}
				break;
			case 'ipage':
				if ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'ipower':
				if ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'mediatemplegrid':
				if ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'pairnetworks':
				if ( strpos( DB_HOST, '.pair.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'rackspacecloud':
				if ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'sysfix.eu':
			case 'sysfix.eupowerhosting':
				if ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
					$return = true;
				}
				break;
			case 'flywheel':
				if ( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
					$return = true;
				}
				break;
			default:
				$return = false;
		}
	}

	return $return;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook give_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'give_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'give_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function    The function that was called.
 * @param string $version     The plugin version that deprecated the function.
 * @param string $replacement Optional. The function that should have been called.
 * @param array  $backtrace   Optional. Contains stack backtrace of deprecated function.
 */
function _give_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {

	/**
	 * Fires while give deprecated function call occurs.
	 *
	 * Allow you to hook to deprecated function call.
	 *
	 * @since 1.0
	 *
	 * @param string $function    The function that was called.
	 * @param string $replacement Optional. The function that should have been called.
	 * @param string $version     The plugin version that deprecated the function.
	 */
	do_action( 'give_deprecated_function_run', $function, $replacement, $version );

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'give_deprecated_function_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Give version %2$s! Use %3$s instead.', 'give' ), $function, $version, $replacement ) );
			trigger_error( print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Give version %2$s with no alternative available.', 'give' ), $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Give Get Admin ID
 *
 * Helper function to return the ID of the post for admin usage
 *
 * @return string $post_id
 */
function give_get_admin_post_id() {
	$post_id = isset( $_GET['post'] ) ? $_GET['post'] : null;
	if ( ! $post_id && isset( $_POST['post_id'] ) ) {
		$post_id = $_POST['post_id'];
	}

	return $post_id;
}

/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0
 * @return string Arg separator output
 */
function give_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}


/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param int $n
 *
 * @return string Short month name
 */
function give_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
}


/**
 * Checks whether function is disabled.
 *
 * @since 1.0
 *
 * @param string $function Name of the function.
 *
 * @return bool Whether or not function is disabled.
 */
function give_is_func_disabled( $function ) {
	$disabled = explode( ',', ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}


/**
 * Give Newsletter
 *
 * Returns the main Give newsletter form
 */
function give_get_newsletter() { ?>

    <p class="newsletter-intro"><?php esc_html_e( 'Be sure to sign up for the Give newsletter below to stay informed of important updates and news.', 'give' ); ?></p>

    <div class="give-newsletter-form-wrap">

        <form action="//givewp.us3.list-manage.com/subscribe/post?u=3ccb75d68bda4381e2f45794c&amp;id=12a081aa13"
              method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate"
              target="_blank" novalidate>
            <div class="give-newsletter-confirmation">
                <p><?php esc_html_e( 'Thanks for Subscribing!', 'give' ); ?> :)</p>
            </div>

            <table class="form-table give-newsletter-form">
                <tr valign="middle">
                    <td>
                        <label for="mce-EMAIL"
                               class="screen-reader-text"><?php esc_html_e( 'Email Address (required)', 'give' ); ?></label>
                        <input type="email" name="EMAIL" id="mce-EMAIL"
                               placeholder="<?php esc_attr_e( 'Email Address (required)', 'give' ); ?>"
                               class="required email" value="">
                    </td>
                    <td>
                        <label for="mce-FNAME"
                               class="screen-reader-text"><?php esc_html_e( 'First Name', 'give' ); ?></label>
                        <input type="text" name="FNAME" id="mce-FNAME"
                               placeholder="<?php esc_attr_e( 'First Name', 'give' ); ?>" class="" value="">
                    </td>
                    <td>
                        <label for="mce-LNAME"
                               class="screen-reader-text"><?php esc_html_e( 'Last Name', 'give' ); ?></label>
                        <input type="text" name="LNAME" id="mce-LNAME"
                               placeholder="<?php esc_attr_e( 'Last Name', 'give' ); ?>" class="" value="">
                    </td>
                    <td>
                        <input type="submit" name="subscribe" id="mc-embedded-subscribe" class="button"
                               value="<?php esc_attr_e( 'Subscribe', 'give' ); ?>">
                    </td>
                </tr>
            </table>
        </form>

        <div style="position: absolute; left: -5000px;">
            <input type="text" name="b_3ccb75d68bda4381e2f45794c_12a081aa13" tabindex="-1" value="">
        </div>

    </div>

    <script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script>
    <script type='text/javascript'>(function ($) {
			window.fnames = new Array();
			window.ftypes = new Array();
			fnames[0] = 'EMAIL';
			ftypes[0] = 'email';
			fnames[1] = 'FNAME';
			ftypes[1] = 'text';
			fnames[2] = 'LNAME';
			ftypes[2] = 'text';

			//Successful submission
			$('form[name="mc-embedded-subscribe-form"]').on('submit', function () {

				var email_field = $(this).find('#mce-EMAIL').val();
				if (!email_field) {
					return false;
				}
				$(this).find('.give-newsletter-confirmation').show().delay(5000).slideUp();
				$(this).find('.give-newsletter-form').hide();

			});


		}(jQuery));
		var $mcj = jQuery.noConflict(true);


    </script>
    <!--End mc_embed_signup-->

<?php }


/**
 * Social Media Like Buttons
 *
 * Various social media elements to Give
 */
function give_social_media_elements() {
	?>

    <div class="social-items-wrap">

        <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fwpgive&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;font&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=220596284639969"
                scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:21px;"
                allowTransparency="true"></iframe>

        <a href="https://twitter.com/givewp" class="twitter-follow-button" data-show-count="false"><?php
			printf(
			/* translators: %s: Give twitter user @givewp */
				esc_html_e( 'Follow %s', 'give' ),
				'@givewp'
			);
			?></a>
        <script>!function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
				if (!d.getElementById(id)) {
					js = d.createElement(s);
					js.id = id;
					js.src = p + '://platform.twitter.com/widgets.js';
					fjs.parentNode.insertBefore(js, fjs);
				}
			}(document, 'script', 'twitter-wjs');
        </script>

    </div>
    <!--/.social-items-wrap -->

	<?php
}


/**
 * Create SVG library function
 *
 * @param string $icon
 *
 * @return string
 */
function give_svg_icons( $icon ) {

	// Store your SVGs in an associative array
	$svgs = array(
		'microphone'    => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjY0cHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgLTIwIDY0IDEyMCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNjQgMTAwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPHBhdGggZD0iTTYyLDM2LjIxNWgtM2MtMS4xLDAtMiwwLjktMiwyVjUyYzAsNi42ODYtNS4yNjYsMTgtMjUsMThTNyw1OC42ODYsNyw1MlYzOC4yMTVjMC0xLjEtMC45LTItMi0ySDJjLTEuMSwwLTIsMC45LTIsMlY1Mg0KCQkJYzAsMTEuMTg0LDguMjE1LDIzLjE1MiwyNywyNC44MDFWOTBIMTRjLTEuMSwwLTIsMC44OTgtMiwydjZjMCwxLjEsMC45LDIsMiwyaDM2YzEuMSwwLDItMC45LDItMnYtNmMwLTEuMTAyLTAuOS0yLTItMkgzN1Y3Ni44MDENCgkJCUM1NS43ODUsNzUuMTUyLDY0LDYzLjE4NCw2NCw1MlYzOC4yMTVDNjQsMzcuMTE1LDYzLjEsMzYuMjE1LDYyLDM2LjIxNXoiLz4NCgkJPHBhdGggZD0iTTMyLDYwYzExLjczMiwwLDE1LTQuODE4LDE1LThWMzYuMjE1SDE3VjUyQzE3LDU1LjE4MiwyMC4yNjYsNjAsMzIsNjB6Ii8+DQoJCTxwYXRoIGQ9Ik00Nyw4YzAtMy4xODQtMy4yNjgtOC0xNS04QzIwLjI2NiwwLDE3LDQuODE2LDE3LDh2MjEuMjE1aDMwVjh6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=',
		'alert'         => "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjI4LjkzOHB4IiBoZWlnaHQ9IjI1LjAwNXB4IiB2aWV3Qm94PSIwIDAgMjguOTM4IDI1LjAwNSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjguOTM4IDI1LjAwNTsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHBhdGggc3R5bGU9ImZpbGw6IzAwMDAwMDsiIGQ9Ik0yOC44NTksMjQuMTU4TDE0Ljk1NywwLjI3OUMxNC44NTYsMC4xMDYsMTQuNjcsMCwxNC40NjgsMGMtMC4xOTgsMC0wLjM4MywwLjEwNi0wLjQ4MSwwLjI3OQ0KCUwwLjA3OSwyNC4xNThjLTAuMTAyLDAuMTc1LTAuMTA2LDAuMzg5LTAuMDA2LDAuNTY1YzAuMTAzLDAuMTc0LDAuMjg3LDAuMjgyLDAuNDg4LDAuMjgyaDI3LjgxNGMwLjIwMSwwLDAuMzg5LTAuMTA4LDAuNDg4LTAuMjgyDQoJYzAuMDQ3LTAuMDg4LDAuMDc0LTAuMTg2LDAuMDc0LTAuMjgxQzI4LjkzOCwyNC4zNDMsMjguOTExLDI0LjI0NSwyOC44NTksMjQuMTU4eiBNMTYuMzY5LDguNDc1bC0wLjQ2Miw5LjQ5M2gtMi4zODlsLTAuNDYxLTkuNDkzDQoJSDE2LjM2OXogTTE0LjcxMSwyMi44MjhoLTAuMDQyYy0xLjA4OSwwLTEuODQzLTAuODE3LTEuODQzLTEuOTA3YzAtMS4xMzEsMC43NzQtMS45MDcsMS44ODUtMS45MDdzMS44NDYsMC43NzUsMS44NjcsMS45MDcNCglDMTYuNTc5LDIyLjAxMSwxNS44NDQsMjIuODI4LDE0LjcxMSwyMi44Mjh6Ii8+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==",
		'placemark'     => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMTAwcHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAxMDAgMTAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwYXRoIGQ9Ik01MC40MzQsMjAuMjcxYy0xMi40OTksMC0yMi42NjgsMTAuMTY5LTIyLjY2OCwyMi42NjhjMCwxMS44MTQsMTguODE1LDMyLjE1NSwyMC45NiwzNC40MzdsMS43MDgsMS44MTZsMS43MDgtMS44MTYNCgkJYzIuMTQ1LTIuMjgxLDIwLjk2LTIyLjYyMywyMC45Ni0zNC40MzdDNzMuMTAzLDMwLjQ0LDYyLjkzNCwyMC4yNzEsNTAuNDM0LDIwLjI3MXogTTUwLjQzNCw1Mi4zMmMtNS4xNzIsMC05LjM4LTQuMjA4LTkuMzgtOS4zOA0KCQlzNC4yMDgtOS4zOCw5LjM4LTkuMzhjNS4xNzMsMCw5LjM4LDQuMjA4LDkuMzgsOS4zOFM1NS42MDcsNTIuMzIsNTAuNDM0LDUyLjMyeiIvPg0KPC9nPg0KPC9zdmc+DQo=',
		'give_grey'     => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjEwMC4xIDAgNDAwIDQwMCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAxMDAuMSAwIDQwMCA0MDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnIGlkPSJMYXllcl8xXzFfIj48Y2lyY2xlIGZpbGw9IiM2NkJCNkEiIGN4PSItNDA3LjMiIGN5PSIzNDYuMyIgcj0iNDIuMiIvPjxnPjxnPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNzg2LjQsMTMzLjh2LTEyLjVoNC44YzMuOCwwLDYuNiwyLjUsNi42LDYuNHMtMi44LDYuNC02LjYsNi40aC00LjhWMTMzLjh6IE0tNzc3LjUsMTI3LjVjMC0yLjMtMS4zLTMuOC0zLjgtMy44aC0yLjN2Ny45aDIuM0MtNzc5LDEzMS42LTc3Ny41LDEyOS44LTc3Ny41LDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNzcxLjYsMTMzLjh2LTEyLjVoOC45djIuM2gtNi4xdjIuNWg2LjF2Mi4zaC02LjF2Mi44aDYuMXYyLjNoLTguOVYxMzMuOHoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTc0OC41LDEzMy44di04LjdsLTMuNiw4LjdoLTEuM2wtMy42LTguN3Y4LjdoLTIuNXYtMTIuNWgzLjhsMy4xLDcuNmwzLjEtNy42aDMuOHYxMi41SC03NDguNXoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTc0Mi40LDEyNy41YzAtMy44LDIuOC02LjQsNi42LTYuNHM2LjYsMi44LDYuNiw2LjRjMCwzLjgtMi44LDYuNC02LjYsNi40Qy03MzkuOCwxMzQuMS03NDIuNCwxMzEuMy03NDIuNCwxMjcuNXogTS03MzIuMiwxMjcuNWMwLTIuMy0xLjUtNC4xLTMuOC00LjFjLTIuMywwLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFDLTczMy43LDEzMS42LTczMi4yLDEyOS44LTczMi4yLDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNzI2LjgsMTI3LjVjMC0zLjgsMi44LTYuNCw2LjYtNi40YzIuOCwwLDQuMywxLjUsNS4zLDMuMWwtMi4zLDFjLTAuNS0xLTEuNS0xLjgtMy4xLTEuOGMtMi4zLDAtMy44LDEuOC0zLjgsNC4xYzAsMi4zLDEuNSw0LjEsMy44LDQuMWMxLjMsMCwyLjMtMC44LDMuMS0xLjhsMi4zLDFjLTEsMS41LTIuNSwzLjEtNS4zLDMuMUMtNzIzLjgsMTM0LjEtNzI2LjgsMTMxLjMtNzI2LjgsMTI3LjV6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS03MDQuNywxMzMuOGwtMi41LTQuM2gtMnY0LjNoLTIuNXYtMTIuNWg1LjljMi41LDAsNC4xLDEuOCw0LjEsNC4xYzAsMi4zLTEuMywzLjMtMi44LDMuOGwyLjgsNC44aC0yLjhWMTMzLjh6IE0tNzA0LjUsMTI1LjJjMC0xLTAuOC0xLjgtMS44LTEuOGgtMi44djMuM2gyLjhDLTcwNS41LDEyNy03MDQuNSwxMjYuNS03MDQuNSwxMjUuMnoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTY4OS43LDEzMy44bC0wLjgtMmgtNS4zbC0wLjgsMmgtMy4xbDQuOC0xMi41aDMuM2w0LjgsMTIuNUgtNjg5Ljd6IE0tNjkzLjMsMTIzLjlsLTIsNS4zaDMuOEwtNjkzLjMsMTIzLjl6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS02ODIuNiwxMzMuOHYtMTAuMmgtMy42di0yLjNoOS45djIuM2gtMy42djEwLjJILTY4Mi42eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjczLjIsMTMzLjh2LTEyLjVoMi41djEyLjVILTY3My4yeiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjY3LDEzMy44di0ybDUuOS03LjloLTUuOXYtMi4zaDkuNHYybC01LjksOC4xaDYuMXYyLjNoLTkuN1YxMzMuOHoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTY1NC4xLDEzMy44di0xMi41aDIuNXYxMi41SC02NTQuMXoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTYzOS4xLDEzMy44bC01LjktOC4xdjguMWgtMi41di0xMi41aDIuOGw1LjksNy45di03LjloMi41djEyLjVILTYzOS4xeiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjMzLjIsMTI3LjVjMC00LjEsMy4xLTYuNCw2LjYtNi40YzIuNSwwLDQuMywxLjMsNS4xLDIuOGwtMi4zLDEuM2MtMC41LTAuOC0xLjUtMS41LTMuMS0xLjVjLTIuMywwLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFjMSwwLDItMC41LDIuNS0xdi0xLjVoLTMuM1YxMjdoNS45djQuOGMtMS4zLDEuNS0zLjEsMi4zLTUuMywyLjNDLTYzMC4yLDEzNC4xLTYzMy4yLDEzMS42LTYzMy4yLDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjEyLjEsMTI3LjVjMC00LjEsMy4xLTYuNCw2LjYtNi40YzIuNSwwLDQuMywxLjMsNS4xLDIuOGwtMi4zLDEuM2MtMC41LTAuOC0xLjUtMS41LTMuMS0xLjVjLTIuMywwLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFjMSwwLDItMC41LDIuNS0xdi0xLjVoLTMuM1YxMjdoNS45djQuOGMtMS4zLDEuNS0zLjEsMi4zLTUuMywyLjNDLTYwOSwxMzQuMS02MTIuMSwxMzEuNi02MTIuMSwxMjcuNXoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTU5Ni42LDEzMy44di0xMi41aDguOXYyLjNoLTYuMXYyLjVoNi4xdjIuM2gtNi4xdjIuOGg2LjF2Mi4zaC04LjlWMTMzLjh6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01NzUuNywxMzMuOGwtNS45LTguMXY4LjFoLTIuNXYtMTIuNWgyLjhsNS45LDcuOXYtNy45aDIuNXYxMi41SC01NzUuN3oiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTU2OS4xLDEzMy44di0xMi41aDguOXYyLjNoLTYuMXYyLjVoNi4xdjIuM2gtNi4xdjIuOGg2LjF2Mi4zaC04LjlWMTMzLjh6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01NDkuNywxMzMuOGwtMi41LTQuM2gtMnY0LjNoLTIuNXYtMTIuNWg1LjljMi41LDAsNC4xLDEuOCw0LjEsNC4xYzAsMi4zLTEuMywzLjMtMi44LDMuOGwyLjgsNC44aC0yLjhWMTMzLjh6IE0tNTQ5LjUsMTI1LjJjMC0xLTAuOC0xLjgtMS44LTEuOGgtMi44djMuM2gyLjhDLTU1MC4zLDEyNy01NDkuNSwxMjYuNS01NDkuNSwxMjUuMnoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTU0My45LDEyNy41YzAtMy44LDIuOC02LjQsNi42LTYuNHM2LjYsMi44LDYuNiw2LjRjMCwzLjgtMi44LDYuNC02LjYsNi40Qy01NDEuMywxMzQuMS01NDMuOSwxMzEuMy01NDMuOSwxMjcuNXogTS01MzMuNywxMjcuNWMwLTIuMy0xLjUtNC4xLTMuOC00LjFzLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFDLTUzNS4yLDEzMS42LTUzMy43LDEyOS44LTUzMy43LDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNTI4LjYsMTMyLjFsMS41LTJjMC44LDEsMi4zLDEuOCw0LjEsMS44YzEuNSwwLDIuMy0wLjgsMi4zLTEuM2MwLTIuMy03LjEtMC44LTcuMS01LjNjMC0yLDEuOC0zLjgsNC44LTMuOGMyLDAsMy42LDAuNSw0LjgsMS44bC0xLjUsMmMtMS0xLTIuMy0xLjMtMy42LTEuM2MtMSwwLTEuOCwwLjUtMS44LDEuM2MwLDIsNy4xLDAuOCw3LjEsNS4zYzAsMi4zLTEuNSw0LjEtNS4xLDQuMUMtNTI1LjYsMTM0LjEtNTI3LjQsMTMzLjEtNTI4LjYsMTMyLjF6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01MTUuMSwxMzMuOHYtMTIuNWgyLjV2MTIuNUgtNTE1LjF6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01MDUuNywxMzMuOHYtMTAuMmgtMy42di0yLjNoOS45djIuM2gtMy42djEwLjJILTUwNS43eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNDkyLjcsMTMzLjh2LTUuMWwtNC44LTcuNGgzLjFsMy4xLDUuMWwzLjEtNS4xaDMuMWwtNC44LDcuNHY1LjFILTQ5Mi43eiIvPjwvZz48Zz48Zz48cGF0aCBmaWxsPSIjNjZCQjZBIiBkPSJNLTQ4NS45LDQ0LjNoLTEuM2wwLjMsMS4zYzIsOS45LDAuMywyNC43LTcuNCwzMy44Yy00LjMsNS4zLTkuOSw4LjEtMTYuOCw4LjFjLTEwLjksMC0xNS0xMy0xNS41LTI3LjdjMTcuOC00LjMsMjkuOC0xNS41LDI5LjgtMjguNWMwLTkuNC0yLjgtMjQuOS0yMS40LTI0LjljLTE3LjYsMC0yNi41LDI2LjItMjguMiw0NC41Yy04LjktMC4zLTE1LjUtNC4zLTE5LjYtOC4xYzEuNS02LjQsMi4zLTEyLjIsMi4zLTE3LjZjMC03LjQtNS4xLTEwLjctOS45LTEwLjdjLTYuOSwwLTE0LDYuNi0xNCwxOS4zYzAsNy42LDIuOCwxNCw4LjcsMTguNmMtNS4xLDEyLTEzLjcsMjIuMS0xNi41LDI1LjRjLTIuMy00LjgtOS43LTIyLjQtMTItNDEuNWMyLjgtNy42LDQuMy0xNCw0LjMtMTdjMC00LjgtMy4xLTcuNi04LjEtNy42Yy02LjksMC0xNy44LDQuMy0xOC4xLDQuNmwtMC41LDAuM3YwLjhjMCwwLjMsMy4zLDE1LjUsNi42LDMyLjNjLTYuNCwxMC40LTE3LjYsMjcuNy0yMy4yLDI3LjdjLTEwLjIsMCw2LjYtNTIuMi0wLjgtNTMuOWMtMC4zLDAtMC41LDAtMC44LDAuM2MtMy42LDIuMy00My41LDI0LjQtOTYuNywyNC40YzAsMCwwLDEsMC41LDJjMC4zLDAuOCwxLDEuNSwxLDEuNWMxNSwxLjgsMzYuNC0wLjMsNTIuNy0yLjVjLTkuNCwyMC4xLTI2LDMzLjMtNDEuMiwzMy4zYy0yOC44LDAtNTAuOS0zNC45LTUwLjktMzQuOWM4LjktNy45LDIzLjQtMzMuMyw0NC44LTMzLjNjMjEuMSwwLDMwLjMsMTEuNywzMC4zLDExLjdsMi4zLTMuOGMwLDAtOS45LTM0LjYtMzcuOS0zNC42cy01Ny44LDQ1LjgtNzUuMSw1Ni41YzAsMCwyMy45LDU2LjUsNzYuMSw1Ni41YzQzLjgsMCw1NS00Miw1Ny01Mi4yYzEwLjctMS41LDE4LjEtMy4xLDE4LjEtMy4xcy0yLjgsMjEuNC0yLjgsMzAuM3M5LjksMTguMywxOC4xLDE4LjNjNi45LDAsMjAuOS0xNC4yLDMxLTMxLjZsMC41LDJjNS4zLDE5LjYsMTIsMjkuOCwxOS44LDI5LjhjNy45LDAsMjAuOS0xNi4zLDI5LjMtMzYuOWM4LjQsMy42LDE4LjMsNC42LDI0LjIsNC44YzIuMywzNS40LDMxLjgsMzYuNCwzNS40LDM2LjRjMjEuOSwwLDQwLjUtMTUuOCw0MC41LTM0LjRDLTQ3MC42LDQ0LjUtNDg1LjYsNDQuMy00ODUuOSw0NC4zeiBNLTUxMi42LDI5LjVjMCwwLTAuMywxMS43LTEzLjUsMTcuNmMxLjMtMTUuNSw1LjEtMjkuNSw3LjYtMjkuNUMtNTE1LjYsMTcuOC01MTIuNiwyMi4xLTUxMi42LDI5LjV6Ii8+PHBhdGggZmlsbD0iIzY2QkI2QSIgZD0iTS02NjUsMTUuNWMwLDAuNSwwLjMsMC44LDAuOCwxYzEwLjQsMS41LDE3LjMtMS44LDE3LjMtMTguNmMwLTE1LjgtMTYuMy0zLjMtMTkuMy0xYy0wLjMsMC4zLTAuMywwLjUtMC4zLDFDLTY2My43LDQuMS02NjQuOCwxMy02NjUsMTUuNXoiLz48L2c+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMjg5LjU4NjQiIHkxPSIzNzMuMjM3OSIgeDI9Ii0yODIuODg0MiIgeTI9IjM3NS40NzE5IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xXykiIGQ9Ik0tNjIzLDQ5LjRjLTQuMSw2LjktMTAuMiwxNi4zLTE1LjUsMjIuMWMxLjMsMy4xLDIuOCw2LjksNC4zLDkuOWM0LjgtNS4zLDkuNy0xMi4yLDE0LTE5LjNMLTYyMyw0OS40eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMl8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI2OS4wNTc3IiB5MT0iMzcxLjU0NDEiIHgyPSItMjY1LjE3MDUiIHkyPSIzNzguMzgwMiIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfMl8pIiBkPSJNLTU3NC43LDU0LjdjLTItMS0zLjgtMi41LTMuOC0yLjVjLTMuNiw3LjktOC40LDE1LjMtMTIuMiwyMC4xYzEuOCwyLjUsNC44LDUuOSw3LjEsOC40YzQuNi02LjQsOS40LTE0LjgsMTMtMjMuN0MtNTcwLjQsNTYuNy01NzIuNiw1Ni01NzQuNyw1NC43eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfM18iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI0OC42NDE2IiB5MT0iMzY4LjM4MzUiIHgyPSItMjQ5LjQ0NTkiIHkyPSIzNzUuNTMyMyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfM18pIiBkPSJNLTUyNi4zLDU5LjhjMCwwLTUuMSwxLTEwLjIsMS41cy05LjksMC4zLTkuOSwwLjNjMC44LDEwLjIsMy42LDE3LjMsNy40LDIyLjZsMTguNi0xLjVDLTUyNC4zLDc3LjYtNTI2LjEsNjktNTI2LjMsNTkuOHoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzRfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yNDkuOCIgeTE9IjM4My41ODEiIHgyPSItMjQ5LjgiIHkyPSIzNzYuMzc2MyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfNF8pIiBkPSJNLTU0MS4xLDI4LjhMLTU0MS4xLDI4LjhjLTAuNSwxLjUtMS4zLDMuMy0xLjgsNS4xYzAsMC41LTAuMywwLjgtMC4zLDEuM2MtMSwzLjMtMS44LDYuNi0yLjMsOS45YzAsMC41LTAuMywwLjgtMC4zLDEuM2MtMC4zLDEuNS0wLjUsMy4xLTAuNSw0LjZjMTIsMCwyMC4xLTMuNiwyMC4xLTMuNmMwLTEuMywwLjMtMi4zLDAuMy0zLjZjMC0wLjMsMC0wLjUsMC0wLjhjMC0xLDAuMy0xLjgsMC4zLTIuOGMwLTAuMywwLTAuNSwwLTAuOGMwLjMtMi4zLDAuOC00LjYsMS02LjZMLTU0MS4xLDI4Ljh6IE0tNTQ2LjQsNTAuNkwtNTQ2LjQsNTAuNkwtNTQ2LjQsNTAuNkwtNTQ2LjQsNTAuNnoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzVfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zMTMiIHkxPSIzNzEuNzcyMiIgeDI9Ii0zMTMiIHkyPSIzODAuNzA4MyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfNV8pIiBkPSJNLTcwOC4zLDcyLjhsMTEuMiw0LjhjNS4zLTcuNiw4LjctMTUuNSwxMC43LTIxLjZsMi04LjFsLTUuMywwLjhDLTY5NC41LDU4LjgtNzAxLjEsNjYuOS03MDguMyw3Mi44eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfNl8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI3Ny41MjU1IiB5MT0iMzkwLjIxMyIgeDI9Ii0yNzguNjQ3OSIgeTI9IjM4OC40MjU0IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF82XykiIGQ9Ik0tNjA3LDM2LjFjMi44LTcuNiw0LjMtMTQsNC4zLTE3YzAtMC4zLDAtMC41LDAtMC44bC02LjYsMkMtNjA5LjMsMjAuNC02MDksMjQuNy02MDcsMzYuMXoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzdfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yNjIuNTgxMyIgeTE9IjM4Ni40ODI3IiB4Mj0iLTI2My4yMTUiIHkyPSIzODQuMDc0OSIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfN18pIiBkPSJNLTU3MS40LDMwLjVjMCwwLTEuOCw1LjMsNS42LDEyYzEuMy01LjMsMi0xMC43LDIuMy0xNS4zTC01NzEuNCwzMC41eiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZmlsbD0iIzY2QkI2QSIgZD0iTS04MDcuOCwzNDYuNmgtMC41djAuNWMwLjgsNC4zLDAsMTAuNC0zLjEsMTQuNWMtMS44LDIuMy00LjMsMy42LTcuMSwzLjZjLTQuNiwwLTYuNC01LjYtNi42LTExLjdjNy42LTEuOCwxMi43LTYuNiwxMi43LTEyLjJjMC00LjEtMS4zLTEwLjctOS4yLTEwLjdjLTcuNCwwLTExLjIsMTEuMi0xMiwxOC44Yy0zLjgsMC02LjYtMS44LTguNC0zLjZjMC44LTIuOCwxLTUuMSwxLTcuNGMwLTMuMS0yLjMtNC42LTQuMS00LjZjLTMuMSwwLTUuOSwyLjgtNS45LDguMWMwLDMuMywxLjMsNS45LDMuOCw3LjljLTIuMyw1LjEtNS45LDkuNC03LjEsMTAuN2MtMS0yLTQuMS05LjctNS4xLTE3LjZjMS4zLTMuMywxLjgtNS45LDEuOC03LjFjMC0yLTEuMy0zLjMtMy42LTMuM2MtMy4xLDAtNy42LDEuOC03LjYsMmwtMC4zLDAuM3YwLjNjMCwwLDEuMyw2LjYsMi44LDEzLjdjLTIuOCw0LjMtNy40LDExLjctOS45LDExLjdjLTQuMywwLDIuOC0yMi4xLTAuMy0yMi45aC0wLjNjLTEuNSwxLTE4LjYsMTAuNC00MSwxMC40YzAsMCwwLDAuNSwwLjMsMC44YzAuMywwLjMsMC41LDAuNSwwLjUsMC41YzYuNCwwLjgsMTUuNSwwLDIyLjQtMWMtNC4xLDguNC0xMC45LDE0LjItMTcuNiwxNC4yYy0xMi4yLDAtMjEuNi0xNC44LTIxLjYtMTQuOGMzLjgtMy4zLDkuOS0xNC4yLDE5LjEtMTQuMmM4LjksMCwxMyw0LjgsMTMsNC44bDEtMS41YzAsMC00LjMtMTQuOC0xNi0xNC44cy0yNC40LDE5LjYtMzEuOCwyMy45YzAsMCwxMC4yLDI0LjIsMzIuMywyNC4yYzE4LjYsMCwyMy40LTE3LjgsMjQuMi0yMi4xYzQuNi0wLjgsNy42LTEuMyw3LjYtMS4zcy0xLDkuMi0xLDEzYzAsMy44LDQuMSw3LjksNy42LDcuOWMzLjEsMCw4LjktNi4xLDEzLjItMTMuNWwwLjMsMC44YzIuMyw4LjQsNS4xLDEyLjcsOC40LDEyLjdzOC45LTYuOSwxMi41LTE1LjVjMy42LDEuNSw3LjksMiwxMC4yLDJjMSwxNSwxMy41LDE1LjUsMTUsMTUuNWM5LjQsMCwxNy4zLTYuNiwxNy4zLTE0LjVDLTgwMS40LDM0Ni44LTgwNy44LDM0Ni42LTgwNy44LDM0Ni42eiBNLTgxOSwzNDAuMmMwLDAsMCw1LjEtNS45LDcuNmMwLjUtNi42LDItMTIuNSwzLjMtMTIuNUMtODIwLjUsMzM1LjQtODE5LDMzNy4yLTgxOSwzNDAuMnoiLz48cGF0aCBmaWxsPSIjNjZCQjZBIiBkPSJNLTg4My44LDMzNC40YzAsMC4zLDAsMC4zLDAuMywwLjVjNC4zLDAuNSw3LjQtMC44LDcuNC03LjljMC02LjYtNi45LTEuNS04LjEtMC41YzAsMC0wLjMsMC4zLDAsMC41Qy04ODMuMywzMjkuNS04ODMuOCwzMzMuMS04ODMuOCwzMzQuNHoiLz48L2c+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF84XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzgyLjAwNzQiIHkxPSIyNTkuODQ3NSIgeDI9Ii0zNzkuMTU4NSIgeTI9IjI2MC43OTcyIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF84XykiIGQ9Ik0tODY2LDM0OC42Yy0xLjgsMi44LTQuMyw2LjktNi42LDkuNGMwLjUsMS4zLDEuMywyLjgsMS44LDQuM2MyLTIuMyw0LjEtNS4xLDYuMS04LjFMLTg2NiwzNDguNnoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzlfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zNzMuMTYyNiIgeTE9IjI1OS4wNDIzIiB4Mj0iLTM3MS41MTAyIiB5Mj0iMjYxLjk0ODIiIGdyYWRpZW50VHJhbnNmb3JtPSJtYXRyaXgoMi41NDQ1IDAgMCAtMi41NDQ1IDEwMC4xMjcyIDEwMTcuODExNykiPjxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz48c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+PC9saW5lYXJHcmFkaWVudD48cGF0aCBmaWxsPSJ1cmwoI1NWR0lEXzlfKSIgZD0iTS04NDUuNCwzNTAuOWMtMC44LTAuNS0xLjUtMS0xLjUtMWMtMS41LDMuMy0zLjYsNi40LTUuMSw4LjdjMC44LDEsMiwyLjUsMy4xLDMuNmMyLTIuOCw0LjEtNi4xLDUuNi0xMC4yQy04NDMuNiwzNTEuOS04NDQuNywzNTEuNC04NDUuNCwzNTAuOXoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEwXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzY0LjY5OTYiIHkxPSIyNTcuNzUwMyIgeDI9Ii0zNjUuMDQxNCIgeTI9IjI2MC43ODkyIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xMF8pIiBkPSJNLTgyNS4xLDM1My4yYzAsMC0yLDAuNS00LjMsMC44Yy0yLDAuMy00LjMsMC00LjMsMGMwLjMsNC4zLDEuNSw3LjQsMy4xLDkuN2w3LjktMC44Qy04MjQsMzYwLjgtODI0LjgsMzU3LTgyNS4xLDM1My4yeiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTFfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zNjUiIHkxPSIyNjQuMjIyMyIgeDI9Ii0zNjUiIHkyPSIyNjEuMTU5NyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfMTFfKSIgZD0iTS04MzEuMiwzMzkuOUwtODMxLjIsMzM5LjljLTAuMywwLjgtMC41LDEuNS0wLjgsMmMwLDAuMywwLDAuMy0wLjMsMC41Yy0wLjUsMS41LTAuOCwyLjgtMSw0LjNjMCwwLjMsMCwwLjMsMCwwLjVjMCwwLjgtMC4zLDEuMy0wLjMsMmM1LjEsMCw4LjctMS41LDguNy0xLjVjMC0wLjUsMC0xLDAuMy0xLjV2LTAuM2MwLTAuNSwwLTAuOCwwLjMtMXYtMC4zYzAuMy0xLDAuMy0yLDAuNS0yLjhMLTgzMS4yLDMzOS45eiBNLTgzMy41LDM0OS40TC04MzMuNSwzNDkuNEwtODMzLjUsMzQ5LjRMLTgzMy41LDM0OS40eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTJfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zOTIiIHkxPSIyNTkuMjAyNSIgeDI9Ii0zOTIiIHkyPSIyNjMuMDAxMSIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfMTJfKSIgZD0iTS05MDIuNCwzNTguOGw0LjgsMmMyLjMtMy4zLDMuNi02LjYsNC42LTkuMmwwLjgtMy42bC0yLjMsMC4zQy04OTYuNiwzNTIuNy04OTkuMSwzNTYuMi05MDIuNCwzNTguOHoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEzXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzc2Ljg2MzciIHkxPSIyNjcuMDM1NSIgeDI9Ii0zNzcuMzQwOSIgeTI9IjI2Ni4yNzU1IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xM18pIiBkPSJNLTg1OS4yLDM0M2MxLjMtMy4zLDEuOC01LjksMS44LTcuMXYtMC4zbC0yLjgsMC44Qy04NjAuMiwzMzYuNC04NjAuMiwzMzguMi04NTkuMiwzNDN6Ii8+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xNF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM3MC41NTQzIiB5MT0iMjY1LjQ2NDUiIHgyPSItMzcwLjgyMzYiIHkyPSIyNjQuNDQxIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xNF8pIiBkPSJNLTg0NC4xLDM0MC43YzAsMC0wLjgsMi4zLDIuMyw1LjFjMC41LTIuMywxLTQuNiwxLTYuNkwtODQ0LjEsMzQwLjd6Ii8+PC9nPjxnPjxyZWN0IHg9Ii02OTcuMyIgeT0iMjkyLjkiIGZpbGw9IiNGRkZGRkYiIHdpZHRoPSIxMDYuOSIgaGVpZ2h0PSIxMDYuOSIvPjxnPjxwYXRoIGZpbGw9IiM2NkJCNkEiIGQ9Ik0tNjQ0LjQsMzQ5LjljMC4zLDAuNSwwLjUsMC44LDAuNSwwLjhjOC43LDEsMjEuMSwwLDMwLjUtMS41Yy01LjMsMTEuNy0xNSwxOS4zLTIzLjksMTkuM2MtMTYuNSwwLTI5LjUtMjAuMS0yOS41LTIwLjFjNS4xLTQuNiwxMy43LTE5LjMsMjYtMTkuM2MxMi4yLDAsMTcuNiw2LjYsMTcuNiw2LjZsMS4zLTIuM2MwLDAtNS45LTIwLjEtMjEuOS0yMC4xYy0xNi4zLDAtMzMuMywyNi41LTQzLjUsMzIuNmMwLDAsMTMuNywzMi44LDQ0LDMyLjhjMjUuNCwwLDMxLjgtMjQuMiwzMy4xLTMwLjNjMy4zLTAuNSw2LjEtMSw4LjEtMS4zYzAuNS0xLjMsMS4zLTMuOCwwLjgtNy4xYy0xMC4yLDMuOC0yNS40LDguNC00My41LDguNEMtNjQ0LjcsMzQ4LjYtNjQ0LjcsMzQ5LjEtNjQ0LjQsMzQ5Ljl6Ii8+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xNV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI4MS44NSIgeTE9IjI1Ny41MTg3IiB4Mj0iLTI4MS44NSIgeTI9IjI2Mi42NTExIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xNV8pIiBkPSJNLTYxMC4xLDM0OC45bC0zLjEsMC4zYzAsMC4zLTAuMywwLjUtMC41LDAuOGMtMC41LDEtMSwxLjgtMS41LDIuOGMtMC4zLDAuNS0wLjUsMC44LTAuOCwxLjNjLTAuNSwxLTEuMywyLTIsMi44Yy0wLjMsMC4zLTAuMywwLjMtMC41LDAuNWMtMS44LDIuMy0zLjYsNC4zLTUuNiw1LjlsNi40LDIuOEMtNjEyLjYsMzU5LjMtNjEwLjYsMzUxLjctNjEwLjEsMzQ4Ljl6Ii8+PC9nPjwvZz48Zz48Zz48ZGVmcz48Y2lyY2xlIGlkPSJTVkdJRF8xNl8iIGN4PSItNDA3LjMiIGN5PSIzNDYuMyIgcj0iNDIuMiIvPjwvZGVmcz48Y2xpcFBhdGggaWQ9IlNWR0lEXzE3XyI+PHVzZSB4bGluazpocmVmPSIjU1ZHSURfMTZfIiAgb3ZlcmZsb3c9InZpc2libGUiLz48L2NsaXBQYXRoPjxwYXRoIGNsaXAtcGF0aD0idXJsKCNTVkdJRF8xN18pIiBmaWxsPSIjRkZGRkZGIiBkPSJNLTQwMS4xLDM0OS40YzAuMywwLjMsMC41LDAuOCwwLjUsMC44YzcuNCwxLDE4LjEsMCwyNi4yLTEuM2MtNC42LDkuOS0xMywxNi41LTIwLjQsMTYuNWMtMTQuMiwwLTI1LjItMTcuMy0yNS4yLTE3LjNjNC4zLTMuOCwxMS43LTE2LjUsMjIuMS0xNi41czE1LDUuOSwxNSw1LjlsMS4zLTEuOGMwLDAtNC44LTE3LTE4LjgtMTdzLTI4LjUsMjIuNi0zNy4yLDI4YzAsMCwxMiwyOCwzNy43LDI4YzIxLjYsMCwyNy4yLTIwLjksMjguMi0yNmMyLjgtMC41LDUuMy0wLjgsNi45LTFjMC41LTEuMywxLTMuMywwLjgtNi4xYy04LjcsMy4zLTIxLjYsNy4xLTM3LjIsNy4xQy00MDEuNCwzNDguMy00MDEuNCwzNDguOS00MDEuMSwzNDkuNHoiLz48L2c+PC9nPjwvZz48ZyBpZD0iTGF5ZXJfMiI+PHBhdGggZmlsbD0iIzg4ODg4OCIgZD0iTTQ2Ny4zLDIwOS45Yy00LjgsMjQuNC0zMC44LDEyMi42LTEzMy42LDEyMi42Yy0xMjIuNiwwLTE3OC42LTEzMi44LTE3OC42LTEzMi44YzQxLTI0LjksMTEwLjQtMTMyLjMsMTc2LjEtMTMyLjNzODguOCw4MS4yLDg4LjgsODEuMmwtNS42LDguOWMwLDAtMjEuNi0yNy4yLTcxLjItMjcuMnMtODMuNyw1OS44LTEwNC42LDc4LjRjMCwwLDUyLjIsODEuNywxMTkuMyw4MS43YzM2LjEsMCw3NS4xLTMxLjMsOTYuOS03OC40Yy0zOC4yLDUuMy04OC4zLDEwLjItMTIzLjcsNS42YzAsMC0xLjgtMS41LTIuNS0zLjNjLTEtMi4zLTEuMy00LjYtMS4zLTQuNmM3MC4yLDAsMTMwLjUtMTYuNSwxNzEuNS0zMS44QzQ4Ny43LDc3LjYsNDAyLjksMCwzMDAuMSwwYy0xMTAuNCwwLTIwMCw4OS42LTIwMCwyMDBzODkuNiwyMDAsMjAwLDIwMGMxMDguOSwwLDE5Ny41LTg3LDIwMC0xOTUuNEM0OTIuNSwyMDUuOSw0ODEsMjA3LjksNDY3LjMsMjA5Ljl6Ii8+PC9nPjwvc3ZnPg==',
		'give_cpt_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOC4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAxNTcuMSAxNTcuMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTU3LjEgMTU3LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiM2NkJCNkE7fQ0KCS5zdDF7ZmlsbDojNTQ2RTdBO30NCgkuc3Qye2ZpbGw6dXJsKCNTVkdJRF8xXyk7fQ0KCS5zdDN7ZmlsbDp1cmwoI1NWR0lEXzJfKTt9DQoJLnN0NHtmaWxsOnVybCgjU1ZHSURfM18pO30NCgkuc3Q1e2ZpbGw6dXJsKCNTVkdJRF80Xyk7fQ0KCS5zdDZ7ZmlsbDp1cmwoI1NWR0lEXzVfKTt9DQoJLnN0N3tmaWxsOnVybCgjU1ZHSURfNl8pO30NCgkuc3Q4e2ZpbGw6dXJsKCNTVkdJRF83Xyk7fQ0KCS5zdDl7ZmlsbDp1cmwoI1NWR0lEXzhfKTt9DQoJLnN0MTB7ZmlsbDp1cmwoI1NWR0lEXzlfKTt9DQoJLnN0MTF7ZmlsbDp1cmwoI1NWR0lEXzEwXyk7fQ0KCS5zdDEye2ZpbGw6dXJsKCNTVkdJRF8xMV8pO30NCgkuc3QxM3tmaWxsOnVybCgjU1ZHSURfMTJfKTt9DQoJLnN0MTR7ZmlsbDp1cmwoI1NWR0lEXzEzXyk7fQ0KCS5zdDE1e2ZpbGw6dXJsKCNTVkdJRF8xNF8pO30NCgkuc3QxNntmaWxsOiNGRkZGRkY7fQ0KCS5zdDE3e2ZpbGw6dXJsKCNTVkdJRF8xNV8pO30NCgkuc3QxOHtjbGlwLXBhdGg6dXJsKCNTVkdJRF8xN18pO2ZpbGw6I0ZGRkZGRjt9DQoJLnN0MTl7ZmlsbDojRjFGMkYyO30NCjwvc3R5bGU+DQo8ZyBpZD0iTGF5ZXJfMSI+DQoJPGNpcmNsZSBjbGFzcz0ic3QwIiBjeD0iLTE5OS40IiBjeT0iMTM2LjEiIHI9IjE2LjYiLz4NCgk8Zz4NCgkJPGc+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTM0OC40LDUyLjZ2LTQuOWgxLjljMS41LDAsMi42LDEsMi42LDIuNWMwLDEuNS0xLjEsMi41LTIuNiwyLjVILTM0OC40eiBNLTM0NC45LDUwLjENCgkJCQljMC0wLjktMC41LTEuNS0xLjUtMS41aC0wLjl2My4xaDAuOUMtMzQ1LjUsNTEuNy0zNDQuOSw1MS0zNDQuOSw1MC4xeiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0zNDIuNiw1Mi42di00LjloMy41djAuOWgtMi40djFoMi40djAuOWgtMi40djEuMWgyLjR2MC45SC0zNDIuNnoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzMzLjUsNTIuNnYtMy40bC0xLjQsMy40aC0wLjVsLTEuNC0zLjR2My40aC0xdi00LjloMS41bDEuMiwzbDEuMi0zaDEuNXY0LjlILTMzMy41eiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0zMzEuMSw1MC4xYzAtMS41LDEuMS0yLjUsMi42LTIuNWMxLjUsMCwyLjYsMS4xLDIuNiwyLjVjMCwxLjUtMS4xLDIuNS0yLjYsMi41DQoJCQkJQy0zMzAuMSw1Mi43LTMzMS4xLDUxLjYtMzMxLjEsNTAuMXogTS0zMjcuMSw1MC4xYzAtMC45LTAuNi0xLjYtMS41LTEuNnMtMS41LDAuNy0xLjUsMS42YzAsMC45LDAuNiwxLjYsMS41LDEuNg0KCQkJCVMtMzI3LjEsNTEtMzI3LjEsNTAuMXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzI1LDUwLjFjMC0xLjUsMS4xLTIuNSwyLjYtMi41YzEuMSwwLDEuNywwLjYsMi4xLDEuMmwtMC45LDAuNGMtMC4yLTAuNC0wLjYtMC43LTEuMi0wLjcNCgkJCQljLTAuOSwwLTEuNSwwLjctMS41LDEuNmMwLDAuOSwwLjYsMS42LDEuNSwxLjZjMC41LDAsMC45LTAuMywxLjItMC43bDAuOSwwLjRjLTAuNCwwLjYtMSwxLjItMi4xLDEuMg0KCQkJCUMtMzIzLjgsNTIuNy0zMjUsNTEuNi0zMjUsNTAuMXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzE2LjMsNTIuNmwtMS0xLjdoLTAuOHYxLjdoLTF2LTQuOWgyLjNjMSwwLDEuNiwwLjcsMS42LDEuNmMwLDAuOS0wLjUsMS4zLTEuMSwxLjVsMS4xLDEuOUgtMzE2LjN6DQoJCQkJIE0tMzE2LjIsNDkuMmMwLTAuNC0wLjMtMC43LTAuNy0wLjdoLTEuMXYxLjNoMS4xQy0zMTYuNiw0OS45LTMxNi4yLDQ5LjctMzE2LjIsNDkuMnoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzEwLjQsNTIuNmwtMC4zLTAuOGgtMi4xbC0wLjMsMC44aC0xLjJsMS45LTQuOWgxLjNsMS45LDQuOUgtMzEwLjR6IE0tMzExLjgsNDguN2wtMC44LDIuMWgxLjUNCgkJCQlMLTMxMS44LDQ4Ljd6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTMwNy42LDUyLjZ2LTRoLTEuNHYtMC45aDMuOXYwLjloLTEuNHY0SC0zMDcuNnoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzAzLjksNTIuNnYtNC45aDF2NC45SC0zMDMuOXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzAxLjUsNTIuNnYtMC44bDIuMy0zLjFoLTIuM3YtMC45aDMuN3YwLjhsLTIuMywzLjJoMi40djAuOUgtMzAxLjV6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI5Ni40LDUyLjZ2LTQuOWgxdjQuOUgtMjk2LjR6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI5MC41LDUyLjZsLTIuMy0zLjJ2My4yaC0xdi00LjloMS4xbDIuMywzLjF2LTMuMWgxdjQuOUgtMjkwLjV6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI4OC4yLDUwLjFjMC0xLjYsMS4yLTIuNSwyLjYtMi41YzEsMCwxLjcsMC41LDIsMS4xbC0wLjksMC41Yy0wLjItMC4zLTAuNi0wLjYtMS4yLTAuNg0KCQkJCWMtMC45LDAtMS41LDAuNy0xLjUsMS42YzAsMC45LDAuNiwxLjYsMS41LDEuNmMwLjQsMCwwLjgtMC4yLDEtMC40di0wLjZoLTEuM3YtMC45aDIuM3YxLjljLTAuNSwwLjYtMS4yLDAuOS0yLjEsMC45DQoJCQkJQy0yODcsNTIuNy0yODguMiw1MS43LTI4OC4yLDUwLjF6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI3OS45LDUwLjFjMC0xLjYsMS4yLTIuNSwyLjYtMi41YzEsMCwxLjcsMC41LDIsMS4xbC0wLjksMC41Yy0wLjItMC4zLTAuNi0wLjYtMS4yLTAuNg0KCQkJCWMtMC45LDAtMS41LDAuNy0xLjUsMS42YzAsMC45LDAuNiwxLjYsMS41LDEuNmMwLjQsMCwwLjgtMC4yLDEtMC40di0wLjZoLTEuM3YtMC45aDIuM3YxLjljLTAuNSwwLjYtMS4yLDAuOS0yLjEsMC45DQoJCQkJQy0yNzguNyw1Mi43LTI3OS45LDUxLjctMjc5LjksNTAuMXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMjczLjgsNTIuNnYtNC45aDMuNXYwLjloLTIuNHYxaDIuNHYwLjloLTIuNHYxLjFoMi40djAuOUgtMjczLjh6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI2NS42LDUyLjZsLTIuMy0zLjJ2My4yaC0xdi00LjloMS4xbDIuMywzLjF2LTMuMWgxdjQuOUgtMjY1LjZ6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI2Myw1Mi42di00LjloMy41djAuOWgtMi40djFoMi40djAuOWgtMi40djEuMWgyLjR2MC45SC0yNjN6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI1NS40LDUyLjZsLTEtMS43aC0wLjh2MS43aC0xdi00LjloMi4zYzEsMCwxLjYsMC43LDEuNiwxLjZjMCwwLjktMC41LDEuMy0xLjEsMS41bDEuMSwxLjlILTI1NS40eg0KCQkJCSBNLTI1NS4zLDQ5LjJjMC0wLjQtMC4zLTAuNy0wLjctMC43aC0xLjF2MS4zaDEuMUMtMjU1LjYsNDkuOS0yNTUuMyw0OS43LTI1NS4zLDQ5LjJ6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI1My4xLDUwLjFjMC0xLjUsMS4xLTIuNSwyLjYtMi41YzEuNSwwLDIuNiwxLjEsMi42LDIuNWMwLDEuNS0xLjEsMi41LTIuNiwyLjUNCgkJCQlDLTI1Mi4xLDUyLjctMjUzLjEsNTEuNi0yNTMuMSw1MC4xeiBNLTI0OS4xLDUwLjFjMC0wLjktMC42LTEuNi0xLjUtMS42Yy0wLjksMC0xLjUsMC43LTEuNSwxLjZjMCwwLjksMC42LDEuNiwxLjUsMS42DQoJCQkJQy0yNDkuNyw1MS43LTI0OS4xLDUxLTI0OS4xLDUwLjF6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI0Ny4xLDUxLjlsMC42LTAuOGMwLjMsMC40LDAuOSwwLjcsMS42LDAuN2MwLjYsMCwwLjktMC4zLDAuOS0wLjVjMC0wLjktMi44LTAuMy0yLjgtMi4xDQoJCQkJYzAtMC44LDAuNy0xLjUsMS45LTEuNWMwLjgsMCwxLjQsMC4yLDEuOSwwLjdsLTAuNiwwLjhjLTAuNC0wLjQtMC45LTAuNS0xLjQtMC41Yy0wLjQsMC0wLjcsMC4yLTAuNywwLjVjMCwwLjgsMi44LDAuMywyLjgsMi4xDQoJCQkJYzAsMC45LTAuNiwxLjYtMiwxLjZDLTI0NS45LDUyLjctMjQ2LjYsNTIuMy0yNDcuMSw1MS45eiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0yNDEuOCw1Mi42di00LjloMXY0LjlILTI0MS44eiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0yMzguMSw1Mi42di00aC0xLjR2LTAuOWgzLjl2MC45aC0xLjR2NEgtMjM4LjF6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTIzMyw1Mi42di0ybC0xLjktMi45aDEuMmwxLjIsMmwxLjItMmgxLjJsLTEuOSwyLjl2MkgtMjMzeiIvPg0KCQk8L2c+DQoJCTxnPg0KCQkJPGc+DQoJCQkJPHBhdGggY2xhc3M9InN0MCIgZD0iTS0yMzAuMywxNy40bC0wLjUsMGwwLjEsMC41YzAuOCwzLjksMC4xLDkuNy0yLjksMTMuM2MtMS43LDIuMS0zLjksMy4yLTYuNiwzLjJjLTQuMywwLTUuOS01LjEtNi4xLTEwLjkNCgkJCQkJYzctMS43LDExLjctNi4xLDExLjctMTEuMmMwLTMuNy0xLjEtOS44LTguNC05LjhjLTYuOSwwLTEwLjQsMTAuMy0xMS4xLDE3LjVjLTMuNS0wLjEtNi4xLTEuNy03LjctMy4yYzAuNi0yLjUsMC45LTQuOCwwLjktNi45DQoJCQkJCWMwLTIuOS0yLTQuMi0zLjktNC4yYy0yLjcsMC01LjUsMi42LTUuNSw3LjZjMCwzLDEuMSw1LjUsMy40LDcuM2MtMiw0LjctNS40LDguNy02LjUsMTBjLTAuOS0xLjktMy44LTguOC00LjctMTYuMw0KCQkJCQljMS4xLTMsMS43LTUuNSwxLjctNi43YzAtMS45LTEuMi0zLTMuMi0zYy0yLjcsMC03LDEuNy03LjEsMS44bC0wLjIsMC4xbDAsMC4zYzAsMC4xLDEuMyw2LjEsMi42LDEyLjcNCgkJCQkJYy0yLjUsNC4xLTYuOSwxMC45LTkuMSwxMC45Yy00LDAsMi42LTIwLjUtMC4zLTIxLjJjLTAuMSwwLTAuMiwwLTAuMywwLjFjLTEuNCwwLjktMTcuMSw5LjYtMzgsOS42YzAsMCwwLDAuNCwwLjIsMC44DQoJCQkJCWMwLjEsMC4zLDAuNCwwLjYsMC40LDAuNmM1LjksMC43LDE0LjMtMC4xLDIwLjctMWMtMy43LDcuOS0xMC4yLDEzLjEtMTYuMiwxMy4xYy0xMS4zLDAtMjAtMTMuNy0yMC0xMy43DQoJCQkJCWMzLjUtMy4xLDkuMi0xMy4xLDE3LjYtMTMuMWM4LjMsMCwxMS45LDQuNiwxMS45LDQuNmwwLjktMS41YzAsMC0zLjktMTMuNi0xNC45LTEzLjZjLTExLDAtMjIuNywxOC0yOS41LDIyLjINCgkJCQkJYzAsMCw5LjQsMjIuMiwyOS45LDIyLjJjMTcuMiwwLDIxLjYtMTYuNSwyMi40LTIwLjVjNC4yLTAuNiw3LjEtMS4yLDcuMS0xLjJzLTEuMSw4LjQtMS4xLDExLjljMCwzLjUsMy45LDcuMiw3LjEsNy4yDQoJCQkJCWMyLjcsMCw4LjItNS42LDEyLjItMTIuNGwwLjIsMC44YzIuMSw3LjcsNC43LDExLjcsNy44LDExLjdjMy4xLDAsOC4yLTYuNCwxMS41LTE0LjVjMy4zLDEuNCw3LjIsMS44LDkuNSwxLjkNCgkJCQkJYzAuOSwxMy45LDEyLjUsMTQuMywxMy45LDE0LjNjOC42LDAsMTUuOS02LjIsMTUuOS0xMy41Qy0yMjQuMywxNy41LTIzMC4yLDE3LjQtMjMwLjMsMTcuNHogTS0yNDAuOCwxMS42YzAsMC0wLjEsNC42LTUuMyw2LjkNCgkJCQkJYzAuNS02LjEsMi0xMS42LDMtMTEuNkMtMjQyLDctMjQwLjgsOC43LTI0MC44LDExLjZ6Ii8+DQoJCQkJPHBhdGggY2xhc3M9InN0MCIgZD0iTS0zMDAuNyw2LjFjMCwwLjIsMC4xLDAuMywwLjMsMC40YzQuMSwwLjYsNi44LTAuNyw2LjgtNy4zYzAtNi4yLTYuNC0xLjMtNy42LTAuNA0KCQkJCQljLTAuMSwwLjEtMC4xLDAuMi0wLjEsMC40Qy0zMDAuMiwxLjYtMzAwLjYsNS4xLTMwMC43LDYuMXoiLz4NCgkJCTwvZz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI4OS41ODQ0IiB5MT0iMjYuNzY4IiB4Mj0iLTI4Mi44ODIzIiB5Mj0iMjQuNTM0Ij4NCgkJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQkJPHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPg0KCQkJPC9saW5lYXJHcmFkaWVudD4NCgkJCTxwYXRoIGNsYXNzPSJzdDIiIGQ9Ik0tMjg0LjIsMTkuNGMtMS42LDIuNy00LDYuNC02LjEsOC43YzAuNSwxLjIsMS4xLDIuNywxLjcsMy45YzEuOS0yLjEsMy44LTQuOCw1LjUtNy42TC0yODQuMiwxOS40eiIvPg0KCQkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8yXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMjY5LjAxOTQiIHkxPSIyOC40Nzc3IiB4Mj0iLTI2NS4xMzIyIiB5Mj0iMjEuNjQxNiI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3QzIiBkPSJNLTI2NS4yLDIxLjVjLTAuOC0wLjQtMS41LTEtMS41LTFjLTEuNCwzLjEtMy4zLDYtNC44LDcuOWMwLjcsMSwxLjksMi4zLDIuOCwzLjNjMS44LTIuNSwzLjctNS44LDUuMS05LjMNCgkJCQlDLTI2My41LDIyLjMtMjY0LjQsMjItMjY1LjIsMjEuNXoiLz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfM18iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI0OC42MjU0IiB5MT0iMzEuNjE0NyIgeDI9Ii0yNDkuNDI5NyIgeTI9IjI0LjQ2NTkiPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCQk8L2xpbmVhckdyYWRpZW50Pg0KCQkJPHBhdGggY2xhc3M9InN0NCIgZD0iTS0yNDYuMiwyMy41YzAsMC0yLDAuNC00LDAuNmMtMiwwLjItMy45LDAuMS0zLjksMC4xYzAuMyw0LDEuNCw2LjgsMi45LDguOWw3LjMtMC42DQoJCQkJQy0yNDUuNCwzMC41LTI0Ni4xLDI3LjEtMjQ2LjIsMjMuNXoiLz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfNF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI0OS43MjY3IiB5MT0iMTYuNDE5IiB4Mj0iLTI0OS43MjY3IiB5Mj0iMjMuNjIzNyI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q1IiBkPSJNLTI1MiwxMS4zTC0yNTIsMTEuM2MtMC4yLDAuNi0wLjUsMS4zLTAuNywyYzAsMC4yLTAuMSwwLjMtMC4xLDAuNWMtMC40LDEuMy0wLjcsMi42LTAuOSwzLjkNCgkJCQljMCwwLjItMC4xLDAuMy0wLjEsMC41Yy0wLjEsMC42LTAuMiwxLjItMC4yLDEuOGM0LjcsMCw3LjktMS40LDcuOS0xLjRjMC0wLjUsMC4xLTAuOSwwLjEtMS40YzAtMC4xLDAtMC4yLDAtMC4zDQoJCQkJYzAtMC40LDAuMS0wLjcsMC4xLTEuMWMwLTAuMSwwLTAuMiwwLTAuM2MwLjEtMC45LDAuMy0xLjgsMC40LTIuNkwtMjUyLDExLjN6IE0tMjU0LjEsMTkuOUMtMjU0LjEsMTkuOS0yNTQuMSwxOS45LTI1NC4xLDE5LjkNCgkJCQlMLTI1NC4xLDE5LjlDLTI1NC4xLDE5LjktMjU0LjEsMTkuOS0yNTQuMSwxOS45eiIvPg0KCQkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF81XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzEzLjAyNzIiIHkxPSIyOC4yMjc4IiB4Mj0iLTMxMy4wMjcyIiB5Mj0iMTkuMjkxNyI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q2IiBkPSJNLTMxNy43LDI4LjZsNC40LDEuOWMyLjEtMywzLjQtNi4xLDQuMi04LjVsMC44LTMuMmwtMi4xLDAuM0MtMzEyLjMsMjMuMS0zMTQuOSwyNi4zLTMxNy43LDI4LjZ6Ii8+DQoJCQk8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzZfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yNzcuNTIzOSIgeTE9IjkuNzg4IiB4Mj0iLTI3OC42NDYzIiB5Mj0iMTEuNTc1NiI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q3IiBkPSJNLTI3Ny45LDE0LjJjMS4xLTMsMS43LTUuNSwxLjctNi43YzAtMC4xLDAtMC4yLDAtMC4zbC0yLjYsMC44Qy0yNzguOCw4LTI3OC43LDkuNy0yNzcuOSwxNC4yeiIvPg0KCQkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF83XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMjYyLjU1MjkiIHkxPSIxMy41MjQ4IiB4Mj0iLTI2My4xODY2IiB5Mj0iMTUuOTMyNiI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q4IiBkPSJNLTI2My45LDEyYzAsMC0wLjcsMi4xLDIuMiw0LjdjMC41LTIuMSwwLjgtNC4yLDAuOS02TC0yNjMuOSwxMnoiLz4NCgkJPC9nPg0KCTwvZz4NCgk8Zz4NCgkJPGc+DQoJCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNLTM1Ni44LDEzNi4ybC0wLjIsMGwwLDAuMmMwLjMsMS43LDAsNC4xLTEuMiw1LjdjLTAuNywwLjktMS43LDEuNC0yLjgsMS40Yy0xLjgsMC0yLjUtMi4yLTIuNi00LjYNCgkJCQljMy0wLjcsNS0yLjYsNS00LjhjMC0xLjYtMC41LTQuMi0zLjYtNC4yYy0yLjksMC00LjQsNC40LTQuNyw3LjRjLTEuNSwwLTIuNi0wLjctMy4zLTEuNGMwLjMtMS4xLDAuNC0yLDAuNC0yLjkNCgkJCQljMC0xLjItMC45LTEuOC0xLjYtMS44Yy0xLjIsMC0yLjMsMS4xLTIuMywzLjJjMCwxLjMsMC41LDIuMywxLjUsMy4xYy0wLjksMi0yLjMsMy43LTIuOCw0LjJjLTAuNC0wLjgtMS42LTMuOC0yLTYuOQ0KCQkJCWMwLjUtMS4zLDAuNy0yLjMsMC43LTIuOGMwLTAuOC0wLjUtMS4zLTEuNC0xLjNjLTEuMiwwLTMsMC43LTMsMC44bC0wLjEsMC4xbDAsMC4xYzAsMCwwLjUsMi42LDEuMSw1LjQNCgkJCQljLTEuMSwxLjctMi45LDQuNi0zLjksNC42Yy0xLjcsMCwxLjEtOC43LTAuMS05YzAsMC0wLjEsMC0wLjEsMGMtMC42LDAuNC03LjMsNC4xLTE2LjEsNC4xYzAsMCwwLDAuMiwwLjEsMC4zDQoJCQkJYzAuMSwwLjEsMC4yLDAuMiwwLjIsMC4yYzIuNSwwLjMsNi4xLDAsOC44LTAuNGMtMS42LDMuMy00LjMsNS42LTYuOSw1LjZjLTQuOCwwLTguNS01LjgtOC41LTUuOGMxLjUtMS4zLDMuOS01LjYsNy41LTUuNg0KCQkJCWMzLjUsMCw1LjEsMS45LDUuMSwxLjlsMC40LTAuNmMwLDAtMS43LTUuOC02LjMtNS44cy05LjYsNy43LTEyLjUsOS40YzAsMCw0LDkuNSwxMi43LDkuNWM3LjMsMCw5LjItNyw5LjUtOC43DQoJCQkJYzEuOC0wLjMsMy0wLjUsMy0wLjVzLTAuNCwzLjYtMC40LDUuMXMxLjYsMy4xLDMsMy4xYzEuMiwwLDMuNS0yLjQsNS4yLTUuM2wwLjEsMC4zYzAuOSwzLjMsMiw1LDMuMyw1YzEuMywwLDMuNS0yLjcsNC45LTYuMQ0KCQkJCWMxLjQsMC42LDMuMSwwLjgsNCwwLjhjMC40LDUuOSw1LjMsNi4xLDUuOSw2LjFjMy43LDAsNi44LTIuNiw2LjgtNS43Qy0zNTQuMywxMzYuMy0zNTYuOCwxMzYuMi0zNTYuOCwxMzYuMnogTS0zNjEuMiwxMzMuNw0KCQkJCWMwLDAsMCwyLTIuMywzYzAuMi0yLjYsMC44LTQuOSwxLjMtNC45Qy0zNjEuOCwxMzEuOC0zNjEuMiwxMzIuNS0zNjEuMiwxMzMuN3oiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0tMzg2LjcsMTMxLjRjMCwwLjEsMCwwLjEsMC4xLDAuMmMxLjcsMC4yLDIuOS0wLjMsMi45LTMuMWMwLTIuNi0yLjctMC42LTMuMi0wLjJjMCwwLTAuMSwwLjEsMCwwLjINCgkJCQlDLTM4Ni41LDEyOS41LTM4Ni43LDEzMC45LTM4Ni43LDEzMS40eiIvPg0KCQk8L2c+DQoJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfOF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM4MS45OTkzIiB5MT0iMTQwLjE3NjkiIHgyPSItMzc5LjE1MDQiIHkyPSIxMzkuMjI3MiI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDkiIGQ9Ik0tMzc5LjcsMTM3Yy0wLjcsMS4xLTEuNywyLjctMi42LDMuN2MwLjIsMC41LDAuNSwxLjEsMC43LDEuN2MwLjgtMC45LDEuNi0yLDIuNC0zLjJMLTM3OS43LDEzN3oiLz4NCgkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF85XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzczLjI1NzUiIHkxPSIxNDAuOTAzNyIgeDI9Ii0zNzEuNjA1MSIgeTI9IjEzNy45OTc4Ij4NCgkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJPC9saW5lYXJHcmFkaWVudD4NCgkJPHBhdGggY2xhc3M9InN0MTAiIGQ9Ik0tMzcxLjYsMTM3LjljLTAuMy0wLjItMC42LTAuNC0wLjYtMC40Yy0wLjYsMS4zLTEuNCwyLjUtMiwzLjRjMC4zLDAuNCwwLjgsMSwxLjIsMS40DQoJCQljMC44LTEuMSwxLjYtMi40LDIuMi00Qy0zNzAuOSwxMzguMy0zNzEuMywxMzguMS0zNzEuNiwxMzcuOXoiLz4NCgkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xMF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM2NC41ODg0IiB5MT0iMTQyLjIzNzIiIHgyPSItMzY0LjkzMDMiIHkyPSIxMzkuMTk4MyI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDExIiBkPSJNLTM2My42LDEzOC44YzAsMC0wLjgsMC4yLTEuNywwLjNjLTAuOCwwLjEtMS43LDAtMS43LDBjMC4xLDEuNywwLjYsMi45LDEuMiwzLjhsMy4xLTAuMw0KCQkJQy0zNjMuMiwxNDEuOC0zNjMuNSwxNDAuMy0zNjMuNiwxMzguOHoiLz4NCgkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xMV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM2NS4wNTY2IiB5MT0iMTM1Ljc3NzciIHgyPSItMzY1LjA1NjYiIHkyPSIxMzguODQwMyI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDEyIiBkPSJNLTM2NiwxMzMuNkwtMzY2LDEzMy42Yy0wLjEsMC4zLTAuMiwwLjYtMC4zLDAuOGMwLDAuMSwwLDAuMS0wLjEsMC4yYy0wLjIsMC42LTAuMywxLjEtMC40LDEuNw0KCQkJYzAsMC4xLDAsMC4xLDAsMC4yYzAsMC4zLTAuMSwwLjUtMC4xLDAuOGMyLDAsMy40LTAuNiwzLjQtMC42YzAtMC4yLDAtMC40LDAuMS0wLjZjMCwwLDAtMC4xLDAtMC4xYzAtMC4yLDAtMC4zLDAuMS0wLjQNCgkJCWMwLDAsMC0wLjEsMC0wLjFjMC4xLTAuNCwwLjEtMC44LDAuMi0xLjFMLTM2NiwxMzMuNnogTS0zNjYuOSwxMzcuM0MtMzY2LjksMTM3LjMtMzY2LjksMTM3LjMtMzY2LjksMTM3LjNMLTM2Ni45LDEzNy4zDQoJCQlDLTM2Ni45LDEzNy4zLTM2Ni45LDEzNy4zLTM2Ni45LDEzNy4zeiIvPg0KCQk8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEyXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzkxLjk2NDQiIHkxPSIxNDAuNzk3NSIgeDI9Ii0zOTEuOTY0NCIgeTI9IjEzNi45OTg5Ij4NCgkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJPC9saW5lYXJHcmFkaWVudD4NCgkJPHBhdGggY2xhc3M9InN0MTMiIGQ9Ik0tMzk0LDE0MWwxLjksMC44YzAuOS0xLjMsMS40LTIuNiwxLjgtMy42bDAuMy0xLjRsLTAuOSwwLjFDLTM5MS43LDEzOC42LTM5Mi43LDE0MC0zOTQsMTQxeiIvPg0KCQk8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEzXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzc2Ljg3MjYiIHkxPSIxMzIuOTU5IiB4Mj0iLTM3Ny4zNDk4IiB5Mj0iMTMzLjcxODkiPg0KCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJPHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPg0KCQk8L2xpbmVhckdyYWRpZW50Pg0KCQk8cGF0aCBjbGFzcz0ic3QxNCIgZD0iTS0zNzcsMTM0LjhjMC41LTEuMywwLjctMi4zLDAuNy0yLjhjMCwwLDAtMC4xLDAtMC4xbC0xLjEsMC4zQy0zNzcuNCwxMzIuMi0zNzcuNCwxMzIuOS0zNzcsMTM0Ljh6Ii8+DQoJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTRfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zNzAuNTA4OCIgeTE9IjEzNC41NDc1IiB4Mj0iLTM3MC43NzgxIiB5Mj0iMTM1LjU3MSI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDE1IiBkPSJNLTM3MS4xLDEzMy45YzAsMC0wLjMsMC45LDAuOSwyYzAuMi0wLjksMC40LTEuOCwwLjQtMi42TC0zNzEuMSwxMzMuOXoiLz4NCgk8L2c+DQoJPGc+DQoJCTxyZWN0IHg9Ii0zMTMuNCIgeT0iMTE1LjEiIGNsYXNzPSJzdDE2IiB3aWR0aD0iNDIiIGhlaWdodD0iNDIiLz4NCgkJPGc+DQoJCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNLTI5Mi42LDEzNy41YzAuMSwwLjIsMC4yLDAuMywwLjIsMC4zYzMuNCwwLjQsOC4zLDAsMTItMC42Yy0yLjEsNC42LTUuOSw3LjYtOS40LDcuNg0KCQkJCWMtNi41LDAtMTEuNi03LjktMTEuNi03LjljMi0xLjgsNS40LTcuNiwxMC4yLTcuNnM2LjksMi42LDYuOSwyLjZsMC41LTAuOWMwLDAtMi4zLTcuOS04LjYtNy45Yy02LjQsMC0xMy4xLDEwLjQtMTcuMSwxMi44DQoJCQkJYzAsMCw1LjQsMTIuOSwxNy4zLDEyLjljMTAsMCwxMi41LTkuNSwxMy0xMS45YzEuMy0wLjIsMi40LTAuNCwzLjItMC41YzAuMi0wLjUsMC41LTEuNSwwLjMtMi44Yy00LDEuNS0xMCwzLjMtMTcuMSwzLjMNCgkJCQlDLTI5Mi43LDEzNy0yOTIuNywxMzcuMi0yOTIuNiwxMzcuNXoiLz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTVfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yODEuNzk2MiIgeTE9IjE0Mi40ODEzIiB4Mj0iLTI4MS43OTYyIiB5Mj0iMTM3LjM0ODkiPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCQk8L2xpbmVhckdyYWRpZW50Pg0KCQkJPHBhdGggY2xhc3M9InN0MTciIGQ9Ik0tMjc5LjEsMTM3LjFsLTEuMiwwLjFjMCwwLjEtMC4xLDAuMi0wLjIsMC4zYy0wLjIsMC40LTAuNCwwLjctMC42LDEuMWMtMC4xLDAuMi0wLjIsMC4zLTAuMywwLjUNCgkJCQljLTAuMiwwLjQtMC41LDAuOC0wLjgsMS4xYy0wLjEsMC4xLTAuMSwwLjEtMC4yLDAuMmMtMC43LDAuOS0xLjQsMS43LTIuMiwyLjNsMi41LDEuMUMtMjgwLjEsMTQxLjItMjc5LjMsMTM4LjItMjc5LjEsMTM3LjF6Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxkZWZzPg0KCQkJPGNpcmNsZSBpZD0iU1ZHSURfMTZfIiBjeD0iLTE5OS40IiBjeT0iMTM2LjEiIHI9IjE2LjYiLz4NCgkJPC9kZWZzPg0KCQk8Y2xpcFBhdGggaWQ9IlNWR0lEXzE3XyI+DQoJCQk8dXNlIHhsaW5rOmhyZWY9IiNTVkdJRF8xNl8iICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZTsiLz4NCgkJPC9jbGlwUGF0aD4NCgkJPHBhdGggY2xhc3M9InN0MTgiIGQ9Ik0tMTk3LDEzNy4zYzAuMSwwLjEsMC4yLDAuMywwLjIsMC4zYzIuOSwwLjQsNy4xLDAsMTAuMy0wLjVjLTEuOCwzLjktNS4xLDYuNS04LDYuNWMtNS42LDAtOS45LTYuOC05LjktNi44DQoJCQljMS43LTEuNSw0LjYtNi41LDguNy02LjVzNS45LDIuMyw1LjksMi4zbDAuNS0wLjdjMCwwLTEuOS02LjctNy40LTYuN3MtMTEuMiw4LjktMTQuNiwxMWMwLDAsNC43LDExLDE0LjgsMTENCgkJCWM4LjUsMCwxMC43LTguMiwxMS4xLTEwLjJjMS4xLTAuMiwyLjEtMC4zLDIuNy0wLjRjMC4yLTAuNSwwLjQtMS4zLDAuMy0yLjRjLTMuNCwxLjMtOC41LDIuOC0xNC42LDIuOA0KCQkJQy0xOTcuMSwxMzYuOS0xOTcuMSwxMzcuMS0xOTcsMTM3LjN6Ii8+DQoJPC9nPg0KPC9nPg0KPGcgaWQ9IkxheWVyXzIiPg0KCTxwYXRoIGNsYXNzPSJzdDE5IiBkPSJNMTQ0LjMsODIuNWMtMS45LDkuNi0xMi4xLDQ4LjItNTIuNSw0OC4yYy00OC4yLDAtNzAuMi01Mi4yLTcwLjItNTIuMmMxNi4xLTkuOCw0My40LTUyLDY5LjItNTINCgkJczM0LjksMzEuOSwzNC45LDMxLjlsLTIuMiwzLjVjMCwwLTguNS0xMC43LTI4LTEwLjdTNjIuNiw3NC43LDU0LjQsODJjMCwwLDIwLjUsMzIuMSw0Ni45LDMyLjFjMTQuMiwwLDI5LjUtMTIuMywzOC4xLTMwLjgNCgkJYy0xNSwyLjEtMzQuNyw0LTQ4LjYsMi4yYzAsMC0wLjctMC42LTEtMS4zYy0wLjQtMC45LTAuNS0xLjgtMC41LTEuOGMyNy42LDAsNTEuMy02LjUsNjcuNC0xMi41QzE1Mi4zLDMwLjUsMTE5LDAsNzguNiwwDQoJCUMzNS4yLDAsMCwzNS4yLDAsNzguNmMwLDQzLjQsMzUuMiw3OC42LDc4LjYsNzguNmM0Mi44LDAsNzcuNi0zNC4yLDc4LjYtNzYuOEMxNTQuMiw4MC45LDE0OS43LDgxLjcsMTQ0LjMsODIuNXoiLz4NCjwvZz4NCjwvc3ZnPg0K',
	);

	// Return the chosen icon's SVG string
	return $svgs[ $icon ];
}

/**
 * Modify Admin Nav Menu Label
 *
 * @since 1.3
 *
 * @param object $post_type The current object to add a menu items meta box for.
 *
 * @return mixed
 */
function modify_nav_menu_meta_box_object( $post_type ) {
	if ( isset( $post_type->name ) && $post_type->name == 'give_forms' ) {
		$post_type->labels->name = esc_html__( 'Donation Forms', 'give' );
	}

	return $post_type;
}

add_filter( 'nav_menu_meta_box_object', 'modify_nav_menu_meta_box_object' );


/**
 * Array_column backup usage
 *
 * This file is part of the array_column library.
 *
 * @since      : 1.3.0.1
 *
 * @copyright  Copyright (c) Ben Ramsey (http://benramsey.com)
 * @license    https://opensource.org/licenses/MIT MIT
 */

if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array      $input     A multi-dimensional array (record set) from which to pull
	 *                              a column of values.
	 * @param int|string $columnKey The column of values to return. This value may be the
	 *                              integer key of the column you wish to retrieve, or it
	 *                              may be the string key name for an associative array.
	 * @param mixed      $indexKey  (Optional.) The column to use as the index/keys for
	 *                              the returned array. This value may be the integer key
	 *                              of the column, or it may be the string key name.
	 *
	 * @return array
	 */
	function array_column( $input = null, $columnKey = null, $indexKey = null ) {
		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();

		if ( $argc < 2 ) {
			trigger_error( sprintf( esc_html__( 'array_column() expects at least 2 parameters, %s given.', 'give' ), $argc ), E_USER_WARNING );

			return null;
		}

		if ( ! is_array( $params[0] ) ) {
			trigger_error( sprintf( esc_html__( 'array_column() expects parameter 1 to be array, %s given.', 'give' ), gettype( $params[0] ) ), E_USER_WARNING );

			return null;
		}

		if ( ! is_int( $params[1] )
		     && ! is_float( $params[1] )
		     && ! is_string( $params[1] )
		     && $params[1] !== null
		     && ! ( is_object( $params[1] ) && method_exists( $params[1], '__toString' ) )
		) {
			trigger_error( esc_html__( 'array_column(): The column key should be either a string or an integer.', 'give' ), E_USER_WARNING );

			return false;
		}

		if ( isset( $params[2] )
		     && ! is_int( $params[2] )
		     && ! is_float( $params[2] )
		     && ! is_string( $params[2] )
		     && ! ( is_object( $params[2] ) && method_exists( $params[2], '__toString' ) )
		) {
			trigger_error( esc_html__( 'array_column(): The index key should be either a string or an integer.', 'give' ), E_USER_WARNING );

			return false;
		}

		$paramsInput     = $params[0];
		$paramsColumnKey = ( $params[1] !== null ) ? (string) $params[1] : null;

		$paramsIndexKey = null;
		if ( isset( $params[2] ) ) {
			if ( is_float( $params[2] ) || is_int( $params[2] ) ) {
				$paramsIndexKey = (int) $params[2];
			} else {
				$paramsIndexKey = (string) $params[2];
			}
		}

		$resultArray = array();

		foreach ( $paramsInput as $row ) {
			$key    = $value = null;
			$keySet = $valueSet = false;

			if ( $paramsIndexKey !== null && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = true;
				$key    = (string) $row[ $paramsIndexKey ];
			}

			if ( $paramsColumnKey === null ) {
				$valueSet = true;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = true;
				$value    = $row[ $paramsColumnKey ];
			}

			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[] = $value;
				}
			}

		}

		return $resultArray;
	}

}

/**
 * Determines the receipt visibility status.
 *
 * @since 1.3.2
 *
 * @param string $payment_key
 *
 * @return bool Whether the receipt is visible or not.
 */
function give_can_view_receipt( $payment_key = '' ) {

	$return = false;

	if ( empty( $payment_key ) ) {
		return $return;
	}

	global $give_receipt_args;

	$give_receipt_args['id'] = give_get_purchase_id_by_key( $payment_key );

	$user_id = (int) give_get_payment_user_id( $give_receipt_args['id'] );

	$payment_meta = give_get_payment_meta( $give_receipt_args['id'] );

	if ( is_user_logged_in() ) {
		if ( $user_id === (int) get_current_user_id() ) {
			$return = true;
		} elseif ( wp_get_current_user()->user_email === give_get_payment_user_email( $give_receipt_args['id'] ) ) {
			$return = true;
		} elseif ( current_user_can( 'view_give_sensitive_data' ) ) {
			$return = true;
		}
	}

	$session = give_get_purchase_session();
	if ( ! empty( $session ) && ! is_user_logged_in() ) {
		if ( $session['purchase_key'] === $payment_meta['key'] ) {
			$return = true;
		}
	}

	return (bool) apply_filters( 'give_can_view_receipt', $return, $payment_key );

}

/**
 * Fallback for cal_days_in_month
 *
 * Fallback in case the calendar extension is not loaded in PHP; Only supports Gregorian calendar
 */
if ( ! function_exists( 'cal_days_in_month' ) ) {
	/**
	 * cal_days_in_month
	 *
	 * @param int $calendar
	 * @param int $month
	 * @param int $year
	 *
	 * @return bool|string
	 */
	function cal_days_in_month( $calendar, $month, $year ) {
		return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
	}
}

/**
 * Get plugin info including status, type, and license validation.
 *
 * This is an enhanced version of get_plugins() that returns the status
 * (`active` or `inactive`) of all plugins, type of plugin (`add-on` or `other`
 * and license validation for Give add-ons (`true` or `false`). Does not include
 * MU plugins.
 *
 * @since 1.8.0
 *
 * @return array Plugin info plus status, type, and license validation if
 *               available.
 */
function give_get_plugins() {
	$plugins             = get_plugins();
	$active_plugin_paths = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$network_activated_plugin_paths = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
		$active_plugin_paths            = array_merge( $active_plugin_paths, $network_activated_plugin_paths );
	}

	foreach ( $plugins as $plugin_path => $plugin_data ) {
		// Is plugin active?
		if ( in_array( $plugin_path, $active_plugin_paths ) ) {
			$plugins[ $plugin_path ]['Status'] = 'active';
		} else {
			$plugins[ $plugin_path ]['Status'] = 'inactive';
		}

		$dirname = strtolower( dirname( $plugin_path ) );

		// Is plugin a Give add-on by WordImpress?
		if ( strstr( $dirname, 'give-' ) && strstr( $plugin_data['AuthorURI'], 'wordimpress.com' ) ) {
			// Plugin is a Give-addon.
			$plugins[ $plugin_path ]['Type'] = 'add-on';

			// Get license info from database.
			$plugin_name    = str_replace( 'Give - ', '', $plugin_data['Name'] );
			$db_option      = 'give_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $plugin_name ) ) ) . '_license_active';
			$license_active = get_option( $db_option );

			// Does a valid license exist?
			if ( ! empty( $license_active ) && 'valid' === $license_active->license ) {
				$plugins[ $plugin_path ]['License'] = true;
			} else {
				$plugins[ $plugin_path ]['License'] = false;
			}
		} else {
			// Plugin is not a Give add-on.
			$plugins[ $plugin_path ]['Type'] = 'other';
		}
	}

	return $plugins;
}


/**
 * Check if terms enabled or not for form.
 *
 * @since 1.8
 *
 * @param $form_id
 *
 * @return bool
 */
function give_is_terms_enabled( $form_id ) {
	$form_option = get_post_meta( $form_id, '_give_terms_option', true );

	if (
		give_is_setting_enabled( $form_option, 'global' )
		&& give_is_setting_enabled( give_get_option( 'terms' ) )
	) {
		return true;

	} elseif ( give_is_setting_enabled( $form_option ) ) {
		return true;

	} else {
		return false;
	}
}
