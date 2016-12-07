<?php
/**
 * Admin Add-ons
 *
 * @package     Give
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2016, WordImpress
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
 * @since 1.0
 * @return void
 */
function give_add_ons_page() {
	ob_start(); ?>
	<div class="wrap" id="give-add-ons">
		<h1><?php echo get_admin_page_title(); ?>
			&nbsp;&mdash;&nbsp;<a href="https://givewp.com/addons/" class="button-primary give-view-addons-all" target="_blank"><?php esc_html_e( 'View All Add-ons', 'give' ); ?>
				<span class="dashicons dashicons-external"></span></a>
		</h1>

		<p><?php esc_html_e( 'The following Add-ons extend the functionality of Give.', 'give' ); ?></p>
		<?php echo give_add_ons_get_feed(); ?>
	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @since 1.0
 * @return string $cache
 */
function give_add_ons_get_feed() {

	$addons_debug = false; //set to true to debug
	$cache        = get_transient( 'give_add_ons_feed' );

	if ( $cache === false || $addons_debug === true && WP_DEBUG === true ) {
		$feed = wp_remote_get( 'https://givewp.com/downloads/feed/', array( 'sslverify' => false ) );

		if ( ! is_wp_error( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( 'give_add_ons_feed', $cache, 3600 );
			}
		} else {
			$cache = '<div class="error"><p>' . esc_html__( 'There was an error retrieving the Give Add-ons list from the server. Please try again later.', 'give' ) . '</div>';
		}
	}

	return $cache;

}