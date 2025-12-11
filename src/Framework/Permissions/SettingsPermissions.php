<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class SettingsPermissions
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
            case 'manage':
            case 'edit':
            case 'update':
                return current_user_can('manage_give_settings');
            default:
                return false;
        }
    }
}
