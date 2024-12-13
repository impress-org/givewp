<?php

namespace Give\Framework\Permissions;

abstract class UserPermission implements Contracts\UserPermissionsInterface
{
    /**
     * @unreleased
     */
    public function can(string $capability): bool
    {
        switch ($capability) {
            case 'delete':
                $capability = $this->getCapability('delete');
                break;
            case 'read':
            case 'view':
            case 'create':
            case 'update':
            case 'edit':
                $capability = $this->getCapability('edit');
                break;
        }

        return current_user_can($capability);
    }

    /**
     * @unreleased
     */
    protected function getCapability(string $cap): string
    {
        $caps = $this->getCapabilities($this::getType());

        return $caps[$cap];
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
