<?php

namespace Give\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormPermissions;
use Give\Framework\Permissions\DonationPermissions;
use Give\Framework\Permissions\DonorPermissions;

/**
 * This is a facade for interacting with WP and GiveWP permissions.
 *
 * @see https://wordpress.org/documentation/article/roles-and-capabilities/
 *
 * @unreleased
 */
class UserPermissionsFacade
{
    /**
     * @unreleased
     */
    public function donationForms(): DonationFormPermissions
    {
        return new DonationFormPermissions();
    }

    /**
     * @unreleased
     */
    public function donations(): DonationPermissions
    {
        return new DonationPermissions();
    }

    /**
     * @unreleased
     */
    public function donors(): DonorPermissions
    {
        return new DonorPermissions();
    }
}
