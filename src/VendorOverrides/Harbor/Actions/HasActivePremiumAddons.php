<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor\Actions;

/**
 * Return true if another brand already has premium presence or if any GiveWP premium add-on is active.
 *
 * Hooked into the `lw_harbor/premium_plugin_exists` filter.
 *
 * @unreleased
 */
class HasActivePremiumAddons
{
    /**
     * @unreleased
     */
    public function __invoke(bool $otherBrandsExistence): bool
    {
        if ($otherBrandsExistence) {
            return true;
        }

        $premiumAddons = give_get_plugins(['only_premium_add_ons' => true]);

        foreach ($premiumAddons as $addon) {
            if ($addon['Status'] === 'active') {
                return true;
            }
        }

        return false;
    }
}
