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
 * Class Give_Admin
 */
class Give_Addons {
	/**
	 * Instance.
	 *
	 * @since  2.5.0
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.5.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return Give_Addons
	 * @since  2.5.0
	 * @access public
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup Admin
	 *
	 * @sinve  2.5.0
	 * @access private
	 */
	private function setup() {

	}
}

Give_Addons::get_instance();


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
				<a href="https://givewp.com/&utm_campaign=admin&utm_source=addons&utm_medium=imagelogo"
				   class="give-admin-logo-link" target="_blank"><img
						src="<?php echo GIVE_PLUGIN_URL . 'assets/dist/images/give-logo-large-no-tagline.png'; ?>"
						alt="<?php _e( 'Click to Visit GiveWP in a new tab.', 'give' ); ?>"/><span><?php echo esc_html( get_admin_page_title() ); ?></span></a>
			</div>
		</div>

		<div class="give-subheader give-clearfix">

			<h1>Give Add-ons</h1>

			<p class="give-subheader-right-text"><?php esc_html_e( 'Maximize your fundraising potential with official add-ons from GiveWP.com.', 'give' ); ?></p>

		</div>

		<div class="give-price-bundle">
			<?php give_add_ons_feed( 'price-bundle' ); ?>
		</div>
		<?php // give_add_ons_feed(); ?>
	</div>
	<?php

}

/**
 * Add-ons Render Feed
 *
 * Renders the add-ons page feed.
 *
 * @param string $feed_type
 *
 * @return void
 * @since 1.0
 */
function give_add_ons_feed( $feed_type = '' ) {

	$addons_debug = true; // set to true to debug
	$cache_key    = $feed_type ? "give_add_ons_feed_{$feed_type}" : 'give_add_ons_feed';
	$cache        = Give_Cache::get( $cache_key, true );

	$feed_url = Give_License::get_website_url() . 'downloads/feed/';

	if ( false === $cache || ( true === $addons_debug && true === WP_DEBUG ) ) {

		switch ( $feed_type ) {
			case 'price-bundle':
				$feed_url = Give_License::get_website_url() . 'downloads/feed/addons-price-bundles.php';
		}

		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$feed = vip_safe_wp_remote_get( $feed_url, false, 3, 1, 20, array( 'sslverify' => false ) );
		} else {
			$feed = wp_remote_get( $feed_url, array( 'sslverify' => false ) );
		}

		if ( ! is_wp_error( $feed ) ) {
			if ( ! empty( $feed['body'] ) ) {
				$cache = wp_remote_retrieve_body( $feed );
				Give_Cache::set( $cache_key, $cache, HOUR_IN_SECONDS, true );
			}
		} else {
			$cache = sprintf(
				'<div class="error"><p>%s</p></div>',
				esc_html__( 'There was an error retrieving the Give Add-ons list from the server. Please try again later.', 'give' )
			);
		}
	}

	echo wp_kses_post( $cache );
}

// @todo: convert all staging site link to live site
// @todo check if all plugin follow download file and github repo naming standards
