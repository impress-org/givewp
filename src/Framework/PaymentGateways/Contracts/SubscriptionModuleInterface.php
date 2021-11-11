<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

interface SubscriptionModuleInterface
{
    /**
     * Create a subscription with gateway
     *
     * @unreleased
     *
     * @param  GatewayPaymentData  $paymentData
     * @param  GatewaySubscriptionData  $subscriptionData
     *
     * @return JsonResponse|RedirectResponse
     */
    public function createSubscription(GatewayPaymentData $paymentData, GatewaySubscriptionData $subscriptionData);
}