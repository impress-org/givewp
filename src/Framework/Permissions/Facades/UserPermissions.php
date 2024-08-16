<?php

namespace Give\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormsPermissions;
use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
 *
 * @method static DonationFormsPermissions donationForms()
 */
class UserPermissions extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return UserPermissionsFacade::class;
    }
}
