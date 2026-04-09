<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithAdminAccess;

/**
 * @since 4.14.0
 */
class ReportsPermissions
{
    use WithAdminAccess;

    /**
     * Check if user can view reports.
     *
     * @since 4.14.0
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
     * @since 4.14.0
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
     * @since 4.14.0
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
     * @since 4.14.0
     */
    public function viewCap(): string
    {
        return $this->getCapability('view');
    }

    /**
     * @since 4.14.0
     */
    public function exportCap(): string
    {
        return $this->getCapability('export');
    }
}
