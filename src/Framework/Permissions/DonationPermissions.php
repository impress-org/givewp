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
     * Donations have a special 'view_give_payments' capability separate from edit.
     *
     * @unreleased
     */
    public function can(string $capability): bool
    {
        switch ($capability) {
            case 'view':
            case 'read':
                return current_user_can('view_give_payments');
            default:
                return parent::can($capability);
        }
    }
}
