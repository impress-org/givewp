<?php

/**
 * Handle Form Themes
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Themes class
 *
 * @since 2.7.0
 */
class Themes {
	/**
	 * Themes
	 *
	 * @var array
	 */
	private static $themes = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  version
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get Registered themes
	 *
	 * @since 2.7.0
	 *
	 * @return array
	 */
	public static function get() {
		return self::$themes;
	}

	/**
	 * Get Registered theme
	 *
	 * @since 2.7.0
	 *
	 * @param string $themeID
	 *
	 * @return Theme
	 */
	public static function getTheme( $themeID ) {
		return self::$themes[ $themeID ];
	}

	/**
	 * Themes constructor.
	 *
	 * @param Theme $registerTheme
	 */
	public static function set( $registerTheme ) {
		self::$themes[ $registerTheme->getID() ] = $registerTheme;
	}
}
