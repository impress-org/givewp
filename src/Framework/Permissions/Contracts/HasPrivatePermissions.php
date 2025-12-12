<?php

namespace Give\Framework\Permissions\Contracts;

/**
 * Contract for resources that support restricted visibility permissions.
 *
 * Implement this interface for resources where certain items may have
 * restricted visibility (e.g., not publicly accessible).
 *
 * @unreleased
 */
interface HasPrivatePermissions
{
    /**
     * Check if user can view items with restricted visibility.
     *
     * @unreleased
     */
    public function canViewPrivate(): bool;
}
