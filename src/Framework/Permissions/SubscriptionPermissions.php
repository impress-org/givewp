<?php

namespace Give\Framework\Permissions;

/**
 * Subscriptions do not have custom capabilities, so we use the same as donations.
 * @since 4.14.0
 */
class SubscriptionPermissions extends DonationPermissions
{

}
