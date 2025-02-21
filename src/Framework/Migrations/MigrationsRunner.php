<?php

namespace Give\Framework\Migrations;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Support\Facades\ActionScheduler\AsBackgroundJobs;
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

        // Store and sort migrations by timestamp
        $migrations = [];

        foreach ($this->migrationRegister->getMigrations() as $migrationClass) {
            /* @var Migration $migrationClass */
            $migrations[$migrationClass::timestamp() . '_' . $migrationClass::id()] = $migrationClass;
        }

        ksort($migrations);

        foreach ($migrations as $migrationClass) {
            $migrationId = $migrationClass::id();

            if (in_array($migrationId, $this->completedMigrations, true)) {
                continue;
            }

            $migrationLog = $this->migrationLogFactory->make($migrationId);

            try {
                /** @var Migration $migration */
                $migration = give($migrationClass);

                if (is_subclass_of($migration, BatchMigration::class)) {
                    $status = $this->runBatch($migration);

                    if ($status === MigrationLogStatus::RUNNING) {
                        give()->notices->register_notice(
                            [
                                'id' => $migrationId,
                                'description' => esc_html__('Running DB migration: ' . $migration::title(), 'give'),
                            ]
                        );
                        break;
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

    /**
     * Run migration batch
     *
     * @unreleased
     * @throws Exception
     */
    public function runBatch(BatchMigration $migration): string
    {
        $group = $migration::id();
        $actionHook = 'givewp-batch-' . $group;

        add_action($actionHook, function ($batchNumber) use ($migration) {
            DB::beginTransaction();

            try {
                $migration->runBatch($batchNumber);

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw new Exception($e->getMessage(), 0, $e);
            }
        });

        $actions = AsBackgroundJobs::getActionsByGroup($group);

        // register actions - initial run
        if (empty($actions)) {
            $batches = ceil($migration->getItemsCount() / $migration->getBatchSize());

            for ($i = 0; $i < $batches; $i++) {
                AsBackgroundJobs::enqueueAsyncAction($actionHook, [$i], $group);
            }

            return MigrationLogStatus::RUNNING;
        }

        $pendingActions = AsBackgroundJobs::getActionsByGroup($group, 'pending');

        if ( ! empty($pendingActions)) {
            return MigrationLogStatus::RUNNING;
        }

        $failedActions = AsBackgroundJobs::getActionsByGroup($group, 'failed');

        if ( ! empty($failedActions)) {
            return MigrationLogStatus::FAILED;
        }

        // todo: discuss deleting actions
        // AsBackgroundJobs::deleteActionsByGroup($group);

        return MigrationLogStatus::SUCCESS;
    }
}
