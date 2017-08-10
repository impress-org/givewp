<?php
/**
 * Plugin Compatibility
 *
 * Functions for compatibility with other plugins.
 *
 * @package     Give
 * @subpackage  Functions/Compatibility
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4
 */


/**
 * Disables the mandrill_nl2br filter while sending Give emails.
 *
 * @since 1.4
 * @return void
 */
function give_disable_mandrill_nl2br() {
	add_filter( 'mandrill_nl2br', '__return_false' );
}

add_action( 'give_email_send_before', 'give_disable_mandrill_nl2br' );


/**
 * This function will clear the Yoast SEO sitemap cache on update of settings
 *
 * @since 1.8.9
 *
 * @return void
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
