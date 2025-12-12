<?php

namespace Give\Framework\Permissions;

use Give\Framework\Permissions\Contracts\HasPrivatePermissions;
use Give\Framework\Permissions\Traits\WithPrivatePermissions;

/**
 * @unreleased
 */
class DonationFormPermissions extends UserPermission implements HasPrivatePermissions
{
    use WithPrivatePermissions;

    /**
     * @unreleased
     */
    public static function getType(): string
    {
        return 'give_form';
    }
}
