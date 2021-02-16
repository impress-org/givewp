<?php

namespace Give\Log\Migrations;

use Give\Log\Log;
use Give\Log\LogFactory;
use Give\Framework\Database\DB;
use Give\Log\Helpers\LogTypeHelper;
use Give\Framework\Migrations\Contracts\Migration;
use Give_Updates;

class MigrateExistingLogs extends Migration {

	/**
	 * @var LogTypeHelper
	 */
	private $logTypeHelper;

	/**
	 * MigrateExistingLogs constructor.
	 *
	 * @param  LogTypeHelper  $logTypeHelper
	 */
	public function __construct( LogTypeHelper $logTypeHelper ) {
		$this->logTypeHelper = $logTypeHelper;
	}
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

		if ( $result ) {
			$give_updates->set_percentage(
				count( $result ),
				$give_updates->step * 100
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
				$data = $this->logTypeHelper->getDataFromType( $log->log_type );

				try {
					LogFactory::make(
						$data['type'],
						$log->log_title,
						$data['category'],
						'Log Migration',
						$context
					)->save();
				} catch ( \Exception $exception ) {
					// Log migration error
					Log::error( self::class )
					   ->error( 'Log migration failed', 'MigrateExistingLogs Migration', [ 'exception' => $exception ] );

					$give_updates->__pause_db_update( true );
					update_option( 'give_upgrade_error', 1, false );
					wp_die();
				}
			}
		}

		give_set_upgrade_complete( self::id() );
	}
}
