<?php

namespace Give\Log\Migrations;

use Give\Log\LogType;
use Give\Log\LogFactory;
use Give\Log\LogCategory;
use Give\Framework\Database\DB;
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

	/**
	 * @inheritDoc
	 */
	public function run() {
		global $wpdb;

		$logs_table    = "{$wpdb->prefix}give_logs";
		$logmeta_table = "{$wpdb->prefix}give_logmeta";

		$result = DB::get_results( "SELECT * FROM {$logs_table}" );

		if ( $result ) {
			foreach ( $result as $log ) {
				$context = [];

				// Add old data as a context
				$context['log_date']    = $log->log_date;
				$context['log_content'] = $log->log_content;

				// Get old log meta
				$logsMeta = DB::get_results(
					DB::prepare( "SELECT * FROM {$logmeta_table} WHERE log_id = %d", $log->ID )
				);

				if ( $logsMeta ) {
					foreach ( $logsMeta as $logMeta ) {
						$context[ $logMeta->meta_key ] = $logMeta->meta_value;
					}
				}

				// Get new type and category
				$data = $this->getNewDataFromType( $log->log_type );

				LogFactory::make(
					$data['type'],
					$log->log_title,
					$data['category'],
					'Log Migration',
					null,
					$context
				)->save();
			}
		}
	}

	/**
	 * Helper method to get new log type and category based on the old log type value
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	private function getNewDataFromType( $type ) {
		switch ( $type ) {
			case 'update':
				return [
					'type'     => LogType::MIGRATION,
					'category' => LogCategory::MIGRATION,
				];

			case 'sale':
			case 'stripe':
			case 'gateway_error':
				return [
					'type'     => LogType::ERROR,
					'category' => LogCategory::PAYMENT,
				];

			default:
				return [
					'type'     => LogType::ERROR,
					'category' => LogCategory::CORE,
				];
		}

	}
}
