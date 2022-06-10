<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

/**
 * @unreleased
 */
interface SubscriptionDashboardLinkable
{
    /**
     * @unreleased
     */
    public function getGatewaySubscriptionIdLink(
        string $gatewaySubscriptionId,
        string $donationMode
    ): string;
}
