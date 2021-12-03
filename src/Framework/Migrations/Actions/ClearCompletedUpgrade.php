<?php

namespace Give\Framework\Migrations\Actions;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * Class ClearCompletedUpgrade
 *
 * Clears an upgrade from the list of completed upgrades so it can be ran again
 */
class ClearCompletedUpgrade
{
    /**
     * @since 2.9.2
     *
     * @param string $upgradeId
     */
    public function __invoke($upgradeId)
    {
        $completedUpgrades = get_option('give_completed_upgrades');

        $upgradeIndex = array_search($upgradeId, $completedUpgrades, true);

        if (false === $upgradeIndex) {
            throw new InvalidArgumentException("No upgrade for the given ID: $upgradeId");
        }

        array_splice($completedUpgrades, $upgradeIndex, 1);

        update_option('give_completed_upgrades', $completedUpgrades);
    }
}
