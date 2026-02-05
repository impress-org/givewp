<?php

namespace Give\Framework\Permissions;

/**
 * @since 4.14.0
 */
class DonationPermissions extends UserPermission
{
    /**
     * @since 4.14.0
     */
    public static function getType(): string
    {
        return 'give_payment';
    }

    /**
     * Delete permission maps to edit permission for donations.
     *
     * There is no explicit delete_give_payments capability assigned to most roles,
     * so we use edit_give_payments as the gate for delete operations.
     *
     * @since 4.14.0
     */
    public function canDelete(): bool
    {
        return $this->canEdit();
    }

    /**
     * @since 4.14.0
     */
    public function deleteCap(): string
    {
        return $this->editCap();
    }
}
