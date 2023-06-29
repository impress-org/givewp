<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;

/**
 * @unreleased
 */
interface PaymentGatewayInterfaceV3 extends BasePaymentGatewayInterface
{
    /**
     * @unreleased
     */
    const API_VERSION = 3;

    /**
     * Returns a human-readable name for the gateway
     *
     * @unreleased
     *
     * @return string - Translated text
     */
    public function getName(): string;

    /**
     * Returns a human-readable label for use when a donor selects a payment method to use
     *
     * @unreleased
     *
     * @return string - Translated text
     */
    public function getPaymentMethodLabel(): string;

    /**
     * Determines if refunds are supported
     *
     * @unreleased
     *
     * @return bool
     */
    public function supportsRefund(): bool;

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
     * @unreleased
     *
     * @return GatewayCommand|RedirectOffsite|void
     *
     * @throws PaymentGatewayException
     * @throws Exception
     */
    public function createPayment(Donation $donation, array $gatewayData);

    /**
     * @unreleased
     *
     * @param Donation $donation
     *
     * @return PaymentRefunded|void
     */
    public function refundDonation(Donation $donation);
}
