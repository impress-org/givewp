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
     */
    public static function id(): string;

    /**
     * Return a unique identifier for the gateway
     *
     * @since 2.18.0
     *
     * @deprecated 2.22.2 use static id() method instead, can use on an instance: $this::id() or $gateway::id() — even in strings
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
     * Note: You can use "givewp_create_payment_gateway_data_{$gatewayId}" filter hook to pass additional data for gateway which helps/require to process transaction.
     *       This filter will help to add additional arguments to this function which should be optional otherwise you will get PHP fatal error.
     *
     * @since 2.21.2 Add second param to function to pass gateway data to process transaction
     * @since 2.18.0
     *
     * @param array $gatewayData
     *
     * @return GatewayCommand|RedirectOffsite|void
     *
     * @throws PaymentGatewayException
     * @throws Exception
     */
    public function createPayment(Donation $donation, $gatewayData);

    /**
     * @since 2.20.0
     *
     * @param Donation $donation
     *
     * @return mixed
     */
    public function refundDonation(Donation $donation);
}
