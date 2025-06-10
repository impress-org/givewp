<?php

namespace Give\Framework\Migrations;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Controllers\BatchMigrationRunner;
use Give\Log\Log;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogRepository;
use Give\MigrationLog\MigrationLogStatus;

/**
 * Class MigrationsRunner
 *
 * @since 2.9.0
 */
class MigrationsRunner
{
    /**
     * List of completed migrations.
     *
     * @since 2.9.0
     *
     * @var array
     */
    private $completedMigrations;

    /**
     * @since 2.9.0
     *
     * @var MigrationsRegister
     */
    private $migrationRegister;

    /**
     * @since 2.10.0
     *
     * @var MigrationLogFactory
     */
    private $migrationLogFactory;

    /**
     * @since 2.10.0
     * @var MigrationLogRepository
     */
    private $migrationLogRepository;

    /**
     *  MigrationsRunner constructor.
     *
     * @param MigrationsRegister     $migrationRegister
     * @param MigrationLogFactory    $migrationLogFactory
     * @param MigrationLogRepository $migrationLogRepository
     */
    public function __construct(
        MigrationsRegister $migrationRegister,
        MigrationLogFactory $migrationLogFactory,
        MigrationLogRepository $migrationLogRepository
    ) {
        $this->migrationRegister = $migrationRegister;
        $this->migrationLogFactory = $migrationLogFactory;
        $this->migrationLogRepository = $migrationLogRepository;
        $this->completedMigrations = $this->migrationLogRepository->getCompletedMigrationsIDs();
    }

    /**
     * Run database migrations.
     *
     * @since      4.0.0 add support for batch processing
     * @since      2.9.0
     */
    public function run()
    {
        if ( ! $this->hasMigrationToRun()) {
            return;
        }

        // Stop Migration Runner if there are failed migrations
        if ($this->migrationLogRepository->getFailedMigrationsCountByIds(
            $this->migrationRegister->getRegisteredIds()
        )) {
            return;
        }

        $migrations = $this->migrationRegister->getMigrations();

        foreach ($migrations as $migrationClass) {
            $migrationId = $migrationClass::id();

            if (in_array($migrationId, $this->completedMigrations, true)) {
                continue;
            }

            $migrationLog = $this->migrationLogFactory->make($migrationId);

            try {
                /**
                 * @var Migration|BatchMigration $migration
                 */
                $migration = give($migrationClass);

                if ($migration instanceof BatchMigration) {
                    $status = (new BatchMigrationRunner($migration))->run();

                    if ($status === MigrationLogStatus::RUNNING) {
                        give()->notices->register_notice(
                            [
                                'id' => $migrationId,
                                'description' => esc_html__('GiveWP is running database updates in the background. You will be notified as soon as it completes.',
                                    'give'),
                            ]
                        );

                        // Update status to RUNNING
                        if (MigrationLogStatus::RUNNING !== $migrationLog->getStatus()) {
                            $migrationLog->setStatus(MigrationLogStatus::RUNNING);
                            $migrationLog->save();
                        }

                        break;
                    }

                    if ($status === MigrationLogStatus::INCOMPLETE) {
                        $listTableLink = sprintf(
                            '<a href="%s">%s</a>',
                            admin_url('edit.php?post_type=give_forms&page=give-tools&tab=data'),
                            esc_html__('Resume update', 'give')
                        );

                        give()->notices->register_notice(
                            [
                                'id' => $migrationId,
                                'type' => 'warning',
                                'description' => sprintf(
                                    __('Incomplete database update: "%s". %s', 'give'),
                                    $migration::title(),
                                    $listTableLink
                                ),
                            ]
                        );
                    }

                    $migrationLog->setStatus($status);
                } else {
                    $migration->run();
                    $migrationLog->setStatus(MigrationLogStatus::SUCCESS);
                }
            } catch (Exception $exception) {
                DB::rollback();
                $migrationLog
                    ->setStatus(MigrationLogStatus::FAILED)
                    ->setError([
                        'status' => __('Migration failed', 'give'),
                        'error' => [
                            'message' => $exception->getMessage(),
                            'code' => $exception->getCode(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                        ],
                    ]);

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
            }

            try {
                $migrationLog->save();
            } catch (DatabaseQueryException $e) {
                Log::error(
                    'Failed to save migration log',
                    [
                        'Error Message' => $e->getMessage(),
                        'Query Errors' => $e->getQueryErrors(),
                    ]
                );
            }

            // Stop Migration Runner if migration has failed
            if ($migrationLog->getStatus() === MigrationLogStatus::FAILED) {
                break;
            }

            // Commit transaction if successful
            DB::commit();
        }
    }

    /**
     * Return whether or not all migrations completed.
     *
     * @since 2.9.0
     *
     * @return bool
     */
    public function hasMigrationToRun()
    {
        return (bool)array_diff($this->migrationRegister->getRegisteredIds(), $this->completedMigrations);
    }
}
