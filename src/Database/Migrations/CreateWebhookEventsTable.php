<?php

namespace Give\Database\Migrations;

class CreateWebhookEventsTable implements DatabaseMigration {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'give_core_v28_create_webhook_events_table';
	}

	/**
	 * @inheritDoc
	 */
	public function getVersion() {
		return '2.8';
	}

	/**
	 * @inheritDoc
	 */
	public function runMigration() {
		// TODO: Implement runMigration() method.
	}
}
