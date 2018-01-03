<?php
/**
 * Defines the Assets class
 *
 * @package Give\Includes
 * @since   2.1.0
 */

namespace Give\Includes;

/**
 * Loads the plugin's assets.
 *
 * Registers and enqueues plugin styles and scripts. Asset versions are based
 * on the current plugin version.
 *
 * All script and style handles should be registered in this class even if they
 * are enqueued dynamically by other classes.
 *
 * @since 2.1.0
 */
class Assets {

	/**
	 * Suffix used when loading minified assets.
	 *
	 * @since  2.1.0
	 * @var    string
	 * @access private
	 */
	private $suffix;

	/**
	 * Whether scripts should be loaded in the footer or not.
	 *
	 * @since  2.1.0
	 * @var    bool
	 * @access private
	 */
	private $scripts_footer;

	/**
	 * Instantiates the Assets class.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {
		$this->suffix         = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$this->scripts_footer = ( give_is_setting_enabled( give_get_option( 'scripts_footer' ) ) ) ? true : false;
		$this->register();
	}

	/**
	 * Registers assets via WordPress hooks.
	 *
	 * @since 2.1.0
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_scripts' ) );
		}
	}

	/**
	 * Registers all plugin styles.
	 *
	 * @since 2.1.0
	 */
	public function register_styles() {
		wp_register_style( 'give-admin', GIVE_PLUGIN_URL . 'assets/dist/css/admin' . $this->suffix . '.css', array(), GIVE_VERSION );
		wp_register_style( 'give-styles', $this->get_frontend_stylesheet_uri(), array(), GIVE_VERSION, 'all' );
	}

	/**
	 * Registers all plugin scripts.
	 *
	 * @since 2.1.0
	 */
	public function register_scripts() {
		wp_register_script( 'give-admin', GIVE_PLUGIN_URL . 'assets/dist/js/admin' . $this->suffix . '.js', array( 'jquery' ), GIVE_VERSION );
		wp_register_script( 'give', GIVE_PLUGIN_URL . 'assets/dist/js/give' . $this->suffix . '.js', array( 'jquery' ), GIVE_VERSION, $this->scripts_footer );
	}

	/**
	 * Enqueues admin styles.
	 *
	 * @since 2.1.0
	 */
	public function admin_enqueue_styles() {
		wp_enqueue_style( 'give-admin' );
	}

	/**
	 * Enqueues public styles.
	 *
	 * @since 2.1.0
	 */
	public function public_enqueue_styles() {
		wp_enqueue_style( 'give-styles' );
	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @since 2.1.0
	 *
	 * @param $hook
	 */
	public function admin_enqueue_scripts( $hook ) {

		// Give Admin Only.
		if ( ! apply_filters( 'give_load_admin_scripts', give_is_admin_page(), $hook ) ) {
			return;
		}

		wp_enqueue_script( 'give-admin' );
	}

	/**
	 * Enqueues public scripts.
	 *
	 * @since 2.1.0
	 */
	public function public_enqueue_scripts() {

		wp_enqueue_script( 'give' );

		// Localize / PHP to AJAX vars.
		$localize_give_vars = apply_filters( 'give_global_script_vars', array(
			'ajaxurl'                    => give_get_ajax_url(),
			'checkout_nonce'             => wp_create_nonce( 'give_checkout_nonce' ), // Do not use this nonce. Its deprecated.
			'currency'                   => give_get_currency(),
			'currency_sign'              => give_currency_filter( '' ),
			'currency_pos'               => give_get_currency_position(),
			'thousands_separator'        => give_get_price_thousand_separator(),
			'decimal_separator'          => give_get_price_decimal_separator(),
			'no_gateway'                 => __( 'Please select a payment method.', 'give' ),
			'bad_minimum'                => __( 'The minimum custom donation amount for this form is', 'give' ),
			'general_loading'            => __( 'Loading...', 'give' ),
			'purchase_loading'           => __( 'Please Wait...', 'give' ),
			'number_decimals'            => give_get_price_decimals(),
			'give_version'               => GIVE_VERSION,
			'magnific_options'           => apply_filters(
				'give_magnific_options',
				array(
					'main_class'        => 'give-modal',
					'close_on_bg_click' => false,
				)
			),
			'form_translation'           => apply_filters(
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
			'confirm_email_sent_message' => __( 'Please check your email and click on the link to access your complete donation history.', 'give' ),
			'ajax_vars'                  => apply_filters( 'give_global_ajax_vars', array(
				'ajaxurl'         => give_get_ajax_url(),
				'ajaxNonce'       => wp_create_nonce( 'give_ajax_nonce' ),
				'loading'         => __( 'Loading', 'give' ),
				// General loading message.
				'select_option'   => __( 'Please select an option', 'give' ),
				// Variable pricing error with multi-donation option enabled.
				'default_gateway' => give_get_default_gateway( null ),
				'permalinks'      => get_option( 'permalink_structure' ) ? '1' : '0',
				'number_decimals' => give_get_price_decimals(),
			) ),
		) );

		wp_localize_script( 'give', 'give_global_vars', $localize_give_vars );

	}

	/**
	 * Get the stylesheet URI.
	 *
	 * @since   1.6
	 * @updated 2.0.1 Moved to class and renamed as method.
	 *
	 * @return string
	 */
	public function get_frontend_stylesheet_uri() {

		// Use minified libraries if SCRIPT_DEBUG is turned off.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		// LTR or RTL files.
		$direction     = ( is_rtl() ) ? '-rtl' : '';
		$file          = 'give' . $direction . $suffix . '.css';
		$templates_dir = give_get_theme_template_dir_name();
		// Directory paths to CSS files to support checking via file_exists().
		$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
		$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'give' . $direction . '.css';
		$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
		$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'give' . $direction . '.css';
		$give_plugin_style_sheet    = trailingslashit( GIVE_PLUGIN_DIR ) . 'assets/dist/css/' . $file;

		$uri = false;

		/**
		 * Locate the Give stylesheet:
		 *
		 * a. Look in the child theme directory first, followed by the parent theme
		 * b. followed by the Give core templates directory also look for the min version first,
		 * c. followed by non minified version, even if SCRIPT_DEBUG is not enabled. This allows users to copy just give.css to their theme.
		 * d. Finally, fallback to the standard Give version. This is the default styles included within the plugin.
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
		} elseif ( file_exists( $give_plugin_style_sheet ) ) {
			$uri = trailingslashit( GIVE_PLUGIN_URL ) . 'assets/dist/css/' . $file;
		}

		return apply_filters( 'give_get_stylesheet_uri', $uri );

	}

}
