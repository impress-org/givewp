<?php

namespace Give\Framework\Migrations\Contracts;

/**
 * Class Migration
 *
 * Extend this class when create database migration. up and timestamp are required member functions
 *
 * @since 4.0.0 extend BaseMigration class
 * @since 2.9.0
 */
abstract class Migration extends BaseMigration
{
    /**
     * Bootstrap migration logic.
     *
     * @since 2.9.0
     */
    abstract public function run();

    /**
     * Reverse migration
     * Method used in case the migration fails, and you need to do some cleanup before you can re-run it.
     *
     * @unreleased
     */
    public function reverse(): void
    {
    }
}
