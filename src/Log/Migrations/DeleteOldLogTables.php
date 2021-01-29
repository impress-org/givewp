<?php

namespace Give\Log\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class DeleteOldLogTables
 * @package Give\Log\Migrations
 */
class DeleteOldLogTables extends Migration {
	/**
	 * @return string
	 */
	public static function id() {
		return 'delete_old_log_tables';
	}

	/**
	 * @return int
	 */
	public static function timestamp() {
		return strtotime( '2021-01-28 14:00' );
	}

	public function run() {
		global $wpdb;

		DB::query( "DROP TABLE {$wpdb->give_logs}, {$wpdb->give_logmeta};" );
	}
}
