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

    /**
     * Get the user capability string for the given capability type.
     *
     * @unreleased
     */
    public function getCapability(string $cap): string
    {
        $caps = [
            'view' => 'view_give_reports',
            'export' => 'export_give_reports',
        ];

        if (isset($caps[$cap])) {
            return $caps[$cap];
        }

        return '';
    }

    /**
     * @unreleased
     */
    public function viewCap(): string
    {
        return $this->getCapability('view');
    }

    /**
     * @unreleased
     */
    public function exportCap(): string
    {
        return $this->getCapability('export');
    }
}
