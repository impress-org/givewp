<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithAdminAccess;

/**
 * @unreleased
 */
class SettingsPermissions
{
    use WithAdminAccess;

    /**
     * Check if user can manage GiveWP settings.
     *
     * @unreleased
     */
    public function canManage(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return current_user_can('manage_give_settings');
    }

    /**
     * Get the user capability string for the given capability type.
     *
     * @unreleased
     */
    public function getCapability(string $cap): string
    {
        $caps = [
            'manage' => 'manage_give_settings',
        ];

        if (isset($caps[$cap])) {
            return $caps[$cap];
        }

        return '';
    }
}
