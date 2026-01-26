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
use Give\Framework\Permissions\EventPermissions;
/**
 * This is a facade for interacting with WP and GiveWP permissions.
 *
 * @see https://wordpress.org/documentation/article/roles-and-capabilities/
 *
 * @since 4.14.0
 */
class UserPermissionsFacade
{
    /**
     * @since 4.14.0
     */
    public function donationForms(): DonationFormPermissions
    {
        return new DonationFormPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function donations(): DonationPermissions
    {
        return new DonationPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function donors(): DonorPermissions
    {
        return new DonorPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function campaigns(): CampaignPermissions
    {
        return new CampaignPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function reports(): ReportsPermissions
    {
        return new ReportsPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function sensitiveData(): SensitiveDataPermissions
    {
        return new SensitiveDataPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function settings(): SettingsPermissions
    {
        return new SettingsPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function subscriptions(): SubscriptionPermissions
    {
        return new SubscriptionPermissions();
    }

    /**
     * @since 4.14.0
     */
    public function events(): EventPermissions
    {
        return new EventPermissions();
    }
}
