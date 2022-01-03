<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
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
     * @return GatewayCommand
     * @throws PaymentGatewayException|Exception
     *
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
     * @return GatewayCommand
     * @throws PaymentGatewayException|Exception
     *
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

    /**
     * Handle gateway route method
     *
     * @unreleased
     *
     * @param  int  $donationId
     * @param  string  $method
     *
     * @return void
     */
    public function handleGatewayRouteMethod($donationId, $method);
}
