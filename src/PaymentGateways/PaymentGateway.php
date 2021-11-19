<?php

namespace Give\PaymentGateways;

/**
 * Interface PaymentGateway
 *
 * For use when defining a Payment Gateway. This gives the basic configurations needed to register
 * the gateway with GiveWP.
 *
 * @since 2.9.0
 */
interface PaymentGateway
{
    /**
     * Returns a unique ID for the gateway
     *
     * @return string
     * @since 2.9.0
     *
     */
    public function getId();

    /**
     * Returns a human-readable name for the gateway
     *
     * @return string
     * @since 2.9.0
     *
     */
    public function getName();

    /**
     * Returns a human-readable label for use when a donor selects a payment method to use
     *
     * @return string
     * @since 2.9.0
     *
     */
    public function getPaymentMethodLabel();

    /**
     * Get payment gateway options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Bootstrap payment gateway
     *
     * @since 2.9.0
     */
    public function boot();
}
