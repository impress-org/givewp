<?php

namespace Give\Traits;

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
	private function isParentTheme( $theme ) {
		$currentTheme  = wp_get_theme();
		$themeTemplate = $currentTheme->offsetGet( 'Template' );

		return $theme === $themeTemplate;
	}
}
