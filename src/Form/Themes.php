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
final class Themes {
	/**
	 * Themes
	 *
	 * @var array
	 */
	private static $themes = array();

	/**
	 * Instance.
	 *
	 * @since  version
	 * @access private
	 * @var
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  version
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  version
	 * @access public
	 * @return Themes
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}


	/**
	 * Register core themes
	 *
	 * @since 2.7.0
	 */
	public static function registerDefaults() {
		/**
		 * Register themes
		 */
		$themes = require 'Config/Themes/Load.php';
		foreach ( $themes as $theme ) {
			self::set( new Theme( $theme ) );
		}
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
