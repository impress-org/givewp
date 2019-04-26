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
	add_thickbox();
	// @todo: show plugin activate button if plugin uploaded successfully.
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
		<?php // give_add_ons_feed(); ?>
	</div>
	<?php

}

/**
 * Add-ons Render Feed
 *
 * Renders the add-ons page feed.
 *
 * @return void
 * @since 1.0
 */
function give_add_ons_feed() {

	$addons_debug = false; // set to true to debug
	$cache        = Give_Cache::get( 'give_add_ons_feed', true );

	if ( false === $cache || ( true === $addons_debug && true === WP_DEBUG ) ) {
		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$feed = vip_safe_wp_remote_get( 'https://givewp.com/downloads/feed/', false, 3, 1, 20, array( 'sslverify' => false ) );
		} else {
			$feed = wp_remote_get( 'https://givewp.com/downloads/feed/', array( 'sslverify' => false ) );
		}

		if ( ! is_wp_error( $feed ) && ! empty( $feed ) ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				Give_Cache::set( 'give_add_ons_feed', $cache, HOUR_IN_SECONDS, true );
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
