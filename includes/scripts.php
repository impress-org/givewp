<?php
/**
 * Scripts
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $give_options
 * @global $post
 * @return void
 */
function give_load_scripts() {

	global $give_options;

	$js_dir         = GIVE_PLUGIN_URL . 'assets/js/frontend/';
	$js_plugins     = GIVE_PLUGIN_URL . 'assets/js/plugins/';
	$scripts_footer = ( give_get_option( 'scripts_footer' ) == 'on' ) ? true : false;

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	//Localize / PHP to AJAX vars
	$localize_give_checkout = apply_filters( 'give_global_script_vars', array(
		'ajaxurl'             => give_get_ajax_url(),
		'checkout_nonce'      => wp_create_nonce( 'give_checkout_nonce' ),
		'currency_sign'       => give_currency_filter( '' ),
		'currency_pos'        => give_get_currency_position(),
		'thousands_separator' => give_get_price_thousand_separator(),
		'decimal_separator'   => give_get_price_decimal_separator(),
		'no_gateway'          => esc_html__( 'Please select a payment method.', 'give' ),
		'bad_minimum'         => esc_html__( 'The minimum donation amount for this form is', 'give' ),
		'general_loading'     => esc_html__( 'Loading...', 'give' ),
		'purchase_loading'    => esc_html__( 'Please Wait...', 'give' ),
		'number_decimals'  => give_get_price_decimals(),
		'give_version'        => GIVE_VERSION
	) );
	$localize_give_ajax     = apply_filters( 'give_global_ajax_vars', array(
		'ajaxurl'          => give_get_ajax_url(),
		'loading'          => esc_html__( 'Loading', 'give' ),
		// General loading message
		'select_option'    => esc_html__( 'Please select an option', 'give' ),
		// Variable pricing error with multi-purchase option enabled
		'default_gateway'  => give_get_default_gateway( null ),
		'permalinks'       => get_option( 'permalink_structure' ) ? '1' : '0',
		'number_decimals'  => give_get_price_decimals()
	) );

	//DEBUG is On
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

		//General scripts
		wp_register_script( 'give-scripts', $js_dir . 'give' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-scripts' );

		// Load AJAX scripts, if enabled
		wp_register_script( 'give-ajax', $js_dir . 'give-ajax' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give-ajax' );

		//Localize / Pass AJAX vars from PHP
		wp_localize_script( 'give-checkout-global', 'give_global_vars', $localize_give_checkout );
		wp_localize_script( 'give-ajax', 'give_scripts', $localize_give_ajax );


	} else {

		//DEBUG is OFF (one JS file to rule them all!)
		wp_register_script( 'give', $js_dir . 'give.all.min.js', array( 'jquery' ), GIVE_VERSION, $scripts_footer );
		wp_enqueue_script( 'give' );

		//Localize / Pass AJAX vars from PHP
		wp_localize_script( 'give', 'give_global_vars', $localize_give_checkout );
		wp_localize_script( 'give', 'give_scripts', $localize_give_ajax );

	}


}

add_action( 'wp_enqueue_scripts', 'give_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @return void
 */
function give_register_styles() {

	if ( give_get_option( 'disable_css', false ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'give' . $suffix . '.css';
	$templates_dir = give_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'give.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'give.css';
	$give_plugin_style_sheet    = trailingslashit( give_get_templates_dir() ) . $file;

	// Look in the child theme directory first, followed by the parent theme, followed by the Give core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just give.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'give.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'give.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $give_plugin_style_sheet ) || file_exists( $give_plugin_style_sheet ) ) {
		$url = trailingslashit( give_get_templates_url() ) . $file;
	}

	wp_register_style( 'give-styles', $url, array(), GIVE_VERSION, 'all' );
	wp_enqueue_style( 'give-styles' );

}

add_action( 'wp_enqueue_scripts', 'give_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 *
 * @global       $post
 * @global       $give_options
 *
 * @param string $hook Page hook
 *
 * @return void
 */
function give_load_admin_scripts( $hook ) {

	global $wp_version, $post, $post_type, $give_options;

	//Directories of assets
	$js_dir     = GIVE_PLUGIN_URL . 'assets/js/admin/';
	$js_plugins = GIVE_PLUGIN_URL . 'assets/js/plugins/';
	$css_dir    = GIVE_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	//Global Admin:
	wp_register_style( 'give-admin-bar-notification', $css_dir . 'adminbar-style.css' );
	wp_enqueue_style( 'give-admin-bar-notification' );

	//Give Admin Only:
	if ( ! apply_filters( 'give_load_admin_scripts', give_is_admin_page(), $hook ) ) {
		return;
	}

	//CSS
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-fresh' . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );
	wp_register_style( 'give-admin', $css_dir . 'give-admin' . $suffix . '.css', GIVE_VERSION );
	wp_enqueue_style( 'give-admin' );
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), GIVE_VERSION );
	wp_enqueue_style( 'jquery-chosen' );
	wp_enqueue_style( 'thickbox' );

	//JS
	wp_register_script( 'jquery-chosen', $js_plugins . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_register_script( 'give-accounting', $js_plugins . 'accounting' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-accounting' );

	wp_register_script( 'give-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-admin-scripts' );

	wp_register_script( 'jquery-flot', $js_plugins . 'jquery.flot' . $suffix . '.js' );
	wp_enqueue_script( 'jquery-flot' );

	wp_register_script( 'give-qtip', $js_plugins . 'jquery.qtip' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_enqueue_script( 'give-qtip' );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'thickbox' );

	// Forms CPT Script.
	if ( $post_type === 'give_forms' ) {
		wp_register_script( 'give-admin-forms-scripts', $js_dir . 'admin-forms' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
		wp_enqueue_script( 'give-admin-forms-scripts' );
	}

    //Settings Scripts
    if (isset($_GET['page']) && $_GET['page'] == 'give-settings'  ) {
        wp_register_script( 'give-admin-settings-scripts', $js_dir . 'admin-settings' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
        wp_enqueue_script( 'give-admin-settings-scripts' );
    }

    // Price Separators.
    $thousand_separator = give_get_price_thousand_separator();
    $decimal_separator  = give_get_price_decimal_separator();

    //Localize strings & variables for JS
	wp_localize_script( 'give-admin-scripts', 'give_vars', array(
		'post_id'                 => isset( $post->ID ) ? $post->ID : null,
		'give_version'            => GIVE_VERSION,
        'thousands_separator'     => $thousand_separator,
        'decimal_separator'       => $decimal_separator,
        'quick_edit_warning'      => esc_html__( 'Sorry, not available for variable priced forms.', 'give' ),
		'delete_payment'          => esc_html__( 'Are you sure you wish to delete this payment?', 'give' ),
		'delete_payment_note'     => esc_html__( 'Are you sure you wish to delete this note?', 'give' ),
		'revoke_api_key'          => esc_html__( 'Are you sure you wish to revoke this API key?', 'give' ),
		'regenerate_api_key'      => esc_html__( 'Are you sure you wish to regenerate this API key?', 'give' ),
		'resend_receipt'          => esc_html__( 'Are you sure you wish to resend the donation receipt?', 'give' ),
		'copy_download_link_text' => esc_html__( 'Copy these links to your clipboard and give them to your donor.', 'give' ),
		/* translators: %s: form singular label */
		'delete_payment_download' => sprintf( esc_html__( 'Are you sure you wish to delete this %s?', 'give' ), give_get_forms_label_singular() ),
		'one_price_min'           => esc_html__( 'You must have at least one price.', 'give' ),
		'one_file_min'            => esc_html__( 'You must have at least one file.', 'give' ),
		'one_field_min'           => esc_html__( 'You must have at least one field.', 'give' ),
		/* translators: %s: form singular label */
		'one_option'              => sprintf( esc_html__( 'Choose a %s', 'give' ), give_get_forms_label_singular() ),
		/* translators: %s: form plural label */
		'one_or_more_option'      => sprintf( esc_html__( 'Choose one or more %s', 'give' ), give_get_forms_label_plural() ),
		'numeric_item_price'      => esc_html__( 'Item price must be numeric.', 'give' ),
		'numeric_quantity'        => esc_html__( 'Quantity must be numeric.', 'give' ),
		'currency_sign'           => give_currency_filter( '' ),
		'currency_pos'            => isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before',
		'currency_decimals'       => give_currency_decimal_filter(),
		'new_media_ui'            => apply_filters( 'give_use_35_media_ui', 1 ),
		'remove_text'             => esc_html__( 'Remove', 'give' ),
		/* translators: %s: form plural label */
		'type_to_search'          => sprintf( esc_html__( 'Type to search %s', 'give' ), give_get_forms_label_plural() ),
		'batch_export_no_class'   => esc_html__( 'You must choose a method.', 'give' ),
		'batch_export_no_reqs'    => esc_html__( 'Required fields not completed.', 'give' ),
		'reset_stats_warn'        => __( 'Are you sure you want to reset Give? This process is <strong><em>not reversible</em></strong> and will delete all data regardless of test or live mode. Please be sure you have a recent backup before proceeding.', 'give' ),
        'price_format_guide'      => sprintf( esc_html__( 'Please enter amount in monetary decimal ( %1$s ) format without thousand separator ( %2$s ) .', 'give' ), $decimal_separator, $thousand_separator )
    ) );

	if ( function_exists( 'wp_enqueue_media' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
		//call for new media manager
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
 * @global $post_type
 * @global $wp_version
 * @return void
 */
function give_admin_icon() {
	global $wp_version;
	?>
	<style type="text/css" media="screen">

		<?php if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) { ?>
		@font-face {
			font-family: 'give-icomoon';
			src: url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.eot?-ngjl88'; ?>');
			src: url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.eot?#iefix-ngjl88'?>') format('embedded-opentype'),
			url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.woff?-ngjl88'; ?>') format('woff'),
			url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.ttf?-ngjl88'; ?>') format('truetype'),
			url('<?php echo GIVE_PLUGIN_URL . '/assets/fonts/icomoon.svg?-ngjl88#icomoon'; ?>') format('svg');
			font-weight: normal;
			font-style: normal;
		}

		.dashicons-give:before, #adminmenu div.wp-menu-image.dashicons-give:before {
			font-family: 'give-icomoon';
			font-size:18px;
			width:18px;
			height:18px;
			content: "\e800";
		}

		<?php }  ?>

	</style>
	<?php
}

add_action( 'admin_head', 'give_admin_icon' );
