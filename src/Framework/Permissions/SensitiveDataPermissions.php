<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class SensitiveDataPermissions
{
    /**
     * Check if user can view sensitive donor data (e.g., email, address).
     *
     * @unreleased
     */
    public function canView(): bool
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return current_user_can('view_give_sensitive_data');
    }
}
