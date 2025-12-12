<?php

namespace Give\Framework\Permissions\Traits;

/**
 * Trait for resources that support private item permissions.
 *
 * @unreleased
 */
trait WithPrivatePermissions
{
    /**
     * Check if user can view private items.
     *
     * @unreleased
     */
    public function canViewPrivate(): bool
    {
        return $this->currentUserCan('read_private');
    }
}
