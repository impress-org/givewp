<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\PaymentGateways\Commands\GatewayCommand;
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
     * @return GatewayCommand
     */
    public function createSubscription(GatewayPaymentData $paymentData, GatewaySubscriptionData $subscriptionData);
}