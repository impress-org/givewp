<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithAdminAccess;

/**
 * @unreleased
 */
class SensitiveDataPermissions
{
    use WithAdminAccess;

    /**
     * Check if user can view sensitive donor data (e.g., email, address).
     *
     * @unreleased
     */
    public function canView(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return current_user_can('view_give_sensitive_data');
    }
}
