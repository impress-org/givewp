<?php

namespace Give\MigrationLog\Helpers;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\MigrationLogModel;
use Give\MigrationLog\MigrationLogRepository;

/**
 * Class MigrationOrder
 * @package Give\MigrationLog\Helpers
 *
 * Helper class used to get migration data
 *
 * @since 2.10.0
 */
class MigrationHelper
{

    /**
     * @var MigrationsRegister
     */
    private $migrationRegister;

    /**
     * @var MigrationLogRepository
     */
    private $migrationRepository;

    /**
     * @var MigrationLogModel[]
     */
    private $migrationsInDatabase;

    /**
     * MigrationOrder constructor.
     *
     * @param MigrationsRegister $migrationRegister
     * @param MigrationLogRepository $migrationRepository
     */
    public function __construct(
        MigrationsRegister $migrationRegister,
        MigrationLogRepository $migrationRepository
    ) {
        $this->migrationRegister = $migrationRegister;
        $this->migrationRepository = $migrationRepository;
    }

    /**
     * Get migrations sorted by run order
     *
     * @since 2.10.0
     *
     * @return array
     */
    private function getMigrationsSorted()
    {
        static $migrations = [];

        if (empty($migrations)) {
            /* @var Migration $migrationClass */
            foreach ($this->migrationRegister->getMigrations() as $migrationClass) {
                $migrations[$migrationClass::timestamp() . '_' . $migrationClass::id()] = $migrationClass::id();
            }

            ksort($migrations);
        }

        return $migrations;
    }

    /**
     * Get pending migrations
     *
     * @since 2.10.0
     *
     * @return string[]
     */
    public function getPendingMigrations()
    {
        return array_filter(
            $this->migrationRegister->getMigrations(),
            function ($migrationClass) {
                /* @var Migration $migrationClass */
                foreach ($this->getMigrationsInDatabase() as $migration) {
                    if ($migration->getId() === $migrationClass::id()) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    /**
     * Get migration run order
     *
     * @since 2.10.0
     *
     * @param string $migrationId
     *
     * @return int
     */
    public function getRunOrderForMigration($migrationId)
    {
        return array_search($migrationId, array_values($this->getMigrationsSorted())) + 1;
    }

    /**
     * Retrieves the migrations from the database, caching the results for future retrieval
     *
     * @since 2.10.1
     *
     * @return MigrationLogModel[]
     */
    private function getMigrationsInDatabase()
    {
        if ($this->migrationsInDatabase === null) {
            $this->migrationsInDatabase = $this->migrationRepository->getMigrations();
        }

        return $this->migrationsInDatabase;
    }
}
