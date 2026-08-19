<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithAdminAccess;

/**
 * @since 4.14.0
 */
class SettingsPermissions
{
    use WithAdminAccess;

    /**
     * Check if user can manage GiveWP settings.
     *
     * @since 4.14.0
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
     * @since 4.14.0
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

    /**
     * @since 4.14.0
     */
    public function manageCap(): string
    {
        return $this->getCapability('manage');
    }
}
