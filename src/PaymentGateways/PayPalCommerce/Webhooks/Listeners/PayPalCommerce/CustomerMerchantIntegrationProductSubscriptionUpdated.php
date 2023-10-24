<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;

/**
 * Class PaymentCaptureCompleted
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @since 3.0.0
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

        $modes = ['live', 'sandbox'];

        foreach ($modes as $mode ){
            $merchantDetailsRepository = clone give(MerchantDetails::class);
            $merchantDetailsRepository->setMode($mode);
            $merchantDetails = $merchantDetailsRepository->getDetails();

            if ($merchantDetails->merchantIdInPayPal === $merchantId) {
                // Do not need to refresh account status if live account is already ready.
                if ($merchantDetails->accountIsReady) {
                    return;
                }

                // Refresh account status.
                $onBoardingRedirectHandler->setModeForServices($mode);
                $onBoardingRedirectHandler->refreshAccountStatus();
            }
        }
    }
}
