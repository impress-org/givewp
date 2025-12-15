<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Traits\WithPrivatePermissions;

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
     * Donations have a special 'view_give_payments' capability separate from edit.
     *
     * @unreleased
     */
    public function canView(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return current_user_can('view_give_payments');
    }
}
