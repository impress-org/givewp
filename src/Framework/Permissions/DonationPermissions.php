<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Contracts\HasPrivatePermissions;
use Give\Framework\Permissions\Traits\WithPrivatePermissions;

/**
 * @unreleased
 */
class DonationPermissions extends UserPermission implements HasPrivatePermissions
{
    use WithPrivatePermissions;

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
        // Admins always have full access
        if (current_user_can('manage_options')) {
            return true;
        }

        return current_user_can('view_give_payments');
    }
}
