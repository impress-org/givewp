<?php

namespace Give\Log\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Log\Helpers\LegacyLogsTable;
use Give\Log\Helpers\LogTypeHelper;
use Give\Log\LogFactory;
use Give_Updates;

class MigrateExistingLogs extends Migration
{

    /**
     * @var LogTypeHelper
     */
    private $logTypeHelper;

    /**
     * @var LegacyLogsTable
     */
    private $legacyLogsTable;

    /**
     * MigrateExistingLogs constructor.
     *
     * @param LogTypeHelper $logTypeHelper
     * @param LegacyLogsTable $legacyLogsTable
     */
    public function __construct(
        LogTypeHelper $logTypeHelper,
        LegacyLogsTable $legacyLogsTable
    ) {
        $this->logTypeHelper = $logTypeHelper;
        $this->legacyLogsTable = $legacyLogsTable;
    }

    /**
     * Register background update.
     *
     * @since 2.10.0
     *
     * @param Give_Updates $give_updates
     *
     */
    public function register($give_updates)
    {
        $give_updates->register(
            [
                'id' => self::id(),
                'version' => '2.10.0',
                'callback' => [$this, 'run'],
            ]
        );
    }

    /**
     * @return string
     */
    public static function id()
    {
        return 'migrate_existing_logs';
    }

    /**
     * @return string
     */
    public static function title()
    {
        return esc_html__('Migrate existing logs to give_log table', 'give');
    }

    /**
     * @return int
     */
    public static function timestamp()
    {
        return strtotime('2021-01-28 13:00');
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        global $wpdb;

        // Check if legacy table exist
        if ( ! $this->legacyLogsTable->exist()) {
            return;
        }

        $logs_table = "{$wpdb->prefix}give_logs";
        $logmeta_table = "{$wpdb->prefix}give_logmeta";

        $give_updates = Give_Updates::get_instance();

        $perBatch = 500;

        $offset = ($give_updates->step - 1) * $perBatch;

        $result = DB::get_results(
            DB::prepare(
                "SELECT * FROM {$logs_table} LIMIT %d OFFSET %d",
                $perBatch,
                $offset
            )
        );

        $totalLogs = DB::get_var("SELECT COUNT(id) FROM {$logs_table}");

        if ($result) {
            $give_updates->set_percentage(
                $totalLogs,
                $give_updates->step * $perBatch
            );

            foreach ($result as $log) {
                $context = [];

                // Add old data as a context
                $context['log_date'] = $log->log_date;
                $context['log_content'] = $log->log_content;

                // Get old log meta
                $logsMeta = DB::get_results(
                    DB::prepare("SELECT * FROM {$logmeta_table} WHERE log_id = %d", $log->ID)
                );

                if ($logsMeta) {
                    foreach ($logsMeta as $logMeta) {
                        $context[$logMeta->meta_key] = $logMeta->meta_value;
                    }
                }

                // Get new type and category
                $data = $this->logTypeHelper->getDataFromType($log->log_type);

                try {
                    LogFactory::make(
                        $data['type'],
                        $log->log_title,
                        $data['category'],
                        'Log Migration',
                        $context
                    )->save();
                } catch (\Exception $exception) {
                    $give_updates->__pause_db_update(true);
                    update_option('give_upgrade_error', 1, false);
                }
            }
        } else {
            give_set_upgrade_complete(self::id());
        }
    }
}
