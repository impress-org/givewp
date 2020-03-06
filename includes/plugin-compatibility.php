<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Give
 * @subpackage  Functions/Compatibility
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */


/**
 * If Tickera is active then allow TCPDF calls in HTML.
 *
 * TCPDF defines this as false by default as a security precaution. Because Tickera uses GiveWP's composer autoloaded
 * TCPDF the constant is false. Therefore, we will set it to true so the QR code feature works as expected.
 *
 * GitHub Issue:
 * See: https://tcpdf.org/examples/example_049/
 *
 * @since 2.5.4
 */
function give_tickera_qr_compatibility() {
	if ( is_plugin_active( 'tickera-event-ticketing-system/tickera.php' ) ) {
		define( 'K_TCPDF_CALLS_IN_HTML', true );
	}
}

add_action( 'plugins_loaded', 'give_tickera_qr_compatibility' );

/**
 * Disables the mandrill_nl2br filter while sending Give emails.
 *
 * @return void
 * @since 1.4
 */
function give_disable_mandrill_nl2br() {
	add_filter( 'mandrill_nl2br', '__return_false' );
}

add_action( 'give_email_send_before', 'give_disable_mandrill_nl2br' );


/**
 * This function will clear the Yoast SEO sitemap cache on update of settings
 *
 * @return void
 * @since 1.8.9
 */
function give_clear_seo_sitemap_cache_on_settings_change() {
	// Load required file if the fn 'is_plugin_active' doesn't exists.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ( is_plugin_active( 'wordpress-seo/wp-seo.php' )
		   || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) )
		 && class_exists( 'WPSEO_Sitemaps_Cache' )
	) {

		$forms_singular_option = give_get_option( 'forms_singular' );
		$forms_archive_option  = give_get_option( 'forms_singular' );

		// If there is change detected for Single Form View and Form Archives options then proceed.
		if (
			( isset( $_POST['forms_singular'] ) && $_POST['forms_singular'] !== $forms_singular_option ) ||
			( isset( $_POST['forms_archives'] ) && $_POST['forms_archives'] !== $forms_archive_option )
		) {
			// If Yoast SEO or Yoast SEO Premium plugin exists, then update seo sitemap cache.
			$yoast_sitemaps_cache = new WPSEO_Sitemaps_Cache();
			if ( method_exists( $yoast_sitemaps_cache, 'clear' ) ) {
				WPSEO_Sitemaps_Cache::clear();
			}
		}
	}
}

add_action( 'give-settings_save_display', 'give_clear_seo_sitemap_cache_on_settings_change' );

/**
 * This is support for the plugin Elementor. This function
 * disables the Give Shortcodes button on the Elementor's
 * editor page.
 *
 * See link: https://github.com/impress-org/give/issues/3171#issuecomment-387471355
 *
 * @return boolean
 * @since 2.1.3
 */
function give_elementor_hide_shortcodes_button() {

	/**
	 * Is the plugin: Elementor activated?
	 */
	if ( is_plugin_active( 'elementor/elementor.php' ) ) {

		/**
		 * Check user is on the Elementor's editor page, then hide Give Shortcodes Button.
		 */
		if ( isset( $_GET['action'] ) && 'elementor' === give_clean( $_GET['action'] ) ) {
			return false;
		}
	}

	return true;
}

add_filter( 'give_shortcode_button_condition', 'give_elementor_hide_shortcodes_button', 11 );
