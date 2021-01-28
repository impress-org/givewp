<?php

namespace Give\Log\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

class CreateNewLogTables extends Migration {

	public function run() {
		// migration logic
	}

	public static function check() {
		global $wpdb;

		return (bool) $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}give_log" ) );
	}
}
