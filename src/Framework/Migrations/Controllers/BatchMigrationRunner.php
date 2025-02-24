<?php

namespace Give\Framework\Migrations\Controllers;

use ActionScheduler_Store;
use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\MigrationLog\MigrationLogStatus;

/**
 * Batch Migration runner controller
 *
 * @unreleased
 */
class BatchMigrationRunner
{
    /**
     * @var BatchMigration
     */
    private $migration;

    /**
     * @var ActionScheduler_Store
     */
    private $actionSchedulerStore;

    /**
     * @unreleased
     */
    public function __construct(BatchMigration $migration)
    {
        $this->migration = $migration;
        $this->actionSchedulerStore = ActionScheduler_Store::instance();
        $this->registerAction();
    }

    /**
     * Run batch migration
     *
     * @unreleased
     */
    public function run(): string
    {
        $actions = $this->actionSchedulerStore->query_actions([
            'group' => $this->getGroup(),
            'per_page' => 0,
        ]);

        if ( ! $actions) {
            $itemsCount = $this->migration->getItemsCount();

            // Bailout if there are no items to process
            if ( ! $itemsCount) {
                return MigrationLogStatus::SUCCESS;
            }

            $batches = ceil($itemsCount / $this->migration->getBatchSize());

            // Register migration action for each batch
            for ($i = 0; $i < $batches; $i++) {
                as_enqueue_async_action($this->getHook(), [$i], $this->getGroup());
            }

            return MigrationLogStatus::RUNNING;
        }

        $pendingActions = (int)$this->actionSchedulerStore->query_actions([
            'group' => $this->getGroup(),
            'status' => [
                ActionScheduler_Store::STATUS_RUNNING,
                ActionScheduler_Store::STATUS_PENDING,
            ],
        ], 'count');

        if ($pendingActions) {
            return MigrationLogStatus::RUNNING;
        }

        $failedActions = (int)$this->actionSchedulerStore->query_actions([
            'group' => $this->getGroup(),
            'status' => [
                ActionScheduler_Store::STATUS_FAILED,
                ActionScheduler_Store::STATUS_CANCELED,
            ],
        ], 'count');

        if ($failedActions) {
            return MigrationLogStatus::INCOMPLETE;
        }

        // If everything went well, delete scheduled actions
        foreach ($actions as $actionId) {
            $this->actionSchedulerStore->delete_action($actionId);
        }

        return MigrationLogStatus::SUCCESS;
    }

    /**
     * @unreleased
     */
    public function getHook(): string
    {
        return 'givewp-batch-' . $this->migration::id();
    }

    /**
     * @unreleased
     */
    public function getGroup(): string
    {
        return $this->migration::id();
    }

    /**
     * Register batch migration action
     *
     * @unreleased
     *
     * @throws Exception
     */
    private function registerAction()
    {
        add_action($this->getHook(), function ($batchNumber) {
            DB::beginTransaction();

            try {
                $this->migration->runBatch($batchNumber);

                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw new Exception($e->getMessage(), 0, $e);
            }
        });
    }

    /**
     * Reschedule failed and canceled actions
     *
     * @unreleased
     */
    public function rescheduleFailedActions()
    {
        $failedActions = $this->actionSchedulerStore->query_actions([
            'group' => $this->getGroup(),
            'per_page' => 0,
            'status' => [
                ActionScheduler_Store::STATUS_FAILED,
                ActionScheduler_Store::STATUS_CANCELED,
            ],
        ]);

        if ( ! is_array($failedActions)) {
            return;
        }

        foreach ($failedActions as $actionId) {
            $action = $this->actionSchedulerStore->fetch_action($actionId);

            // Reschedule new action
            as_enqueue_async_action($this->getHook(), $action->get_args(), $this->getGroup());

            // Delete failed action
            $this->actionSchedulerStore->delete_action($actionId);
        }
    }
}
