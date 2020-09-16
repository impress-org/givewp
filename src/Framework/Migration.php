<?php
namespace Give\Framework;

/**
 * Class Migration
 * @package Give\Framework
 *
 * extend this class when create database migration.
 *
 * @since 2.9.0
 */
abstract class Migration {

	/**
	 * Bootstrap migration logic.
	 *
	 * @since 2.9.0
	 *
	 * @return mixed
	 */
	abstract public function up();
}
