<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @since 2.18.0
 */
interface PaymentGatewayInterface extends SubscriptionModuleInterface
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
     * @param GatewayPaymentData $paymentData
     *
     * @return GatewayCommand
     * @throws PaymentGatewayException|Exception
     *
     */
    public function createPayment(GatewayPaymentData $paymentData);

    /**
     * @since 2.20.0
     *
     * @param Donation $donation
     *
     * @return mixed
     */
    public function refundDonation(Donation $donation);
}
