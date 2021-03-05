<?php

namespace Give\Log\Helpers;

/**
 * Class LogsLegacyTable
 * @package Give\Log\Helpers
 *
 * @unreleased
 */
class LegacyLogsTable {
	/**
	 * Check if legacy logs table exists
	 *
	 * @return bool
	 */
	public function exist() {
		global $wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare( "SHOW TABLES LIKE '%s'", "{$wpdb->prefix}give_logs" )
		);
	}
}
