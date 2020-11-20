<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;

/**
 * Class WebsiteData
 *
 * Represents the website data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class WebsiteData implements Collection {

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
			'siteTitle'    => get_option( 'blogname' ),
			'timestamp'    => (int) date( 'Uv' ),
			'wpVersion'    => $wp_version,
			'homeURL'      => home_url(),
			'adminURL'     => admin_url(),
			'email'        => get_bloginfo( 'admin_email' ),
			'isMultisite'  => absint( is_multisite() ),
			'siteLanguage' => get_bloginfo( 'language' ),
		];
	}
}

