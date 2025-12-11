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
        switch ($capability) {
            case 'view':
            case 'read':
                return current_user_can('view_give_sensitive_data');
            default:
                return false;
        }
    }
}
