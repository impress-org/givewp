<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;

/**
 * Class PaymentCaptureCompleted
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @unreleased
 */
class CustomerMerchantIntegrationProductSubscriptionUpdated extends PaymentEventListener
{
    /**
     * @inheritDoc
     */
    public function processEvent($event)
    {
        // Refresh account status.
        give(onBoardingRedirectHandler::class)->refreshAccountStatus();
    }
}
