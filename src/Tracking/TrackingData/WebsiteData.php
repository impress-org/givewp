<?php
namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;

/**
 * Class WebsiteData
 *
 * Represents the website data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class WebsiteData implements TrackData {

	/**
	 * Returns the collection data.
	 *
	 * @since 2.10.0
	 *
	 * @return array The collection data.
	 */
	public function get() {
		global $wp_version;

		return [
			'site_title'     => get_option( 'blogname' ),
			'wp_version'     => $wp_version,
			'givewp_version' => GIVE_VERSION,
			'home_url'       => home_url(),
			'admin_url'      => admin_url(),
			'is_multisite'   => absint( is_multisite() ),
			'site_language'  => get_bloginfo( 'language' ),
		];
	}
}

