<?php

namespace Give\Framework\Migrations\Contracts;

/**
 * @unreleased
 */
interface ReversibleMigration
{
    /**
     * Reverse migration
     */
    public function reverse();
}
