<?php

namespace Give\Database\Migrations;

interface DatabaseMigration {
	/**
	 * Returns a unique ID for the migration. This must be unique across all addons.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Returns the plugin version the migration was introduced in, in the form of "1.3.0".
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * A callback function for running the migration at the right time.
	 *
	 * @since 2.8.0
	 *
	 * @return void
	 */
	public function runMigration();
}
