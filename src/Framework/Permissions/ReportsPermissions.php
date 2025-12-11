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
