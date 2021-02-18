<?php

namespace Give\Log\Migrations;

use Give\Log\Log;
use Give\Log\LogFactory;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Log\ValueObjects\LogCategory;
use Give\Log\ValueObjects\LogType;
use Give_Updates;

class MigrateExistingLogs extends Migration {
	/**
	 * Register background update.
	 *
	 * @param Give_Updates $give_updates
	 *
	 * @since 2.9.7
	 */
	public function register( $give_updates ) {
		$give_updates->register(
			[
				'id'       => self::id(),
				'version'  => '2.9.7',
				'callback' => [ $this, 'run' ],
			]
		);
	}

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
		$give_updates  = Give_Updates::get_instance();

		$perBatch = 100;

		$result = DB::get_results(
			DB::prepare(
				"SELECT * FROM {$logs_table} LIMIT %d, %d",
				$perBatch,
				$give_updates->step * $perBatch
			)
		);

		$totalLogs = DB::get_var( "SELECT COUNT(id) FROM {$logs_table}" );

		if ( $result ) {
			$give_updates->set_percentage(
				$totalLogs,
				$give_updates->step * $perBatch
			);

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

				try {
					LogFactory::make(
						$data['type'],
						$log->log_title,
						$data['category'],
						'Log Migration',
						null,
						$context
					)->save();
				} catch ( \Exception $exception ) {
					// Log migration error
					Log::migration( self::class )
					   ->error( 'Log migration failed', 'MigrateExistingLogs Migration', [ 'exception' => $exception ] );

					$give_updates->__pause_db_update( true );
					update_option( 'give_upgrade_error', 1, false );
				}
			}
		} else {
			give_set_upgrade_complete( self::id() );
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
					'type'     => LogType::ERROR,
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
