<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @since 2.18.0
 */
interface PaymentGatewayInterface
{
    /**
     * Return a unique identifier for the gateway
     *
     * @since 2.18.0
     *
     * @return string
     */
    public static function id();

    /**
     * Return a unique identifier for the gateway
     *
     * @since 2.18.0
     *
     * @return string
     */
    public function getId();

    /**
     * Returns a human-readable name for the gateway
     *
     * @since 2.18.0
     *
     * @return string - Translated text
     */
    public function getName();

    /**
     * Returns a human-readable label for use when a donor selects a payment method to use
     *
     * @since 2.18.0
     *
     * @return string - Translated text
     */
    public function getPaymentMethodLabel();

    /**
     * Determines if subscriptions are supported
     *
     * @since 2.18.0
     *
     * @return bool
     */
    public function supportsSubscriptions();

    /**
     * Create a payment with gateway
     *
     * @since 2.18.0
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
     * @since 2.18.0
     *
     * @param  GatewayPaymentData  $gatewayPaymentData
     * @return void
     */
    public function handleCreatePayment(GatewayPaymentData $gatewayPaymentData);

    /**
     * Create a subscription with gateway
     *
     * @since 2.18.0
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
     * @since 2.18.0
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
