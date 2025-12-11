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
