<?php
namespace Give\Framework;

/**
 * Class Migration
 * @package Give\Framework
 *
 * extend this class when create database migration. up and timestamp are required member functions
 *
 * @since 2.9.0
 */
abstract class Migration {

	/**
	 * Bootstrap migration logic.
	 *
	 * @since 2.9.0
	 */
	public static function run(){}

	/**
	 * Return migration timestamp.
	 *
	 * @since 2.9.0
	 *
	 * @return string Return static date in mysql format but do not contain '-', ':', ' '. For example "20190916000000"
	 */
	public static function timestamp(){}
}
