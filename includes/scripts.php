<?php
/**
 * Scripts
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
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 *
 * @return void
 */
function give_load_scripts() {

	$js_dir         = GIVE_PLUGIN_URL . 'assets/js/frontend/';
	$js_plugins     = GIVE_PLUGIN_URL . 'assets/js/plugins/';
	$scripts_footer = ( give_is_setting_enabled( give_get_option( 'scripts_footer' ) ) ) ? true : false;

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Localize / PHP to AJAX vars.
	$localize_give_vars = apply_filters( 'give_global_script_vars', array(
		'ajaxurl'             => give_get_ajax_url(),
		'checkout_nonce'      => wp_create_nonce( 'give_checkout_nonce' ),
		'currency_sign'       => give_currency_filter( '' ),
		'currency_pos'        => give_get_currency_position(),
		'thousands_separator' => give_get_price_thousand_separator(),
		'decimal_separator'   => give_get_price_decimal_separator(),
		'no_gateway'          => __( 'Please select a payment method.', 'give' ),
		'bad_minimum'         => __( 'The minimum donation amount for this form is', 'give' ),
		'general_loading'     => __( 'Loading...', 'give' ),
		'purchase_loading'    => __( 'Please Wait...', 'give' ),
		'number_decimals'     => give_get_price_decimals(),
		'give_version'        => GIVE_VERSION,
		'magnific_options'    => apply_filters(
			'give_magnific_options',
			array(
				'main_class'        => 'give-modal',
				'close_on_bg_click' => false,
			)
		),
		'form_translation'    => apply_filters(
			'give_form_translation_js',
			array(
				// Field name               Validation message.
				'payment-mode'           => __( 'Please select payment mode.', 'give' ),
				'give_first'             => __( 'Please enter your first name.', 'give' ),
				'give_email'             => __( 'Please enter a valid email address.', 'give' ),
				'give_user_login'        => __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed.', 'give' ),
				'give_user_pass'         => __( 'Enter a password.', 'give' ),
				'give_user_pass_confirm' => __( 'Enter the password confirmation.', 'give' ),
				'give_agree_to_terms'    => __( 'You must agree to the terms and conditions.', 'give' ),
			)
		),
	) );

	$localize_give_ajax = apply_filters( 'give_global_ajax_vars', array(
		'ajaxurl'         => give_get_ajax_url(),
		'loading'         => __( 'Loading', 'give' ),
		// General loading message.
		'select_option'   => __( 'Please select an option', 'give' ),
		// Variable pricing error with multi-donation option enabled.
		'default_gateway' => give_get_default_gateway( null ),
		'permalinks'      => get_option( 'permalink_structure' ) ? '1' : '0',
		'number_decimals' => give_get_price_decimals(),
	) );

	// DEBUG is On.
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {

		if ( give_is_cc_verify_enabled() ) {
			wp_register_script( 'give-cc-validator', $js_plugins . 'jquery.payment' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
			wp_enqueue_script( 'give-cc-validator' );
		}

		wp_register_script( 'give-float-labels', $js_plugins . 'float-labels' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-float-labels' );

		wp_register_script( 'give-blockui', $js_plugins . 'jquery.blockUI' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-blockui' );

		wp_register_script( 'give-qtip', $js_plugins . 'jquery.qtip' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-qtip' );

		wp_register_script( 'give-accounting', $js_plugins . 'accounting' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-accounting' );

		wp_register_script( 'give-magnific', $js_plugins . 'jquery.magnific-popup' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-magnific' );

		wp_register_script( 'give-checkout-global', $js_dir . 'give-checkout-global' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-checkout-global' );

		// General scripts.
		wp_register_script( 'give-scripts', $js_dir . 'give' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-scripts' );

		// Load AJAX scripts, if enabled.
		wp_register_script( 'give-ajax', $js_dir . 'give-ajax' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-ajax' );

		// Localize / Pass AJAX vars from PHP,
		wp_localize_script( 'give-checkout-global', 'give_global_vars', $localize_give_vars );
		wp_localize_script( 'give-ajax', 'give_scripts', $localize_give_ajax );

	} else {

		// DEBUG is OFF (one JS file to rule them all!).
		wp_register_script( 'give', $js_dir . 'give.all.min.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give' );

		// Localize / Pass AJAX vars from PHP.
		wp_localize_script( 'give', 'give_global_vars', $localize_give_vars );
		wp_localize_script( 'give', 'give_scripts', $localize_give_ajax );

	}

}

add_action( 'wp_enqueue_scripts', 'give_load_scripts' );

/**
 * Register styles.
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 *
 * @return void
 */
function give_register_styles() {

	if ( ! give_is_setting_enabled( give_get_option( 'css' ) ) ) {
		return;
	}

	wp_register_style( 'give-styles', give_get_stylesheet_uri(), array(), GIVE_VERSION, 'all' );
	wp_enqueue_style( 'give-styles' );

}

add_action( 'wp_enqueue_scripts', 'give_register_styles' );


/**
 * Get the stylesheet URI.
 *
 * @since 1.6
 *
 * @return string
 */
function give_get_stylesheet_uri() {

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// LTR or RTL files.
	$direction = ( is_rtl() ) ? '-rtl' : '';

	$file          = 'give' . $direction . $suffix . '.css';
	$templates_dir = give_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'give' . $direction . '.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'give' . $direction . '.css';
	$give_plugin_style_sheet    = trailingslashit( give_get_templates_dir() ) . $file;

	$uri = false;

	/**
	 * Look in the child theme directory first, followed by the parent theme,
	 * followed by the Give core templates directory also look for the min version first,
	 * followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	 * This allows users to copy just give.css to their theme.
	 */
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$uri = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'give' . $direction . '.css';
		} else {
			$uri = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$uri = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'give' . $direction . '.css';
		} else {
			$uri = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $give_plugin_style_sheet ) || file_exists( $give_plugin_style_sheet ) ) {
		$uri = trailingslashit( give_get_templates_url() ) . $file;
	}

	return apply_filters( 'give_get_stylesheet_uri', $uri );

}

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 *
 * @global       $post
 *
 * @param string $hook Page hook.
 *
 * @return void
 */
function give_load_admin_scripts( $hook ) {

	global $post, $post_type;

	$give_options = give_get_settings();

	// Directories of assets.
	$js_dir     = GIVE_PLUGIN_URL . 'assets/js/admin/';
	$js_plugins = GIVE_PLUGIN_URL . 'assets/js/plugins/';
	$css_dir    = GIVE_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off.
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// LTR or RTL files.
	$direction = ( is_rtl() ) ? '-rtl' : '';

	// Global Admin.
	wp_register_style( 'give-admin-bar-notification', $css_dir . 'adminbar-style.css' );
	wp_enqueue_style( 'give-admin-bar-notification' );

	// Give Admin Only.
	if ( ! apply_filters( 'give_load_admin_scripts', give_is_admin_page(), $hook ) ) {
		return;
	}

	// CSS.
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-fresh' . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );
	wp_register_style( 'give-admin', $css_dir . 'give-admin' . $direction . $suffix . '.css', array(), GIVE_VERSION );
	wp_enqueue_style( 'give-admin' );
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), GIVE_VERSION );
	wp_enqueue_style( 'jquery-chosen' );
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_style( 'wp-color-picker' );


	// JS.
	wp_register_script( 'jquery-chosen', $js_plugins . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_register_script( 'give-accounting', $js_plugins . 'accounting' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-accounting' );

	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'thickbox' );

	wp_register_script( 'give-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-admin-scripts' );

	wp_register_script( 'jquery-flot', $js_plugins . 'jquery.flot' . $suffix . '.js' );
	wp_enqueue_script( 'jquery-flot' );

	wp_register_script( 'give-qtip', $js_plugins . 'jquery.qtip' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-qtip' );

	wp_register_script( 'give-repeatable-fields', $js_plugins . 'repeatable-fields' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-repeatable-fields' );

	// Forms CPT Script.
	if ( $post_type === 'give_forms' ) {
		wp_register_script( 'give-admin-forms-scripts', $js_dir . 'admin-forms' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
		wp_enqueue_script( 'give-admin-forms-scripts' );
	}

	// Settings Scripts.
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'give-settings' ) {
		wp_register_script( 'give-admin-settings-scripts', $js_dir . 'admin-settings' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
		wp_enqueue_script( 'give-admin-settings-scripts' );
	}

	// Price Separators.
	$thousand_separator = give_get_price_thousand_separator();
	$decimal_separator  = give_get_price_decimal_separator();

	// Localize strings & variables for JS.
	wp_localize_script( 'give-admin-scripts', 'give_vars', array(
		'post_id'                        => isset( $post->ID ) ? $post->ID : null,
		'give_version'                   => GIVE_VERSION,
		'thousands_separator'            => $thousand_separator,
		'decimal_separator'              => $decimal_separator,
		'quick_edit_warning'             => __( 'Not available for variable priced forms.', 'give' ),
		'delete_payment'                 => __( 'Are you sure you wish to delete this payment?', 'give' ),
		'delete_payment_note'            => __( 'Are you sure you wish to delete this note?', 'give' ),
		'revoke_api_key'                 => __( 'Are you sure you wish to revoke this API key?', 'give' ),
		'regenerate_api_key'             => __( 'Are you sure you wish to regenerate this API key?', 'give' ),
		'resend_receipt'                 => __( 'Are you sure you wish to resend the donation receipt?', 'give' ),
		'logo'                           => __( 'Logo', 'give' ),
		'use_this_image'                 => __( 'Use this image', 'give' ),
		'one_option'                     => __( 'Choose a form', 'give' ),
		'one_or_more_option'             => __( 'Choose one or more forms', 'give' ),
		'currency_sign'                  => give_currency_filter( '' ),
		'currency_pos'                   => isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before',
		'currency_decimals'              => give_currency_decimal_filter( give_get_price_decimals() ),
		'batch_export_no_class'          => __( 'You must choose a method.', 'give' ),
		'batch_export_no_reqs'           => __( 'Required fields not completed.', 'give' ),
		'reset_stats_warn'               => __( 'Are you sure you want to reset Give? This process is <strong><em>not reversible</em></strong> and will delete all data regardless of test or live mode. Please be sure you have a recent backup before proceeding.', 'give' ),
		'price_format_guide'             => sprintf( __( 'Please enter amount in monetary decimal ( %1$s ) format without thousand separator ( %2$s ) .', 'give' ), $decimal_separator, $thousand_separator ),
		/* translators : %s: Donation form options metabox */
		'confirm_before_remove_row_text' => __( 'Do you want to delete this level?', 'give' ),
		'matched_success_failure_page'   => __( 'You cannot set the success and failed pages to the same page', 'give' ),
		'dismiss_notice_text'            => __( 'Dismiss this notice.', 'give' ),
		'bulk_action' => array(
			'delete'         => array(
				'zero_payment_selected' => __( 'You must choose at least one or more payments to delete.', 'give' ),
				'delete_payment'        => __( 'Are you sure you want to permanently delete this donation?', 'give' ),
				'delete_payments'       => __( 'Are you sure you want to permanently delete the selected {payment_count} donations?', 'give' ),
			),
			'resend_receipt' => array(
				'zero_recipient_selected' => __( 'You must choose at least one or more recipients to resend the email receipt.', 'give' ),
				'resend_receipt'          => __( 'Are you sure you want to resend the email receipt to this recipient?', 'give' ),
				'resend_receipts'         => __( 'Are you sure you want to resend the emails receipt to {payment_count} recipients?', 'give' ),
			),
		),
		'metabox_fields' => array(
			'media' => array(
				'button_title' => esc_html__( 'Choose Attachment', 'give' ),
			)
		)
	) );

	if ( function_exists( 'wp_enqueue_media' ) && version_compare( get_bloginfo( 'version' ), '3.5', '>=' ) ) {
		// call for new media manager.
		wp_enqueue_media();
	}

}

add_action( 'admin_enqueue_scripts', 'give_load_admin_scripts', 100 );

/**
 * Admin Give Icon
 *
 * Echoes the CSS for the Give post type icon.
 *
 * @since 1.0
 *
 * @return void
 */
function give_admin_icon() {
	?>
    <style type="text/css" media="screen">

        <?php if ( version_compare( get_bloginfo( 'version' ), '3.8-RC', '>=' ) || version_compare( get_bloginfo( 'version' ), '3.8', '>=' ) ) { ?>
        @font-face {
            font-family: 'give-icomoon';
            src: url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.eot?ngjl88'; ?>');
            src: url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.eot?#iefixngjl88'?>') format('embedded-opentype'),
            url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.woff?ngjl88'; ?>') format('woff'),
            url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.svg?ngjl88#icomoon'; ?>') format('svg');
            font-weight: normal;
            font-style: normal;
        }

        .dashicons-give:before, #adminmenu div.wp-menu-image.dashicons-give:before {
            font-family: 'give-icomoon';
            font-size: 18px;
            width: 18px;
            height: 18px;
            content: "\e800";
        }

        <?php }  ?>

    </style>
	<?php
}

add_action( 'admin_head', 'give_admin_icon' );

/**
 * Admin js code
 *
 * This code helps to hide license notices for 24 hour if admin user dismissed notice.
 *
 * @since 1.7
 *
 * @return void
 */
function give_admin_hide_notice_shortly_js() {
	?>
    <script>
		jQuery(document).ready(function ($) {
			$('.give-license-notice').on('click', 'button.notice-dismiss', function (e) {
				e.preventDefault();

				var parent             = $(this).parents('.give-license-notice'),
				    dismiss_notice_url = parent.data('dismiss-notice-shortly');

				if (dismiss_notice_url) {
					window.location.assign(dismiss_notice_url);
				}
			});
		});
    </script>
	<?php
}

add_action( 'admin_head', 'give_admin_hide_notice_shortly_js' );
