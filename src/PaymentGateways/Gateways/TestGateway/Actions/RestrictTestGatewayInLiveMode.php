<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Actions;

use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

/**
 * Removes the Test Donation gateway from the enabled gateways when the current user cannot use it.
 * While test mode is disabled, only users who can manage GiveWP settings can use it. The saved
 * gateway settings are left alone.
 *
 * @since TBD
 */
class RestrictTestGatewayInLiveMode
{
    /**
     * @since TBD
     *
     * @param array $gateways Enabled gateways keyed by gateway ID.
     *
     * @return array The enabled gateways, without the Test Donation gateway when the current user cannot use it.
     */
    public function __invoke(array $gateways): array
    {
        if (! array_key_exists(TestGateway::id(), $gateways) || give(TestGateway::class)->isAvailable()) {
            return $gateways;
        }

        unset($gateways[TestGateway::id()]);

        return $gateways;
    }
}
