<?php

namespace Give\Database\Tables;

class WebhookEvents {
	public function getTableName() {
		global $wpdb;

		return "{$wpdb->prefix}give_webhook_events";
	}

	public function getCreateTableSql() {
		return /** @lang SQL */ "
			CREATE TABLE {$this->getTableName()} (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				event_id VARCHAR(255)
				type VARCHAR(255)
				status VARCHAR(255)
				created_at TIMESTAMP

				PRIMARY KEY(id)
				KEY event_id_key (event_id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;
		";
	}
}
