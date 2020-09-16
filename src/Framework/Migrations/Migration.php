<?php
namespace Give\Framework\Migrations;

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
	 * @return string Static date in mysql format. Date string does not contain '-', ':', ' '. For example "20190916000000"
	 */
	public static function timestamp(){}
}
