<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;

/**
 * Class PaymentCaptureCompleted
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @unreleased
 */
class CustomerMerchantIntegrationProductSubscriptionUpdated extends PaymentEventListener
{
    /**
     * This function will process the PayPal event.
     *
     * Connected PayPal account will be verified again if:
     * - Account is not ready for payment, and
     * - Merchant ID from event matches with connected PayPal account.
     *
     * @inheritDoc
     */
    public function processEvent($event)
    {
        $onBoardingRedirectHandler = give(onBoardingRedirectHandler::class);
        $merchantId = $event->resource->merchant_id;

        /* Refresh sandbox account */
        $liveMerchantDetailsRepository = clone give(MerchantDetails::class);
        $liveMerchantDetailsRepository->setMode('live');
        $liveMerchantDetails = $liveMerchantDetailsRepository->getDetails();

        if ($liveMerchantDetails->merchantId === $merchantId) {
            // Do not need to refresh account status if live account is already ready.
            if ($liveMerchantDetails->accountIsReady) {
                return;
            }

            // Refresh account status.
            $onBoardingRedirectHandler->setModeForServices('live');
            $onBoardingRedirectHandler->refreshAccountStatus();
        }

        /* Refresh sandbox account */
        $sandboxMerchantDetailsRepository = clone give(MerchantDetails::class);
        $sandboxMerchantDetailsRepository->setMode('sandbox');
        $sandboxMerchantDetails = $sandboxMerchantDetailsRepository->getDetails();

        if ($sandboxMerchantDetails->merchantId === $merchantId) {
            // Do not need to refresh account status if sandbox account is already ready.
            if ($sandboxMerchantDetails->accountIsReady) {
                return;
            }

            // Refresh account status.
            $onBoardingRedirectHandler->setModeForServices('sandbox');
            $onBoardingRedirectHandler->refreshAccountStatus();
        }
    }
}
