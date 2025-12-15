<?php

namespace Give\Framework\Permissions\Traits;

/**
 * Trait for checking if the current user is an administrator.
 *
 * Provides a reusable method to check for admin access via manage_options capability.
 *
 * @unreleased
 */
trait WithAdminAccess
{
    /**
     * Check if the current user is an administrator (has manage_options capability).
     *
     * @unreleased
     */
    protected function isAdmin(): bool
    {
        return current_user_can('manage_options');
    }
}
