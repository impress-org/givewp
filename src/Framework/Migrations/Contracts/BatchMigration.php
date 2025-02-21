<?php

namespace Give\Framework\Migrations\Contracts;

/**
 * @unreleased
 */
interface BatchMigration
{
    /**
     * @unreleased
     *
     * Get the number of items per batch
     */
    public function getBatchSize(): int;

    /**
     * @unreleased
     *
     * Get the total items count
     */
    public function getItemsCount(): int;

    /**
     * @unreleased
     *
     * Run batch
     */
    public function runBatch($batchNumber);
}
