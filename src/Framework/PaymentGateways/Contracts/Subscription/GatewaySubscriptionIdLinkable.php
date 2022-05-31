<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

/**
 * @unreleased
 */
interface GatewaySubscriptionIdLinkable
{
    /**
     * @unreleased
     */
    public function getGatewaySubscriptionIdLink(
        string $gatewaySubscriptionId,
        string $donationMode
    ): string;
}
