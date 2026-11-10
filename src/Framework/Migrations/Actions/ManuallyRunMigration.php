<?php

namespace Give\Framework\Migrations\Actions;

use Exception;
use Give\Framework\Migrations\Contracts\Migration;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogStatus;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- transaction statements around a migration that an admin runs by hand; they cannot be cached.

class ManuallyRunMigration
{
    /**
     * Manually runs the migration and then marks the migration as finished if successful
     *
     * @since 4.19.1 Log the exception class and message instead of the full dump.
     * @since 2.9.2
     *
     * @param Migration $migration
     *
     */
    public function __invoke(Migration $migration)
    {
        global $wpdb;

        $wpdb->query('START TRANSACTION');

        try {
            $migration->run();
        } catch (Exception $exception) {
            $wpdb->query('ROLLBACK');

            give_record_log('Migration Failed', get_class($exception) . ': ' . $exception->getMessage(), 0, 'update');
            give()->notices->register_notice(
                [
                    'id' => 'migration-failure',
                    'description' => sprintf(
                        '%1$s <a href="https://givewp.com/support/">https://givewp.com/support</a>',
                        esc_html__(
                            'There was a problem running the migrations. Please reach out to GiveWP support for assistance:',
                            'give'
                        )
                    ),
                ]
            );

            throw $exception;
        }

        // Commit transaction if successful
        $wpdb->query('COMMIT');

        $this->updateMigrationsSetting($migration::id());
    }

    /**
     * Marks the migration as completed in the migration log
     *
     * @since 4.19.1 Record the run in the migration log. The option this used to update no longer exists, so a valid run ended in a fatal error.
     * @since 2.9.2
     *
     * @param string $migrationId
     */
    private function updateMigrationsSetting($migrationId)
    {
        give(MigrationLogFactory::class)
            ->make($migrationId, MigrationLogStatus::SUCCESS)
            ->save();
    }
}
