<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class SettingsPermissions
{
    /**
     * Check if user can manage GiveWP settings.
     *
     * @unreleased
     */
    public function canManage(): bool
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return current_user_can('manage_give_settings');
    }
}
