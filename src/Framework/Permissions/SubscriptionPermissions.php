<?php

namespace Give\Framework\Permissions;

/**
 * Subscriptions do not have custom capabilities, so we use the same as donations.
 * @unreleased
 */
class SubscriptionPermissions extends DonationPermissions
{

}
