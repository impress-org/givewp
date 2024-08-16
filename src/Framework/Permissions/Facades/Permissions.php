<?php

namespace Give\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormsPermissions;
use Give\Framework\Support\Facades\Facade;

/**
 * @unreleased
 *
 * @method static DonationFormsPermissions donationForms()
 */
class Permissions extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return PermissionsFacade::class;
    }
}
