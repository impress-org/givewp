<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterfaceV3;

/**
 * @unreleased
 */
abstract class PaymentGatewayV3 extends BasePaymentGateway implements PaymentGatewayInterfaceV3
{
    /**
     * Enqueue gateway scripts using WordPress wp_enqueue_script().
     *
     * @unreleased
     *
     * @return void
     */
    public function enqueueScript(int $formId)
    {
        // wp_enqueue_script();
    }

    /**
     * Convenient way of localizing data to the JS gateway object accessible from `this.settings`.
     *
     * @unreleased
     */
    public function formSettings(int $formId): array
    {
        return [];
    }

}
