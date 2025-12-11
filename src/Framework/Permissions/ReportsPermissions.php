<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class ReportsPermissions
{
    /**
     * Check if user can view reports.
     *
     * @unreleased
     */
    public function canView(): bool
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return current_user_can('view_give_reports');
    }

    /**
     * Check if user can export reports.
     *
     * @unreleased
     */
    public function canExport(): bool
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return current_user_can('export_give_reports');
    }
}
