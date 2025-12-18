<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithAdminAccess;

abstract class UserPermission implements Contracts\UserPermissionsInterface
{
    use WithAdminAccess;

    /**
     * Check if user can create (maps to edit capability).
     *
     * @unreleased
     */
    public function canCreate(): bool
    {
        return $this->currentUserCan('edit');
    }

    /**
     * Check if user can view/read (maps to edit capability).
     *
     * @unreleased
     */
    public function canView(): bool
    {
        return $this->currentUserCan('edit');
    }

    /**
     * Check if user can edit.
     *
     * @unreleased
     */
    public function canEdit(): bool
    {
        return $this->currentUserCan('edit');
    }

    /**
     * Check if user can delete.
     *
     * @unreleased
     */
    public function canDelete(): bool
    {
        return $this->currentUserCan('delete');
    }

    /**
     * Check if the current user has the specified capability.
     *
     * @unreleased
     */
    protected function currentUserCan(string $capability): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return current_user_can($this->getCapability($capability));
    }

    /**
     * Get the user capability string for the given capability type.
     *
     * @unreleased
     */
    public function getCapability(string $cap): string
    {
        $caps = $this->getCapabilities($this::getType());

        return $caps[$cap] ?? '';
    }

    /**
     * @unreleased
     */
    protected function getCapabilities(string $type): array
    {
        return [
            // Post type.
            "edit" => "edit_{$type}s",
            "edit_others" => "edit_others_{$type}s",
            "publish" => "publish_{$type}s",
            "read_private" => "read_private_{$type}s",
            "delete" => "delete_{$type}s",
            "delete_private" => "delete_private_{$type}s",
            "delete_published" => "delete_published_{$type}s",
            "delete_others" => "delete_others_{$type}s",
            "edit_private" => "edit_private_{$type}s",
            "edit_published" => "edit_published_{$type}s",

            // Terms / taxonomies.
            "manage_terms" => "manage_{$type}_terms",
            "edit_terms" => "edit_{$type}_terms",
            "delete_terms" => "delete_{$type}_terms",
            "assign_terms" => "assign_{$type}_terms",

            // Custom capabilities.
            "view_stats" => "view_{$type}_stats",
            "import" => "import_{$type}s"
        ];
    }
}
