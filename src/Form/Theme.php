<?php

/**
 * Handle Theme registration
 *
 * @package Give
 * @since 2.7.0
 */

namespace Give\Form;

defined( 'ABSPATH' ) || exit;

/**
 * Theme class.
 *
 * @since 2.7.0
 */
class Theme {
	/**
	 * Theme data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * RegisterTheme constructor.
	 *
	 * @param array $args    {
	 *
	 * @type string $id      Theme ID
	 * @type string $name    Theme name
	 * @type string $image   Theme image
	 * @type string $title   Theme title (optional). Can be contain whitelisted HTML tags: strong, a.
	 * @type array  $options Array representation of setting.
	 *
	 * }
	 */
	public function __construct( $args ) {
		$this->data = $args;
	}

	/**
	 * return theme ID.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getID() {
		return $this->data['id'];
	}
}
