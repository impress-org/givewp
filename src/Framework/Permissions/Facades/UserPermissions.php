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
use Give\Framework\Support\Facades\Facade;

/**
 * Facade for accessing user permission checks for different resources.
 *
 * @since 4.14.0
 *
 * @method static DonationFormPermissions donationForms()
 * @method static DonationPermissions donations()
 * @method static DonorPermissions donors()
 * @method static CampaignPermissions campaigns()
 * @method static ReportsPermissions reports()
 * @method static SensitiveDataPermissions sensitiveData()
 * @method static SettingsPermissions settings()
 * @method static SubscriptionPermissions subscriptions()
 * @method static EventPermissions events()
 */
class UserPermissions extends Facade
{
    protected function getFacadeAccessor(): string
    {
        return UserPermissionsFacade::class;
    }
}
