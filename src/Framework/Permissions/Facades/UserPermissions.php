<?php

namespace Give\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormPermissions;
use Give\Framework\Permissions\DonationPermissions;
use Give\Framework\Permissions\DonorPermissions;
use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
 *
 * @method static DonationFormPermissions donationForms()
 * @method static DonationPermissions donations()
 * @method static DonorPermissions donors()
 */
class UserPermissions extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return UserPermissionsFacade::class;
    }
}
