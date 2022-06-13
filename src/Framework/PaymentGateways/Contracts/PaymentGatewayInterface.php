<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

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
    public static function id(): string;

    /**
     * Return a unique identifier for the gateway
     *
     * @since 2.18.0
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns a human-readable name for the gateway
     *
     * @since 2.18.0
     *
     * @return string - Translated text
     */
    public function getName(): string;

    /**
     * Returns a human-readable label for use when a donor selects a payment method to use
     *
     * @since 2.18.0
     *
     * @return string - Translated text
     */
    public function getPaymentMethodLabel(): string;

    /**
     * Determines if subscriptions are supported
     *
     * @since 2.18.0
     *
     * @return bool
     */
    public function supportsSubscriptions(): bool;

    /**
     * Create a payment with gateway
     *
     * @since 2.18.0
     *
     * @return GatewayCommand|RedirectOffsite|void
     *
     * @throws PaymentGatewayException
     * @throws Exception
     */
    public function createPayment(Donation $donation);

    /**
     * @since 2.20.0
     *
     * @param Donation $donation
     *
     * @return mixed
     */
    public function refundDonation(Donation $donation);
}
