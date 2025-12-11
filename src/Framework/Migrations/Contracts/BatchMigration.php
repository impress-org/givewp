<?php

namespace Give\Framework\Migrations\Contracts;

/**
 * Extend this class when you need database migration to run in batches.
 *
 * @since 4.0.0
 */
abstract class BatchMigration extends BaseMigration
{
    /**
     * Get the number of items per batch
     *
     * @since 4.0.0
     */
    abstract public function getBatchSize(): int;

    /**
     *
     * Get the total items count
     *
     * @since 4.0.0
     */
    abstract public function getItemsCount(): int;


    /**
     *
     * Get the first and the last item ID for a batch
     *
     * @since 4.0.0
     *
     * @return array{0: int, 1: int} the first value is the first id and the second value is the last id of a batch
     */
    abstract public function getBatchItemsAfter($lastId): ?array;

    /**
     * Run batch
     *
     * @param $firstId - first item ID in batch
     * @param $lastId  - last item ID in batch
     *
     * @since 4.0.0
     */
    abstract public function runBatch($firstId, $lastId);

    /**
     * Last step of the migration process
     *
     * The purpose of this method is to check if we have new items that came during the migration.
     *
     * @since 4.0.0
     *
     * @param $lastProcessedId
     *
     * @return bool - true if there are new items, otherwise false
     */
    abstract public function hasMoreItemsToBatch($lastProcessedId): ?bool;
}
