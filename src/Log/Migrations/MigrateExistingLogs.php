<?php

namespace Give\Log\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

class MigrateExistingLogs extends Migration {
	/**
	 * @return string
	 */
	public static function id() {
		return 'migrate_existing_logs';
	}

	/**
	 * @return int
	 */
	public static function timestamp() {
		return strtotime( '2021-01-28 13:00' );
	}

	public function run() {
		// migration logic
	}

	public function check() {

	}
}
