<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class SensitiveDataPermissions
{
    /**
     * @unreleased
     */
    public function can(string $capability): bool
    {
        // Admins always have full access
        if (current_user_can('manage_options')) {
            return true;
        }

        switch ($capability) {
            case 'view':
            case 'read':
                return current_user_can('view_give_sensitive_data');
            default:
                return false;
        }
    }
}
