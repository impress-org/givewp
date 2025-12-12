<?php

namespace Give\Framework\Permissions\Contracts;

/**
 * Contract for resources that support private item permissions.
 *
 * Use this for post-type resources that can have a "private" status.
 *
 * @unreleased
 */
interface HasPrivatePermissions
{
    /**
     * Check if user can view private items.
     *
     * @unreleased
     */
    public function canViewPrivate(): bool;
}
