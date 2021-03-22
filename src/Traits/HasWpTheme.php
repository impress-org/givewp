<?php

namespace Give\Traits;

use WP_Theme;

/**
 * Trait HasWpTheme
 * @package Give\Traits
 *
 * @since 2.10.0
 */
trait HasWpTheme {

	/**
	 * Return whether or not theme is parent theme of current active theme.
	 *
	 * @since 2.10.0
	 *
	 * @param string $theme
	 *
	 * @return bool
	 */
	protected function isParentTheme( $theme ) {
		$currentTheme  = wp_get_theme();
		$themeTemplate = $currentTheme->offsetGet( 'Template' );

		return $theme === $themeTemplate;
	}

	/**
	 * Return whether or not given theme is child them or not.
	 * Note: is_child_theme WordPress  function does not return correct return immediately after switching theme.
	 *
	 * @since 2.10.0
	 *
	 * @param WP_Theme $theme
	 *
	 * @return bool
	 */
	protected function isChildTheme( $theme ) {
		$themeSlug     = $theme->offsetGet( 'Stylesheet' );
		$themeTemplate = $theme->offsetGet( 'Template' );

		return $themeSlug !== $themeTemplate;
	}
}
