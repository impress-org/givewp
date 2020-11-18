<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;

/**
 * Class WebsiteData
 *
 * Represents the default data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class WebsiteData implements Collection {

	/**
	 * Returns the collection data.
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
			'isMultisite'  => is_multisite(),
			'siteLanguage' => get_bloginfo( 'language' ),
		];
	}
}

