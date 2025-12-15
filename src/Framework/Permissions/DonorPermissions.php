<?php

namespace Give\Framework\Permissions;

/**
 * @unreleased
 */
class DonorPermissions extends UserPermission
{
    /**
     * @unreleased
     */
    public static function getType(): string
    {
        return 'give_donor';
    }
}
