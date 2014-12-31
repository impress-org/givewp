<?php
/**
 * Scripts
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, WordImpress
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

	global $give_options, $post;

	$js_dir = GIVE_PLUGIN_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	if ( give_is_cc_verify_enabled() ) {
		wp_enqueue_script( 'creditCardValidator', $js_dir . 'jquery.creditCardValidator' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );
	}

	wp_enqueue_script( 'give-checkout-global', $js_dir . 'give-checkout-global' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );
	wp_localize_script( 'give-checkout-global', 'give_global_vars', array(
		'ajaxurl'           => give_get_ajax_url(),
		'checkout_nonce'    => wp_create_nonce( 'give_checkout_nonce' ),
		'currency_sign'     => give_currency_filter( '' ),
		'currency_pos'      => isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before',
		'no_gateway'        => __( 'Please select a payment method', 'give' ),
		'discount_applied'  => __( 'Discount Applied', 'give' ), // Discount verified message
		'no_email'          => __( 'Please enter an email address before applying a discount code', 'give' ),
		'no_username'       => __( 'Please enter a username before applying a discount code', 'give' ),
		'purchase_loading'  => __( 'Please Wait...', 'give' ),
		'complete_purchase' => __( 'Purchase', 'give' ),
		'give_version'      => GIVE_VERSION
	) );


	// Load AJAX scripts, if enabled
	wp_enqueue_script( 'give-ajax', $js_dir . 'give-ajax' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );
	wp_localize_script( 'give-ajax', 'give_scripts', array(
			'ajaxurl'          => give_get_ajax_url(),
			'position_in_cart' => isset( $position ) ? $position : - 1,
			'loading'          => __( 'Loading', 'give' ),
			// General loading message
			'select_option'    => __( 'Please select an option', 'give' ),
			// Variable pricing error with multi-purchase option enabled
			'ajax_loader'      => GIVE_PLUGIN_URL . 'assets/images/loading.gif',
			'default_gateway'  => give_get_default_gateway(),
			'permalinks'       => get_option( 'permalink_structure' ) ? '1' : '0'
		)
	);

}

add_action( 'wp_enqueue_scripts', 'give_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @global $give_options
 * @return void
 */
function give_register_styles() {

	global $give_options;

	if ( isset( $give_options['disable_styles'] ) ) {
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

	wp_enqueue_style( 'give-styles', $url, array(), GIVE_VERSION );

}

add_action( 'wp_enqueue_scripts', 'give_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global       $post
 *
 * @param string $hook Page hook
 *
 * @return void
 */
function give_load_admin_scripts( $hook ) {

	if ( ! apply_filters( 'give_load_admin_scripts', give_is_admin_page(), $hook ) ) {
		return;
	}

	global $wp_version, $post;

	$js_dir  = GIVE_PLUGIN_URL . 'assets/js/';
	$css_dir = GIVE_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	//CSS
	wp_enqueue_style( 'jquery-ui-css', $css_dir . 'jquery-ui-fresh' . $suffix . '.css' );

	wp_enqueue_style( 'give-admin', $css_dir . 'give-admin' . $suffix . '.css', GIVE_VERSION );

	//JS
	wp_enqueue_script( 'give-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
	wp_localize_script( 'give-admin-scripts', 'give_vars', array(
		'post_id' => isset( $post->ID ) ? $post->ID : null,
	) );
	wp_enqueue_script( 'jquery-ui-datepicker' );


	wp_localize_script( 'give-admin-scripts', 'give_vars', array(
			'post_id'                 => isset( $post->ID ) ? $post->ID : null,
			'give_version'             => GIVE_VERSION,
			'quick_edit_warning'      => __( 'Sorry, not available for variable priced products.', 'give' ),
			'delete_payment'          => __( 'Are you sure you wish to delete this payment?', 'give' ),
			'delete_payment_note'     => __( 'Are you sure you wish to delete this note?', 'give' ),
			'delete_tax_rate'         => __( 'Are you sure you wish to delete this tax rate?', 'give' ),
			'revoke_api_key'          => __( 'Are you sure you wish to revoke this API key?', 'give' ),
			'regenerate_api_key'      => __( 'Are you sure you wish to regenerate this API key?', 'give' ),
			'resend_receipt'          => __( 'Are you sure you wish to resend the purchase receipt?', 'give' ),
			'copy_download_link_text' => __( 'Copy these links to your clipboard and give them to your customer', 'give' ),
			'delete_payment_download' => sprintf( __( 'Are you sure you wish to delete this %s?', 'give' ), give_get_forms_label_singular() ),
			'one_price_min'           => __( 'You must have at least one price', 'give' ),
			'one_file_min'            => __( 'You must have at least one file', 'give' ),
			'one_field_min'           => __( 'You must have at least one field', 'give' ),
			'one_option'              => sprintf( __( 'Choose a %s', 'give' ), give_get_forms_label_singular() ),
			'one_or_more_option'      => sprintf( __( 'Choose one or more %s', 'give' ), give_get_forms_label_plural() ),
			'numeric_item_price'      => __( 'Item price must be numeric', 'give' ),
			'numeric_quantity'        => __( 'Quantity must be numeric', 'give' ),
			'currency_sign'           => give_currency_filter(''),
			'currency_pos'            => isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before',
			'currency_decimals'       => give_currency_decimal_filter(),
			'new_media_ui'            => apply_filters( 'give_use_35_media_ui', 1 ),
			'remove_text'             => __( 'Remove', 'give' ),
			'type_to_search'          => sprintf( __( 'Type to search %s', 'give' ), give_get_forms_label_plural() ),
		));


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

	$menu_icon = '\f507';

	?>
	<style type="text/css" media="screen">
		<?php if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) { ?>
		#adminmenu #menu-posts-give_forms .wp-menu-image:before {
			content: '<?php echo $menu_icon; ?>';
		}

		<?php }  ?>

	</style>
<?php
}

add_action( 'admin_head', 'give_admin_icon' );
