<?php

namespace Give\Framework\Migrations\Contracts;

use Give\Framework\Exceptions\Primitives\RuntimeException;

/**
 * Class Migration
 *
 * Extend this class when create database migration. up and timestamp are required member functions
 *
 * @since 2.9.0
 */
abstract class Migration {
	/**
	 * Bootstrap migration logic.
	 *
	 * @since 2.9.0
	 */
	abstract public function run();

	/**
	 * Return a unique identifier for the migration
	 *
	 * @return string
	 */
	public static function id() {
		throw new RuntimeException( 'A unique ID must be provided for the migration' );
	}

	/**
	 * Return a Unix Timestamp for when the migration was created
	 *
	 * Example: strtotime( '2020-09-16 12:30:00')
	 *
	 * @since 2.9.0
	 *
	 * @return int Unix timestamp for when the migration was created
	 */
	public static function timestamp() {
		throw new RuntimeException( 'This method must be overridden to return a valid unix timestamp' );
	}

	/**
	 * Return migration title
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public static function title() {
		return static::id();
	}


	/**
	 * Return migration source
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public static function source() {
		return esc_html__( 'GiveWP Core', 'give' );
	}
}
