<?php

namespace Give\Framework\Permissions\Facades;

use Give\Framework\Permissions\DonationFormsPermissions;

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
    public function donationForms(): DonationFormsPermissions
    {
        return new DonationFormsPermissions();
    }
}
