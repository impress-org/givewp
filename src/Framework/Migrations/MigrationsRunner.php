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
     * @unreleased add support for batch processing
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
                                'description' => sprintf(
                                    esc_html__('GiveWP is running the "%s" migration in the background. You will be notified as soon as it completes.', 'give'),
                                    $migration::title()
                                ),
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
                        give()->notices->register_notice(
                            [
                                'id' => $migrationId,
                                'type' => 'warning',
                                'description' => sprintf(
                                    esc_html__('Incomplete DB migration: %s', 'give'),
                                    $migration::title()
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

                $migrationLog->setStatus(MigrationLogStatus::FAILED);
                $migrationLog->setError($exception);

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
