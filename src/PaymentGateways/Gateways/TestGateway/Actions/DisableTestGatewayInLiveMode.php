<?php

namespace Give\PaymentGateways\Gateways\TestGateway\Actions;

use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

/**
 * Removes the Test Donation gateway from the enabled gateways while test mode is disabled. The saved
 * gateway settings are left alone, so the gateway returns when test mode is enabled again.
 *
 * @since TBD
 */
class DisableTestGatewayInLiveMode
{
    /**
     * @since TBD
     *
     * @param array $gateways Enabled gateways keyed by gateway ID.
     *
     * @return array The enabled gateways, without the Test Donation gateway while test mode is disabled.
     */
    public function __invoke(array $gateways): array
    {
        if (give_is_test_mode()) {
            return $gateways;
        }

        unset($gateways[TestGateway::id()]);

        return $gateways;
    }
}
