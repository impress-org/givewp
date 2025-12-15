<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithAdminAccess;

/**
 * @unreleased
 */
class ReportsPermissions
{
    use WithAdminAccess;

    /**
     * Check if user can view reports.
     *
     * @unreleased
     */
    public function canView(): bool
    {
        if ($this->isAdmin()) {
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
        if ($this->isAdmin()) {
            return true;
        }

        return current_user_can('export_give_reports');
    }
}
