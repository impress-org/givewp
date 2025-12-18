<?php

namespace Give\Framework\Permissions\Facades;

use Give\Framework\Permissions\CampaignPermissions;
use Give\Framework\Permissions\DonationFormPermissions;
use Give\Framework\Permissions\DonationPermissions;
use Give\Framework\Permissions\DonorPermissions;
use Give\Framework\Permissions\ReportsPermissions;
use Give\Framework\Permissions\SensitiveDataPermissions;
use Give\Framework\Permissions\SettingsPermissions;
use Give\Framework\Permissions\SubscriptionPermissions;

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

    /**
     * @unreleased
     */
    public function campaigns(): CampaignPermissions
    {
        return new CampaignPermissions();
    }

    /**
     * @unreleased
     */
    public function reports(): ReportsPermissions
    {
        return new ReportsPermissions();
    }

    /**
     * @unreleased
     */
    public function sensitiveData(): SensitiveDataPermissions
    {
        return new SensitiveDataPermissions();
    }

    /**
     * @unreleased
     */
    public function settings(): SettingsPermissions
    {
        return new SettingsPermissions();
    }

    /**
     * @unreleased
     */
    public function subscriptions(): SubscriptionPermissions
    {
        return new SubscriptionPermissions();
    }
}
