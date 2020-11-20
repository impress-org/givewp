<?php
namespace Give\Tracking\TrackingData;

use Give\Framework\Collection;
use WP_Theme;

/**
 * Class ThemeData
 *
 * Represents the theme data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class ThemeData implements Collection {

	/**
	 * Returns the collection data.
	 *
	 * @since 2.10.0
	 *
	 * @return array The collection data.
	 */
	public function get() {
		/* @var WP_Theme $theme */
		$theme = wp_get_theme();

		return [
			'theme' => [
				'name'        => $theme->get( 'Name' ),
				'url'         => $theme->get( 'ThemeURI' ),
				'version'     => $theme->get( 'Version' ),
				'author'      => [
					'name' => $theme->get( 'Author' ),
					'url'  => $theme->get( 'AuthorURI' ),
				],
				'parentTheme' => $this->getParentTheme( $theme ),
			],
		];
	}

	/**
	 * Returns the name of the parent theme.
	 *
	 * @since 2.10.0
	 *
	 * @param  WP_Theme  $theme  The theme object.
	 *
	 * @return null|string The name of the parent theme or null.
	 */
	private function getParentTheme( WP_Theme $theme ) {
		if ( is_child_theme() ) {
			return $theme->get( 'Template' );
		}

		return null;
	}
}

