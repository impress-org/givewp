<?php
/**
 * Admin Add-ons
 *
 * @package     Give
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @return void
 * @since 1.0
 */
function give_add_ons_page() {
	?>
	<div class="wrap" id="give-addons">

		<div class="give-addons-header">

			<div class="give-admin-logo give-addon-h1">
				<a href="https://givewp.com/?utm_campaign=admin&utm_source=addons&utm_medium=imagelogo"
				   class="give-admin-logo-link" target="_blank"><img
						src="<?php echo GIVE_PLUGIN_URL . 'assets/dist/images/give-logo-large-no-tagline.png'; ?>"
						alt="<?php _e( 'Click to Visit GiveWP in a new tab.', 'give' ); ?>"/><span><?php echo esc_html( get_admin_page_title() ); ?></span></a>
			</div>
		</div>

		<div class="give-subheader give-clearfix">

			<h1>Give Add-ons</h1>

			<p class="give-subheader-right-text"><?php esc_html_e( 'Maximize your fundraising potential with official add-ons from GiveWP.com.', 'give' ); ?></p>

			<div class="give-hidden">
				<hr class="wp-header-end">
			</div>
		</div>
		<div class="give-price-bundles-wrap give-clearfix">
			<?php give_add_ons_feed( 'price-bundle' ); ?>
		</div>

		<div class="give-addons-directory-wrap give-clearfix">
			<?php give_add_ons_feed( 'addons-directory' ); ?>
		</div>
	</div>
	<?php

}

/**
 * Enqueue GiveWP font family for just the add-ons page.
 *
 * @param $hook
 */
function give_addons_enqueue_scripts( $hook ) {

	// Only enqueue on the addons page.
	if ( 'give_forms_page_give-addons' !== $hook ) {
		return;
	}

	// https://fonts.google.com/specimen/Montserrat?selection.family=Montserrat:400,400i,600,600i,700,700i,800,800i
	wp_register_style( 'give_addons_font_families', 'https://fonts.googleapis.com/css?family=Montserrat:400,400i,600,600i,700,700i,800,800i', false );
	wp_enqueue_style( 'give_addons_font_families' );
}

add_action( 'admin_enqueue_scripts', 'give_addons_enqueue_scripts' );
