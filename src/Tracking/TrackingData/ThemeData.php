<?php
namespace Give\Tracking\TrackingData;

use Give\Tracking\Contracts\TrackData;
use WP_Theme;

/**
 * Class ThemeData
 *
 * Represents the theme data.
 *
 * @since 2.10.0
 * @package Give\Tracking\TrackingData
 */
class ThemeData implements TrackData {

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

		$themeSlug = $theme->offsetGet( 'Stylesheet' );
		$data      = [
			'name'    => $theme->get( 'Name' ),
			'slug'    => $themeSlug,
			'url'     => $theme->get( 'ThemeURI' ),
			'version' => $theme->get( 'Version' ),
			'author'  => [
				'name' => $theme->get( 'Author' ),
				'url'  => $theme->get( 'AuthorURI' ),
			],
		];

		$themeTemplate = $theme->offsetGet( 'Template' );
		if ( $themeSlug !== $themeTemplate ) {
			$parentTheme         = wp_get_theme( $themeTemplate );
			$data['parentTheme'] = $this->getParentTheme( $parentTheme );
		}

		return $data;
	}

	/**
	 * Returns parent theme data.
	 *
	 * @since 2.10.0
	 *
	 * @param  WP_Theme  $theme  The theme object.
	 *
	 * @return array TParent theme data.
	 */
	private function getParentTheme( WP_Theme $theme ) {
		return [
			'name'    => $theme->get( 'Name' ),
			'slug'    => $theme->offsetGet( 'Stylesheet' ),
			'url'     => $theme->get( 'ThemeURI' ),
			'version' => $theme->get( 'Version' ),
			'author'  => [
				'name' => $theme->get( 'Author' ),
				'url'  => $theme->get( 'AuthorURI' ),
			],
		];
	}
}

