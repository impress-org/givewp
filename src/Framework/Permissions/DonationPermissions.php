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
}
