<?php

namespace Give\Framework\Migrations\Contracts;

/**
 * Extend this class when you need database migration to run in batches.
 *
 * @unreleased
 */
abstract class BatchMigration extends BaseMigration
{
    /**
     * Get the number of items per batch
     *
     * @unreleased
     */
    abstract public function getBatchSize(): int;

    /**
     *
     * Get the total items count
     *
     * @unreleased
     */
    abstract public function getItemsCount(): int;

    /**
     * Run batch
     *
     * @unreleased
     */
    abstract public function runBatch($batchNumber);
}
