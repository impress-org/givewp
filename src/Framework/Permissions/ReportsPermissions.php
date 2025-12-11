<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class ReportsPermissions
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
                return current_user_can('view_give_reports');
            case 'export':
                return current_user_can('export_give_reports');
            default:
                return false;
        }
    }
}
