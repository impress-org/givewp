<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Http\Response\Types\JsonResponse;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @unreleased
 */
interface PaymentGatewayInterface
{
    /**
     * Return a unique identifier for the gateway
     *
     * @unreleased
     *
     * @return string
     */
    public static function id();

    /**
     * Return a unique identifier for the gateway
     *
     * @unreleased
     *
     * @return string
     */
    public function getId();

    /**
     * Returns a human-readable name for the gateway
     *
     * @unreleased
     *
     * @return string - Translated text
     */
    public function getName();

    /**
     * Returns a human-readable label for use when a donor selects a payment method to use
     *
     * @unreleased
     *
     * @return string - Translated text
     */
    public function getPaymentMethodLabel();

    /**
     * Determines if subscriptions are supported
     *
     * @unreleased
     *
     * @return bool
     */
    public function supportsSubscriptions();

    /**
     * Create a payment with gateway
     *
     * @unreleased
     *
     * @param  GatewayPaymentData  $paymentData
     *
     * @return JsonResponse|RedirectResponse
     */
    public function createPayment(GatewayPaymentData $paymentData);

    /**
     * Handle creating a payment with gateway
     *
     * @unreleased
     *
     * @param  GatewayPaymentData  $gatewayPaymentData
     * @return void
     */
    public function handleCreatePayment(GatewayPaymentData $gatewayPaymentData);

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

    /**
     * Handle creating a subscription with gateway
     *
     * @unreleased
     *
     * @param  GatewayPaymentData  $paymentData
     * @param  GatewaySubscriptionData  $subscriptionData
     *
     * @return void
     */
    public function handleCreateSubscription(
        GatewayPaymentData $paymentData,
        GatewaySubscriptionData $subscriptionData
    );
}
