<?php

namespace Give\Framework\Migrations\Contracts;

/**
 * @since 4.3.0
 */
interface ReversibleMigration
{
    /**
     * Reverse migration
     */
    public function reverse();
}
