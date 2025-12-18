<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class DonationPermissions extends UserPermission
{
    /**
     * @unreleased
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
     * @unreleased
     */
    public function canDelete(): bool
    {
        return $this->canEdit();
    }

    /**
     * @unreleased
     */
    public function deleteCap(): string
    {
        return $this->editCap();
    }
}
