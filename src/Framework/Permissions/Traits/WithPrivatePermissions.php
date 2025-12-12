<?php

namespace Give\Framework\Permissions\Traits;

/**
 * Trait for resources that support restricted visibility permissions.
 *
 * Use this trait with HasPrivatePermissions interface for resources
 * where certain items may have restricted visibility.
 *
 * @unreleased
 */
trait WithPrivatePermissions
{
    /**
     * Check if user can view items with restricted visibility.
     *
     * @unreleased
     */
    public function canViewPrivate(): bool
    {
        return $this->currentUserCan('read_private');
    }
}
