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
     *
     * Get the first and the last item ID for a batch
     *
     * @unreleased
     *
     * @return array<int, int>
     */
    abstract public function getBatchItemsAfter($lastId): ?array;

    /**
     * Run batch
     *
     * @param $firstId - first item ID in batch
     * @param $lastId  - last item ID in batch
     *
     * @unreleased
     */
    abstract public function runBatch($firstId, $lastId);

    /**
     * Last step of the migration process
     *
     * The purpose of this method is to check if we have new items that came during the migration.
     *
     * @unreleased
     *
     * @param $lastProcessedId
     *
     * @return bool - true if there are new items, otherwise false
     */
    abstract public function hasIncomingData($lastProcessedId): ?bool;
}
