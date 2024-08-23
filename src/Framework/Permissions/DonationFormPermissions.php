<?php

namespace Give\Framework\Permissions;
/**
 * @unreleased
 */
class DonationFormPermissions extends UserPermission
{
    /**
     * @unreleased
     */
    public static function getType(): string
    {
        return 'give_form';
    }
}
